<?php

namespace Tests\Unit\Unit;

use App\Actions\Mapping\MapProvider1ToTrackTikAction;
use PHPUnit\Framework\TestCase;

class MapProvider1ToTrackTikActionTest extends TestCase
{
    public function test_maps_provider1_data_to_track_tik_format(): void
    {
        $action = new MapProvider1ToTrackTikAction;

        $providerData = [
            'emp_id' => 'P1_001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email_address' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'job_title' => 'Security Guard',
            'dept' => 'Security Operations',
            'hire_date' => '2024-01-15',
            'employment_status' => 'active',
        ];

        $result = $action->execute($providerData);

        $this->assertEquals('P1_001', $result['employeeId']);
        $this->assertEquals('John', $result['firstName']);
        $this->assertEquals('Doe', $result['lastName']);
        $this->assertEquals('john.doe@example.com', $result['email']);
        $this->assertEquals('+1234567890', $result['phoneNumber']);
        $this->assertEquals('Security Guard', $result['position']);
        $this->assertEquals('Security Operations', $result['department']);
        $this->assertEquals('2024-01-15', $result['startDate']);
        $this->assertEquals('active', $result['status']);
    }

    public function test_maps_provider1_status_correctly(): void
    {
        $action = new MapProvider1ToTrackTikAction;

        $statusMappings = [
            'active' => 'active',
            'inactive' => 'inactive',
            'terminated' => 'terminated',
        ];

        foreach ($statusMappings as $providerStatus => $expectedStatus) {
            $result = $action->execute([
                'emp_id' => 'TEST',
                'first_name' => 'Test',
                'last_name' => 'User',
                'email_address' => 'test@example.com',
                'employment_status' => $providerStatus,
            ]);

            $this->assertEquals($expectedStatus, $result['status']);
        }
    }

    public function test_handles_missing_optional_fields(): void
    {
        $action = new MapProvider1ToTrackTikAction;

        $providerData = [
            'emp_id' => 'P1_002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email_address' => 'jane.smith@example.com',
        ];

        $result = $action->execute($providerData);

        $this->assertNull($result['phoneNumber']);
        $this->assertNull($result['position']);
        $this->assertNull($result['department']);
        $this->assertNull($result['startDate']);
    }
}
