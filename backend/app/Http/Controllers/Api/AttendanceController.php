<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


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

        // Validasi lokasi (radius 100–200 meter dari kantor)
        $officeLat = env('OFFICE_LAT');
        $officeLong = env('OFFICE_LONG');
        $allowedRadius = env('OFFICE_RADIUS');

        $distance = $this->calculateDistance(
            $request->lat,
            $request->long,
            $officeLat,
            $officeLong
        );

        if ($distance > $allowedRadius) {
            return response()->json([
                'message' => 'Kamu berada di luar radius kantor'
            ], 403);
        }

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

        $query = Attendance::with('user');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('attendance_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $data = $query->orderBy('attendance_date', 'desc')->get();

        return response()->json([
            'message' => 'Data presensi',
            'data' => $data
        ]);
    }

    public function monthlyRecap(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $month = $request->month; // format: 2026-02

        $data = Attendance::selectRaw('user_id, COUNT(*) as total_hadir')
            ->where('status', 'hadir')
            ->where('attendance_date', 'like', $month.'%')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return response()->json([
            'message' => 'Rekap bulanan',
            'data' => $data
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new AttendanceExport, 'presensi.xlsx');
    }

    public function stats()
    {
        $user = auth()->user();
        $month = Carbon::now()->format('Y-m');

        if ($user->role === 'admin') {
            // Stats global untuk admin
            $totalPresent = Attendance::where('status', 'hadir')
                ->where('attendance_date', 'like', $month.'%')
                ->count();
                
            $totalLate = Attendance::where('status', 'terlambat')
                ->where('attendance_date', 'like', $month.'%')
                ->count();
                
            $totalAbsent = Attendance::where('status', 'alfa')
                ->where('attendance_date', 'like', $month.'%')
                ->count();

            $pendingLeaves = \App\Models\Leave::where('status', 'pending')->count();
            $totalInterns = \App\Models\User::where('role', 'user')->count();

            return response()->json([
                'present' => $totalPresent,
                'late' => $totalLate,
                'absent' => $totalAbsent,
                'pending_leaves' => $pendingLeaves,
                'total_interns' => $totalInterns
            ]);
        }

        // Stats personal untuk user
        $present = Attendance::where('user_id', $user->id)
            ->where('status', 'hadir')
            ->where('attendance_date', 'like', $month.'%')
            ->count();

        $late = Attendance::where('user_id', $user->id)
            ->where('status', 'terlambat')
            ->where('attendance_date', 'like', $month.'%')
            ->count();

        $absent = Attendance::where('user_id', $user->id)
            ->where('status', 'alfa')
            ->where('attendance_date', 'like', $month.'%')
            ->count();

        // Attendance rate calculation (dummy for now, based on work days)
        $totalWorkDays = 22; // Assumed
        $rate = $totalWorkDays > 0 ? round(($present / $totalWorkDays) * 100, 1) : 0;

        return response()->json([
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'attendance_rate' => $rate
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function exportPdf(Request $request)
    {
        $month = $request->month;

        $data = Attendance::with('user')
            ->where('attendance_date', 'like', $month.'%')
            ->get();

        $pdf = Pdf::loadView('pdf.rekap', compact('data'));

        return $pdf->download('rekap.pdf');
    }

    public function calendar(Request $request)
    {
        $user = $request->user();

        $attendance = \App\Models\Attendance::where('user_id', $user->id)->get();
        $leave = \App\Models\Leave::where('user_id', $user->id)->get();

        return response()->json([
            'attendance' => $attendance,
            'leave' => $leave
        ]);
    }
}