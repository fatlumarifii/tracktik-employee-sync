<?php

namespace App\Models;

use App\Enums\Provider;
use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Model;

class EmployeeSync extends Model
{
    protected $fillable = [
        'provider',
        'provider_employee_id',
        'tracktik_employee_id',
        'sync_status',
        'error_message',
    ];

    protected $casts = [
        'provider' => Provider::class,
        'sync_status' => SyncStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
