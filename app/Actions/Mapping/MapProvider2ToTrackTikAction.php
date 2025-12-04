<?php

namespace App\Actions\Mapping;

use App\Enums\EmployeeStatus;

class MapProvider2ToTrackTikAction
{
    public function execute(array $providerData): array
    {
        $personalInfo = $providerData['personal_info'] ?? [];
        $workInfo = $providerData['work_info'] ?? [];

        return [
            'employeeId' => $providerData['employee_number'],
            'firstName' => $personalInfo['given_name'],
            'lastName' => $personalInfo['family_name'],
            'email' => $personalInfo['email'],
            'phoneNumber' => $personalInfo['mobile'] ?? null,
            'position' => $workInfo['role'] ?? null,
            'department' => $workInfo['division'] ?? null,
            'startDate' => $workInfo['start_date'] ?? null,
            'status' => $this->mapStatus($workInfo['current_status'] ?? 'employed'),
        ];
    }

    protected function mapStatus(string $status): string
    {
        return EmployeeStatus::fromProvider2($status)->value;
    }
}
