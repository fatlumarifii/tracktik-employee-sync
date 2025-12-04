<?php

namespace Tests\Unit\Unit;

use App\Actions\Employees\CreateEmployeeInTrackTikAction;
use App\Enums\Provider;
use App\Enums\SyncStatus;
use App\Models\EmployeeSync;
use App\Services\TrackTikApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateEmployeeSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_existing_successful_sync(): void
    {
        // Create a successful sync record
        $existingSync = EmployeeSync::create([
            'provider' => Provider::PROVIDER1,
            'provider_employee_id' => 'P1_001',
            'tracktik_employee_id' => 'TT_001',
            'sync_status' => SyncStatus::SUCCESS,
        ]);

        // Mock the service (should not be called)
        $mockService = $this->createMock(TrackTikApiService::class);
        $mockService->expects($this->never())->method('createEmployee');

        $action = new CreateEmployeeInTrackTikAction($mockService);

        $result = $action->execute(
            Provider::PROVIDER1,
            'P1_001',
            ['employeeId' => 'P1_001', 'firstName' => 'John']
        );

        $this->assertEquals($existingSync->id, $result->id);
        $this->assertEquals(SyncStatus::SUCCESS, $result->sync_status);
        $this->assertEquals('TT_001', $result->tracktik_employee_id);
    }

    public function test_retries_failed_sync(): void
    {
        // Create a failed sync record
        EmployeeSync::create([
            'provider' => Provider::PROVIDER1,
            'provider_employee_id' => 'P1_002',
            'sync_status' => SyncStatus::FAILED,
            'error_message' => 'Previous error',
        ]);

        // Mock the service to return success
        $mockService = $this->createMock(TrackTikApiService::class);
        $mockService->expects($this->once())
            ->method('createEmployee')
            ->willReturn([
                'data' => ['employeeId' => 'TT_002'],
            ]);

        $action = new CreateEmployeeInTrackTikAction($mockService);

        $result = $action->execute(
            Provider::PROVIDER1,
            'P1_002',
            ['employeeId' => 'P1_002', 'firstName' => 'Jane']
        );

        $this->assertEquals(SyncStatus::SUCCESS, $result->sync_status);
        $this->assertEquals('TT_002', $result->tracktik_employee_id);
        $this->assertNull($result->error_message);
    }

    public function test_creates_new_sync_for_new_employee(): void
    {
        // Mock the service
        $mockService = $this->createMock(TrackTikApiService::class);
        $mockService->expects($this->once())
            ->method('createEmployee')
            ->willReturn([
                'data' => ['employeeId' => 'TT_003'],
            ]);

        $action = new CreateEmployeeInTrackTikAction($mockService);

        $result = $action->execute(
            Provider::PROVIDER2,
            'P2_003',
            ['employeeId' => 'P2_003', 'firstName' => 'Bob']
        );

        $this->assertEquals(SyncStatus::SUCCESS, $result->sync_status);
        $this->assertEquals('TT_003', $result->tracktik_employee_id);
        $this->assertEquals(Provider::PROVIDER2, $result->provider);
        $this->assertEquals('P2_003', $result->provider_employee_id);
    }
}
