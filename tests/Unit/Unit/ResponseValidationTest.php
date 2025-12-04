<?php

namespace Tests\Unit\Unit;

use App\Actions\Employees\CreateEmployeeInTrackTikAction;
use App\Enums\Provider;
use App\Enums\SyncStatus;
use App\Services\TrackTikApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponseValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_failed_status_on_missing_employee_id(): void
    {
        // Mock service to return invalid response
        $mockService = $this->createMock(TrackTikApiService::class);
        $mockService->expects($this->once())
            ->method('createEmployee')
            ->willReturn([
                'data' => ['name' => 'John Doe'], // Missing employeeId
            ]);

        $action = new CreateEmployeeInTrackTikAction($mockService);

        $result = $action->execute(
            Provider::PROVIDER1,
            'P1_001',
            ['employeeId' => 'P1_001', 'firstName' => 'John']
        );

        $this->assertEquals(SyncStatus::FAILED, $result->sync_status);
        $this->assertStringContainsString('missing employeeId', $result->error_message);
    }

    public function test_validates_employee_id_exists_in_response(): void
    {
        $mockService = $this->createMock(TrackTikApiService::class);
        $mockService->expects($this->once())
            ->method('createEmployee')
            ->willReturn([
                'data' => ['employeeId' => 'TT_001'], // Valid response
            ]);

        $action = new CreateEmployeeInTrackTikAction($mockService);

        $result = $action->execute(
            Provider::PROVIDER1,
            'P1_001',
            ['employeeId' => 'P1_001', 'firstName' => 'John']
        );

        $this->assertEquals('TT_001', $result->tracktik_employee_id);
        $this->assertEquals(SyncStatus::SUCCESS, $result->sync_status);
    }

    public function test_failed_validation_updates_status_to_failed(): void
    {
        $mockService = $this->createMock(TrackTikApiService::class);
        $mockService->expects($this->once())
            ->method('createEmployee')
            ->willReturn([
                'data' => [], // Invalid: no employeeId
            ]);

        $action = new CreateEmployeeInTrackTikAction($mockService);

        $result = $action->execute(
            Provider::PROVIDER1,
            'P1_999',
            ['employeeId' => 'P1_999', 'firstName' => 'Test']
        );

        $this->assertEquals(SyncStatus::FAILED, $result->sync_status);
        $this->assertNotNull($result->error_message);
        $this->assertStringContainsString('invalid response format', $result->error_message);
    }
}
