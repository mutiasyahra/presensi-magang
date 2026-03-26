<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Get authenticated user's account details and preferences.
     */
    public function getMe(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'fullName' => $user->name,
                'email' => $user->email,
                'isDarkMode' => $user->is_dark_mode,
                'notifyLateAlerts' => $user->notify_late_alerts,
                'notifyLeaveRequests' => $user->notify_leave_requests,
            ]
        ]);
    }

    /**
     * Update authenticated user's account details and preferences.
     */
    public function updateMe(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'isDarkMode' => 'boolean',
            'notifyLateAlerts' => 'boolean',
            'notifyLeaveRequests' => 'boolean',
        ]);

        $user->name = $request->fullName;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('isDarkMode')) {
            $user->is_dark_mode = $request->boolean('isDarkMode');
        }
        
        if ($request->has('notifyLateAlerts')) {
            $user->notify_late_alerts = $request->boolean('notifyLateAlerts');
        }
        
        if ($request->has('notifyLeaveRequests')) {
            $user->notify_leave_requests = $request->boolean('notifyLeaveRequests');
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Account settings updated successfully.',
            'data' => [
                'fullName' => $user->name,
                'email' => $user->email,
                'isDarkMode' => $user->is_dark_mode,
                'notifyLateAlerts' => $user->notify_late_alerts,
                'notifyLeaveRequests' => $user->notify_leave_requests,
            ]
        ]);
    }

    /**
     * Get system configurations.
     */
    public function getSystem()
    {
        // Define default values
        $defaults = [
            'work_start_time' => '08:00',
            'work_end_time' => '17:00',
            'geofencing_radius' => 100,
        ];

        // Fetch existing settings
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Merge defaults with existing
        $systemConfig = array_merge($defaults, $settings);

        return response()->json([
            'status' => 'success',
            'data' => [
                'startTime' => $systemConfig['work_start_time'],
                'endTime' => $systemConfig['work_end_time'],
                'radius' => (int) $systemConfig['geofencing_radius'],
            ]
        ]);
    }

    /**
     * Update system configurations (admin only).
     */
    public function updateSystem(Request $request)
    {
        $request->validate([
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i',
            'radius' => 'required|integer|min:10',
        ]);

        Setting::updateOrCreate(
            ['key' => 'work_start_time'],
            ['value' => $request->startTime]
        );

        Setting::updateOrCreate(
            ['key' => 'work_end_time'],
            ['value' => $request->endTime]
        );

        Setting::updateOrCreate(
            ['key' => 'geofencing_radius'],
            ['value' => $request->radius]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'System configuration updated successfully.',
            'data' => [
                'startTime' => $request->startTime,
                'endTime' => $request->endTime,
                'radius' => (int) $request->radius,
            ]
        ]);
    }
}
