<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // CEK apakah sudah absen hari ini
        $already = Attendance::where('user_id', $user->id)
                    ->whereDate('attendance_date', $today)
                    ->first();

        if ($already) {
            return response()->json([
                'message' => 'Kamu sudah absen hari ini'
            ], 400);
        }

        // Validasi
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'lat' => 'required',
            'long' => 'required',
            'rencana_kegiatan' => 'required'
        ]);

        // Simpan foto
        $photoPath = $request->file('photo')->store('clock_in', 'public');

        // Simpan ke database
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => $today,
            'clock_in' => now(),
            'clock_in_photo' => $photoPath,
            'clock_in_lat' => $request->lat,
            'clock_in_long' => $request->long,
            'rencana_kegiatan' => $request->rencana_kegiatan,
            'status' => 'hadir'
        ]);

        return response()->json([
            'message' => 'Clock In berhasil',
            'data' => $attendance
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        // Cari presensi hari ini
        $attendance = Attendance::where('user_id', $user->id)
                        ->whereDate('attendance_date', $today)
                        ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'Kamu belum clock in hari ini'
            ], 400);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'message' => 'Kamu sudah clock out'
            ], 400);
        }

        // Validasi
        $request->validate([
            'photo' => 'required|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120',
            'lat' => 'required',
            'long' => 'required',
            'progress_kegiatan' => 'required'
        ]);

        // Simpan foto
        $photoPath = $request->file('photo')->store('clock_out', 'public');

        // Update data
        $attendance->update([
            'clock_out' => now(),
            'clock_out_photo' => $photoPath,
            'clock_out_lat' => $request->lat,
            'clock_out_long' => $request->long,
            'progress_kegiatan' => $request->progress_kegiatan
        ]);

        return response()->json([
            'message' => 'Clock Out berhasil',
            'data' => $attendance
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak'
            ], 403);
        }

        $data = Attendance::with('user')
                ->orderBy('attendance_date', 'desc')
                ->get();

        return response()->json([
            'message' => 'Data presensi',
            'data' => $data
        ]);
    }
}
