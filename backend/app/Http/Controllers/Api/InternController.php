<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InternController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')
            ->with(['intern'])
            ->withCount(['attendances' => function ($q) {
                $q->whereNotNull('clock_out');
            }])
            ->get()
            ->map(function ($user) {
                $intern = $user->intern;
                
                $totalDays = $intern && $intern->start_date
                    ? now()->diffInWeekdays($intern->start_date)
                    : 0;

                $presentDays = $user->attendances_count ?? 0;

                $attendancePercentage = $totalDays > 0
                    ? round(($presentDays / $totalDays) * 100)
                    : 0;
                
                return [
                    'id' => $intern ? $intern->id : $user->id,
                    'user_id' => $user->id,
                    'intern_id' => $intern ? $intern->intern_id : ('USR-'.$user->id),
                    'full_name' => $intern ? $intern->full_name : $user->name,
                    'email' => $user->email,
                    'phone_number' => $intern ? $intern->phone_number : '-',
                    'university' => $intern ? $intern->university : '-',
                    'department' => $intern ? $intern->department : '-',
                    'mentor' => $intern ? $intern->mentor : '-',
                    'project' => $intern ? $intern->project : '-',
                    'start_date' => $intern ? $intern->start_date : null,
                    'end_date' => $intern ? $intern->end_date : null,
                    'status' => $intern ? $intern->status : 'active',
                    'attendance_percentage' => $attendancePercentage,
                    'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
                ];
            });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:interns|unique:users',
            'intern_id' => 'required|unique:interns',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        // Buat user login untuk intern
        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make('password123')
        ]);

        $intern = Intern::create([
            'user_id' => $user->id,
            'intern_id' => $request->intern_id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'university' => $request->university,
            'department' => $request->department,
            'mentor' => $request->mentor,
            'project' => $request->project,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        return response()->json($intern);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'intern_id' => 'required|unique:interns,intern_id,'.($user->intern ? $user->intern->id : 'NULL'),
        ]);

        $user->update([
            'name' => $request->full_name,
            'email' => $request->email,
        ]);

        if ($user->intern) {
            $user->intern->update([
                'intern_id' => $request->intern_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'university' => $request->university,
                'department' => $request->department,
                'mentor' => $request->mentor,
                'project' => $request->project,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);
            $intern = $user->intern;
        } else {
            $intern = Intern::create([
                'user_id' => $user->id,
                'intern_id' => $request->intern_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'university' => $request->university,
                'department' => $request->department,
                'mentor' => $request->mentor,
                'project' => $request->project,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);
        }

        return response()->json($intern);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->intern) {
            $user->intern->delete();
        }
        $user->delete();

        return response()->json(['message' => 'Intern deleted successfully']);
    }
}