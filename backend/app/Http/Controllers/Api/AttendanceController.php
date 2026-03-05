<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    //  CLOCK IN
    public function clockIn(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|image',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'rencana_kegiatan' => 'required|string'
        ]);

        $userId = auth()->id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return response()->json([
                'message' => 'Anda sudah clock in hari ini'
            ], 400);
        }

        // store uploaded photo and save path
        $photoPath = $request->file('photo')->store('clock_in', 'public');

        Attendance::updateOrCreate(
            [
                'user_id' => $userId,
                'attendance_date' => $today,
            ],
            [
                'clock_in' => now(),
                'clock_in_photo' => $photoPath,
                'clock_in_lat' => $request->lat,
                'clock_in_long' => $request->long,
                'rencana_kegiatan' => $request->rencana_kegiatan,
                'status' => 'hadir'
            ]
        );

        return response()->json([
            'message' => 'Clock in berhasil'
        ]);
    }

    //  CLOCK OUT
    public function clockOut(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|image',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'progress_kegiatan' => 'required|string',
            'evidence' => 'nullable|file|mimes:jpeg,png,pdf|max:5120'
        ]);

        $userId = auth()->id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return response()->json([
                'message' => 'Anda belum clock in hari ini'
            ], 400);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'message' => 'Anda sudah clock out hari ini'
            ], 400);
        }

        // save uploaded clock-out photo
        $outPath = $request->file('photo')->store('clock_out', 'public');
        
        // save evidence if provided
        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('evidence', 'public');
        }

        $updateData = [
            'clock_out' => now(),
            'clock_out_photo' => $outPath,
            'clock_out_lat' => $request->lat,
            'clock_out_long' => $request->long,
            'progress_kegiatan' => $request->progress_kegiatan,
        ];

        if ($evidencePath) {
            $updateData['evidence'] = $evidencePath;
        }

        $attendance->update($updateData);

        return response()->json([
            'message' => 'Clock out berhasil'
        ]);
    }

    //  ADMIN - LIST ATTENDANCE
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
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

    //  ADMIN - REKAP BULANAN
    public function monthlyRecap(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'month' => 'required|date_format:Y-m'
        ]);

        $month = $request->month;

        $data = Attendance::selectRaw('user_id, COUNT(*) as total_hadir')
            ->where('status', 'hadir')
            ->where('attendance_date', 'like', $month . '%')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return response()->json([
            'message' => 'Rekap bulanan',
            'data' => $data
        ]);
    }

    //  EXPORT EXCEL
    public function exportExcel()
    {
        return Excel::download(new AttendanceExport, 'presensi.xlsx');
    }

    //  DASHBOARD STATS
    public function stats()
    {
        $user = auth()->user();
        $month = now()->format('Y-m');

        if ($user->role === 'admin') {
            return response()->json([
                'present' => Attendance::where('status', 'hadir')
                    ->where('attendance_date', 'like', $month.'%')
                    ->count(),

                'absent' => Attendance::where('status', 'alpha')
                    ->where('attendance_date', 'like', $month.'%')
                    ->count(),

                'pending_leaves' => Leave::where('status', 'pending')->count(),
                'total_interns' => User::where('role', 'user')->count()
            ]);
        }

        $present = Attendance::where('user_id', $user->id)
            ->where('status', 'hadir')
            ->where('attendance_date', 'like', $month.'%')
            ->count();

        $absent = Attendance::where('user_id', $user->id)
            ->where('status', 'alpha')
            ->where('attendance_date', 'like', $month.'%')
            ->count();

        $totalWorkDays = 22;
        $rate = $totalWorkDays > 0
            ? round(($present / $totalWorkDays) * 100, 1)
            : 0;

        return response()->json([
            'present' => $present,
            'absent' => $absent,
            'attendance_rate' => $rate
        ]);
    }

    //  EXPORT PDF
    public function exportPdf(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m'
        ]);

        $data = Attendance::with('user')
            ->where('attendance_date', 'like', $request->month.'%')
            ->get();

        $pdf = Pdf::loadView('pdf.rekap', compact('data'));

        return $pdf->download('rekap-presensi.pdf');
    }

    //  CALENDAR (USER)
    public function calendar(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'attendance' => Attendance::where('user_id', $user->id)->get(),
            'leave' => Leave::where('user_id', $user->id)->get()
        ]);
    }
}