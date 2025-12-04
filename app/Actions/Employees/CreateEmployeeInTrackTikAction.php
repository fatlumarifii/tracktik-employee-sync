<?php

namespace App\Actions\Employees;

use App\Enums\Provider;
use App\Enums\SyncStatus;
use App\Models\EmployeeSync;
use App\Services\TrackTikApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateEmployeeInTrackTikAction
{
    public function __construct(
        protected TrackTikApiService $trackTikApiService
    ) {}

    public function execute(Provider $provider, string $providerEmployeeId, array $mappedData): EmployeeSync
    {
        $existingSync = EmployeeSync::where('provider', $provider)
            ->where('provider_employee_id', $providerEmployeeId)
            ->first();

        if ($existingSync && $existingSync->sync_status === SyncStatus::SUCCESS) {
            Log::info('Employee already synced, returning existing record', [
                'provider' => $provider->value,
                'provider_employee_id' => $providerEmployeeId,
                'tracktik_employee_id' => $existingSync->tracktik_employee_id,
            ]);

            return $existingSync;
        }

        return DB::transaction(function () use ($provider, $providerEmployeeId, $mappedData, $existingSync) {
            $employeeSync = $existingSync ?? EmployeeSync::create([
                'provider' => $provider,
                'provider_employee_id' => $providerEmployeeId,
                'sync_status' => SyncStatus::PENDING,
            ]);

            if ($existingSync) {
                $employeeSync->update(['sync_status' => SyncStatus::PENDING]);
            }

            try {
                Log::info('Creating employee in TrackTik', [
                    'provider' => $provider->value,
                    'provider_employee_id' => $providerEmployeeId,
                    'retry' => $existingSync !== null,
                ]);

                $response = $this->trackTikApiService->createEmployee($mappedData);

                if (! isset($response['data']['employeeId'])) {
                    throw new \RuntimeException('TrackTik API returned invalid response format: missing employeeId');
                }

                $trackTikEmployeeId = $response['data']['employeeId'];

                $employeeSync->update([
                    'tracktik_employee_id' => $trackTikEmployeeId,
                    'sync_status' => SyncStatus::SUCCESS,
                    'error_message' => null,
                ]);

                Log::info('Employee created successfully', [
                    'provider' => $provider->value,
                    'provider_employee_id' => $providerEmployeeId,
                    'tracktik_employee_id' => $trackTikEmployeeId,
                ]);

                return $employeeSync->fresh();
            } catch (\Exception $e) {
                Log::error('Failed to create employee in TrackTik', [
                    'provider' => $provider->value,
                    'provider_employee_id' => $providerEmployeeId,
                    'error' => $e->getMessage(),
                ]);

                $employeeSync->update([
                    'sync_status' => SyncStatus::FAILED,
                    'error_message' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }
}
