<?php

namespace Tests\Unit\Unit;

use App\Exceptions\TrackTikApiException;
use App\Services\TrackTikApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TokenRefreshTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'tracktik.client_id' => 'test_client_id',
            'tracktik.client_secret' => 'test_secret',
            'tracktik.token_url' => 'https://example.com/oauth/token',
            'tracktik.api_base_url' => 'https://example.com/api',
            'tracktik.scope' => 'employees:read employees:write',
        ]);
    }

    public function test_refreshes_token_on_401_response(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('initial_token');

        Cache::shouldReceive('forget')
            ->once()
            ->with(config('tracktik.token_cache_key'));

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('refreshed_token');

        // First request with initial token returns 401
        Http::fake([
            '*/employees' => Http::sequence()
                ->push(['error' => 'Unauthorized'], 401)
                ->push(['data' => ['employeeId' => 'TT_001']], 200),
        ]);

        $service = new TrackTikApiService;
        $result = $service->createEmployee(['employeeId' => 'P1_001']);

        $this->assertEquals('TT_001', $result['data']['employeeId']);
    }

    public function test_throws_exception_after_retry_fails(): void
    {
        Cache::shouldReceive('remember')
            ->twice()
            ->andReturn('token');

        Cache::shouldReceive('forget')
            ->once();

        // Both requests return 401
        Http::fake([
            '*/employees' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $service = new TrackTikApiService;

        $this->expectException(TrackTikApiException::class);
        $service->createEmployee(['employeeId' => 'P1_001']);
    }

    public function test_throws_exception_on_api_error(): void
    {
        Cache::shouldReceive('remember')->andReturn('token');

        Http::fake([
            '*/employees' => Http::response([
                'error' => 'Invalid employee data',
                'details' => ['field' => 'email'],
            ], 400),
        ]);

        $service = new TrackTikApiService;

        $this->expectException(TrackTikApiException::class);
        $this->expectExceptionMessage('Failed to create employee in TrackTik');

        $service->createEmployee(['employeeId' => 'P1_001']);
    }
}
