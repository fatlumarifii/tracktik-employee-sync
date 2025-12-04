<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSyncResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider?->value,
            'provider_employee_id' => $this->provider_employee_id,
            'tracktik_employee_id' => $this->tracktik_employee_id,
            'sync_status' => $this->sync_status->value,
            'error_message' => $this->error_message,
            'synced_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
