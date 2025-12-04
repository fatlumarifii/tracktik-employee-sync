<?php

namespace Tests\Unit\Unit;

use App\Actions\Employees\CreateEmployeeInTrackTikAction;
use App\Enums\Provider;
use App\Enums\SyncStatus;
use App\Exceptions\TrackTikApiException;
use App\Models\EmployeeSync;
use App\Services\TrackTikApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CreateEmployeeInTrackTikActionTest extends TestCase
{
    use RefreshDatabase;

    protected TrackTikApiService $mockService;

    protected CreateEmployeeInTrackTikAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = Mockery::mock(TrackTikApiService::class);
        $this->action = new CreateEmployeeInTrackTikAction($this->mockService);
    }

    public function test_creates_employee_successfully(): void
    {
        $this->mockService->shouldReceive('createEmployee')
            ->once()
            ->with([
                'employeeId' => 'P1_001',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@example.com',
            ])
            ->andReturn([
                'success' => true,
                'data' => [
                    'id' => 'tracktik-uuid-123',
                    'employeeId' => 'P1_001',
                ],
            ]);

        $result = $this->action->execute(
            Provider::PROVIDER1,
            'P1_001',
            [
                'employeeId' => 'P1_001',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@example.com',
            ]
        );

        $this->assertInstanceOf(EmployeeSync::class, $result);
        $this->assertEquals(Provider::PROVIDER1, $result->provider);
        $this->assertEquals('P1_001', $result->provider_employee_id);
        $this->assertEquals('P1_001', $result->tracktik_employee_id);
        $this->assertEquals(SyncStatus::SUCCESS, $result->sync_status);
        $this->assertNull($result->error_message);
    }

    public function test_throws_exception_on_failure(): void
    {
        $this->mockService->shouldReceive('createEmployee')
            ->once()
            ->andThrow(new TrackTikApiException('API Error: Invalid data'));

        $this->expectException(TrackTikApiException::class);
        $this->expectExceptionMessage('API Error: Invalid data');

        $this->action->execute(
            Provider::PROVIDER2,
            'P2_001',
            ['employeeId' => 'P2_001']
        );
    }

    public function test_creates_pending_record_before_api_call(): void
    {
        $this->mockService->shouldReceive('createEmployee')
            ->once()
            ->andReturnUsing(function () {
                $pending = EmployeeSync::where('provider', Provider::PROVIDER1)
                    ->where('provider_employee_id', 'P1_TEST')
                    ->first();

                $this->assertNotNull($pending);
                $this->assertEquals(SyncStatus::PENDING, $pending->sync_status);

                return [
                    'success' => true,
                    'data' => ['employeeId' => 'P1_TEST'],
                ];
            });

        $this->action->execute(
            Provider::PROVIDER1,
            'P1_TEST',
            ['employeeId' => 'P1_TEST']
        );
    }

    public function test_handles_track_tik_employee_id_from_different_response_formats(): void
    {
        $this->mockService->shouldReceive('createEmployee')
            ->once()
            ->andReturn([
                'data' => [
                    'employeeId' => 'TT_002',
                ],
            ]);

        $result = $this->action->execute(
            Provider::PROVIDER1,
            'P1_002',
            ['employeeId' => 'P1_002']
        );

        $this->assertEquals('TT_002', $result->tracktik_employee_id);
    }

    public function test_rolls_back_transaction_on_exception(): void
    {
        $this->mockService->shouldReceive('createEmployee')
            ->once()
            ->andThrow(new \Exception('Database error'));

        try {
            $this->action->execute(
                Provider::PROVIDER1,
                'P1_003',
                ['employeeId' => 'P1_003']
            );
        } catch (\Exception $e) {
        }

        $employeeSync = EmployeeSync::where('provider', Provider::PROVIDER1)
            ->where('provider_employee_id', 'P1_003')
            ->first();

        $this->assertNull($employeeSync);
    }
}
