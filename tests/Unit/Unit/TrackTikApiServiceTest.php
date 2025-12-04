<?php

namespace Tests\Unit\Unit;

use App\Exceptions\TrackTikApiException;
use App\Services\TrackTikApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrackTikApiServiceTest extends TestCase
{
    protected TrackTikApiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrackTikApiService;
        Cache::flush();
    }

    public function test_get_access_token_success(): void
    {
        Http::fake([
            config('tracktik.token_url') => Http::response([
                'access_token' => 'test_token_123',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token = $this->service->getAccessToken();

        $this->assertEquals('test_token_123', $token);
    }

    public function test_get_access_token_caches_token(): void
    {
        Http::fake([
            config('tracktik.token_url') => Http::response([
                'access_token' => 'cached_token_456',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token1 = $this->service->getAccessToken();
        $token2 = $this->service->getAccessToken();

        $this->assertEquals($token1, $token2);
        Http::assertSentCount(1);
    }

    public function test_get_access_token_throws_exception_on_failure(): void
    {
        Http::fake([
            config('tracktik.token_url') => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $this->expectException(TrackTikApiException::class);
        $this->expectExceptionMessage('Failed to authenticate with TrackTik API');

        $this->service->getAccessToken();
    }

    public function test_create_employee_success(): void
    {
        Http::fake([
            config('tracktik.token_url') => Http::response([
                'access_token' => 'test_token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
            config('tracktik.api_base_url').'/employees' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 'uuid-123',
                    'employeeId' => 'EMP001',
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                ],
            ], 201),
        ]);

        $employeeData = [
            'employeeId' => 'EMP001',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
        ];

        $result = $this->service->createEmployee($employeeData);

        $this->assertTrue($result['success']);
        $this->assertEquals('EMP001', $result['data']['employeeId']);
        $this->assertEquals('John', $result['data']['firstName']);
    }

    public function test_create_employee_throws_exception_on_failure(): void
    {
        Http::fake([
            config('tracktik.token_url') => Http::response([
                'access_token' => 'test_token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
            config('tracktik.api_base_url').'/employees' => Http::response([
                'error' => 'Validation failed',
            ], 400),
        ]);

        $this->expectException(TrackTikApiException::class);

        $this->service->createEmployee([
            'employeeId' => 'EMP001',
            'firstName' => 'John',
        ]);
    }

    public function test_create_employee_uses_authorization_header(): void
    {
        Http::fake([
            config('tracktik.token_url') => Http::response([
                'access_token' => 'test_token_xyz',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
            config('tracktik.api_base_url').'/employees' => Http::response([
                'success' => true,
                'data' => ['employeeId' => 'EMP001'],
            ], 201),
        ]);

        $this->service->createEmployee(['employeeId' => 'EMP001']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test_token_xyz') &&
                   $request->url() === config('tracktik.api_base_url').'/employees';
        });
    }
}
