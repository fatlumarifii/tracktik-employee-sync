<?php

use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('api.auth')->group(function () {
    Route::post('/employees/provider1', [EmployeeController::class, 'syncProvider1']);
    Route::post('/employees/provider2', [EmployeeController::class, 'syncProvider2']);
});
