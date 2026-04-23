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
        $user = Auth::user()->load('intern');

        return response()->json([
            'status' => 'success',
            'data' => [
                'fullName' => $user->intern ? $user->intern->full_name : $user->name,
                'email' => $user->email,
                'phoneNumber' => $user->intern ? $user->intern->phone_number : '-',
                'internId' => $user->intern ? $user->intern->intern_id : ('USR-' . $user->id),
                'university' => $user->intern ? $user->intern->university : '-',
                'department' => $user->intern ? $user->intern->department : '-',
                'mentor' => $user->intern ? $user->intern->mentor : '-',
                'project' => $user->intern ? $user->intern->project : '-',
                'startDate' => $user->intern ? $user->intern->start_date : null,
                'endDate' => $user->intern ? $user->intern->end_date : null,
                'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
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

        // Debugging: Log the request and files
        \Illuminate\Support\Facades\Log::info('Update Profile Request Body:', $request->all());
        \Illuminate\Support\Facades\Log::info('Update Profile Request Files:', $request->allFiles());

        $request->validate([
            'fullName' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string|required_with:new_password',
            'new_password' => 'nullable|string|min:6|confirmed',
            'profile_photo' => 'nullable|file|max:5120', // Temporarily more lenient for testing
            'isDarkMode' => 'sometimes|boolean',
            'notifyLateAlerts' => 'sometimes|boolean',
            'notifyLeaveRequests' => 'sometimes|boolean',
        ]);

        if ($request->has('fullName')) {
            $user->name = $request->fullName;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // Handle Password Change
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password saat ini salah.'
                ], 422);
            }
            $user->password = Hash::make($request->new_password);
        }

        // Handle Profile Photo Upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo = $path;
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
            'message' => 'Profile updated successfully.',
            'data' => [
                'fullName' => $user->name,
                'email' => $user->email,
                'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
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
