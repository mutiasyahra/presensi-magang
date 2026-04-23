<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\InternController;
use App\Http\Controllers\Api\LeaveController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:sanctum')->get('/calendar', [AttendanceController::class, 'calendar']);

// ================= USER =================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendances/{id}', [AttendanceController::class, 'update']);
    Route::post('/leave', [LeaveController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});

// ================= ADMIN =================
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::patch('/leave/{id}', [LeaveController::class, 'approve']);
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::get('/stats', [AttendanceController::class, 'stats']);
    Route::get('/export', [AttendanceController::class, 'exportExcel']);
    Route::get('/recap-monthly', [AttendanceController::class, 'monthlyRecap']);
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::get('/attendance-filters', [AttendanceController::class, 'attendanceFilters']);
    Route::patch('/attendances/{id}/verify', [AttendanceController::class, 'verify']);
    Route::get('/interns', [InternController::class, 'index']);
    Route::post('/interns', [InternController::class, 'store']);
    
    // Settings System
    Route::get('/settings/system', [\App\Http\Controllers\Api\SettingsController::class, 'getSystem']);
    Route::put('/settings/system', [\App\Http\Controllers\Api\SettingsController::class, 'updateSystem']);

});

// Settings Preferences (all authenticated users, even though this system is single admin currently)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/settings/me', [\App\Http\Controllers\Api\SettingsController::class, 'getMe']);
    Route::post('/settings/me', [\App\Http\Controllers\Api\SettingsController::class, 'updateMe']);
});

// ================= AUTH =================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ================= PROTECTED =================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ================= TEST =================
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
