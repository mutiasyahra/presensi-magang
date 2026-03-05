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
    Route::get('/interns', [InternController::class, 'index']);
    Route::post('/interns', [InternController::class, 'store']);

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
