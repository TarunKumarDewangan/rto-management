<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminUserManagementController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\CitizenController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\PuccController;
use App\Http\Controllers\Api\FitnessController;
use App\Http\Controllers\Api\VltdController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ExpiryReportController;
use App\Http\Controllers\Api\BackupController;


// Public Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Require Token)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/users', [AdminUserManagementController::class, 'index']);
    Route::post('/users', [AdminUserManagementController::class, 'store']); // Create
    Route::put('/users/{id}', [AdminUserManagementController::class, 'update']); // Edit
    Route::patch('/users/{id}/status', [AdminUserManagementController::class, 'toggleStatus']); // Active/Deactive
    Route::delete('/users/{id}', [AdminUserManagementController::class, 'destroy']); // Delete
    Route::get('/user/stats', [UserDashboardController::class, 'stats']);
    Route::get('/citizens', [CitizenController::class, 'index']);
    Route::post('/citizens', [CitizenController::class, 'store']);
    Route::get('/citizens/{id}', [CitizenController::class, 'show']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles/{id}/taxes', [TaxController::class, 'index']);
    Route::post('/taxes', [TaxController::class, 'store']);
    Route::put('/taxes/{id}', [TaxController::class, 'update']); // Edit
    Route::delete('/taxes/{id}', [TaxController::class, 'destroy']); // Delete

    // Payment
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/vehicles/{id}/insurances', [App\Http\Controllers\Api\InsuranceController::class, 'index']);
    Route::post('/insurances', [App\Http\Controllers\Api\InsuranceController::class, 'store']);
    Route::put('/insurances/{id}', [App\Http\Controllers\Api\InsuranceController::class, 'update']);
    Route::delete('/insurances/{id}', [App\Http\Controllers\Api\InsuranceController::class, 'destroy']);

    Route::get('/vehicles/{id}/puccs', [App\Http\Controllers\Api\PuccController::class, 'index']);
    Route::post('/puccs', [App\Http\Controllers\Api\PuccController::class, 'store']);
    Route::put('/puccs/{id}', [App\Http\Controllers\Api\PuccController::class, 'update']);
    Route::delete('/puccs/{id}', [App\Http\Controllers\Api\PuccController::class, 'destroy']);

    Route::get('/vehicles/{id}/fitness', [App\Http\Controllers\Api\FitnessController::class, 'index']);
    Route::post('/fitness', [App\Http\Controllers\Api\FitnessController::class, 'store']);
    Route::put('/fitness/{id}', [App\Http\Controllers\Api\FitnessController::class, 'update']);
    Route::delete('/fitness/{id}', [App\Http\Controllers\Api\FitnessController::class, 'destroy']);

    Route::get('/vehicles/{id}/vltds', [App\Http\Controllers\Api\VltdController::class, 'index']);
    Route::post('/vltds', [App\Http\Controllers\Api\VltdController::class, 'store']);
    Route::put('/vltds/{id}', [App\Http\Controllers\Api\VltdController::class, 'update']);
    Route::delete('/vltds/{id}', [App\Http\Controllers\Api\VltdController::class, 'destroy']);

    // Permit
    Route::get('/vehicles/{id}/permits', [App\Http\Controllers\Api\PermitController::class, 'index']);
    Route::post('/permits', [App\Http\Controllers\Api\PermitController::class, 'store']);
    Route::put('/permits/{id}', [App\Http\Controllers\Api\PermitController::class, 'update']);
    Route::delete('/permits/{id}', [App\Http\Controllers\Api\PermitController::class, 'destroy']);

    // Speed Governor
    Route::get('/vehicles/{id}/speed-governors', [App\Http\Controllers\Api\SpeedGovernorController::class, 'index']);
    Route::post('/speed-governors', [App\Http\Controllers\Api\SpeedGovernorController::class, 'store']);
    Route::put('/speed-governors/{id}', [App\Http\Controllers\Api\SpeedGovernorController::class, 'update']);
    Route::delete('/speed-governors/{id}', [App\Http\Controllers\Api\SpeedGovernorController::class, 'destroy']);

    Route::get('/citizens/{id}/statement', [AccountController::class, 'statement']);
    Route::put('/payments/{id}', [PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);

    Route::get('/reports/expiry', [ExpiryReportController::class, 'index']);

    Route::get('/export/backup', [BackupController::class, 'export']);
});
