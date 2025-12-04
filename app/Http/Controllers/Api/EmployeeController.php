<?php

namespace App\Http\Controllers\Api;

use App\Actions\Employees\CreateEmployeeInTrackTikAction;
use App\Actions\Mapping\MapProvider1ToTrackTikAction;
use App\Actions\Mapping\MapProvider2ToTrackTikAction;
use App\Enums\Provider;
use App\Http\Requests\Provider1EmployeeRequest;
use App\Http\Requests\Provider2EmployeeRequest;
use App\Http\Resources\EmployeeSyncResource;
use Illuminate\Http\JsonResponse;

class EmployeeController extends ApiController
{
    public function __construct(
        protected CreateEmployeeInTrackTikAction $createEmployeeAction,
        protected MapProvider1ToTrackTikAction $mapProvider1Action,
        protected MapProvider2ToTrackTikAction $mapProvider2Action,
    ) {}

    public function syncProvider1(Provider1EmployeeRequest $request): JsonResponse
    {
        try {
            $mappedData = $this->mapProvider1Action->execute($request->validated());

            $employeeSync = $this->createEmployeeAction->execute(
                Provider::PROVIDER1,
                $request->validated('emp_id'),
                $mappedData
            );

            return $this->jsonResponse(
                message: 'Employee synced successfully',
                data: new EmployeeSyncResource($employeeSync),
                statusCode: 201
            );
        } catch (\Exception $e) {
            return $this->jsonResponse(
                message: 'Failed to sync employee',
                error: $e->getMessage(),
                statusCode: 500
            );
        }
    }

    public function syncProvider2(Provider2EmployeeRequest $request): JsonResponse
    {
        try {
            $mappedData = $this->mapProvider2Action->execute($request->validated());

            $employeeSync = $this->createEmployeeAction->execute(
                Provider::PROVIDER2,
                $request->validated('employee_number'),
                $mappedData
            );

            return $this->jsonResponse(
                message: 'Employee synced successfully',
                data: new EmployeeSyncResource($employeeSync),
                statusCode: 201
            );
        } catch (\Exception $e) {
            return $this->jsonResponse(
                message: 'Failed to sync employee',
                error: $e->getMessage(),
                statusCode: 500
            );
        }
    }
}
