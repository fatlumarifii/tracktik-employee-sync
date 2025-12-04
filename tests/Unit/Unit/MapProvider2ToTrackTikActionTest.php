<?php

namespace Tests\Unit\Unit;

use App\Actions\Mapping\MapProvider2ToTrackTikAction;
use PHPUnit\Framework\TestCase;

class MapProvider2ToTrackTikActionTest extends TestCase
{
    public function test_maps_provider2_data_to_track_tik_format(): void
    {
        $action = new MapProvider2ToTrackTikAction;

        $providerData = [
            'employee_number' => 'P2_001',
            'personal_info' => [
                'given_name' => 'Jane',
                'family_name' => 'Doe',
                'email' => 'jane.doe@example.com',
                'mobile' => '+1234567890',
            ],
            'work_info' => [
                'role' => 'Security Guard',
                'division' => 'Night Shift Security',
                'start_date' => '2024-02-01',
                'current_status' => 'employed',
            ],
        ];

        $result = $action->execute($providerData);

        $this->assertEquals('P2_001', $result['employeeId']);
        $this->assertEquals('Jane', $result['firstName']);
        $this->assertEquals('Doe', $result['lastName']);
        $this->assertEquals('jane.doe@example.com', $result['email']);
        $this->assertEquals('+1234567890', $result['phoneNumber']);
        $this->assertEquals('Security Guard', $result['position']);
        $this->assertEquals('Night Shift Security', $result['department']);
        $this->assertEquals('2024-02-01', $result['startDate']);
        $this->assertEquals('active', $result['status']);
    }

    public function test_maps_provider2_status_correctly(): void
    {
        $action = new MapProvider2ToTrackTikAction;

        $statusMappings = [
            'employed' => 'active',
            'on_leave' => 'inactive',
            'terminated' => 'terminated',
        ];

        foreach ($statusMappings as $providerStatus => $expectedStatus) {
            $result = $action->execute([
                'employee_number' => 'TEST',
                'personal_info' => [
                    'given_name' => 'Test',
                    'family_name' => 'User',
                    'email' => 'test@example.com',
                ],
                'work_info' => [
                    'current_status' => $providerStatus,
                ],
            ]);

            $this->assertEquals($expectedStatus, $result['status']);
        }
    }

    public function test_handles_missing_optional_fields(): void
    {
        $action = new MapProvider2ToTrackTikAction;

        $providerData = [
            'employee_number' => 'P2_002',
            'personal_info' => [
                'given_name' => 'John',
                'family_name' => 'Smith',
                'email' => 'john.smith@example.com',
            ],
            'work_info' => [],
        ];

        $result = $action->execute($providerData);

        $this->assertNull($result['phoneNumber']);
        $this->assertNull($result['position']);
        $this->assertNull($result['department']);
        $this->assertNull($result['startDate']);
    }
}
