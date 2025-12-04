<?php

namespace App\Actions\Mapping;

use App\Enums\EmployeeStatus;

class MapProvider1ToTrackTikAction
{
    public function execute(array $providerData): array
    {
        return [
            'employeeId' => $providerData['emp_id'],
            'firstName' => $providerData['first_name'],
            'lastName' => $providerData['last_name'],
            'email' => $providerData['email_address'],
            'phoneNumber' => $providerData['phone'] ?? null,
            'position' => $providerData['job_title'] ?? null,
            'department' => $providerData['dept'] ?? null,
            'startDate' => $providerData['hire_date'] ?? null,
            'status' => $this->mapStatus($providerData['employment_status'] ?? 'active'),
        ];
    }

    protected function mapStatus(string $status): string
    {
        return EmployeeStatus::fromProvider1($status)->value;
    }
}
