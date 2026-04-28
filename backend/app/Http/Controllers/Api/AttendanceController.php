<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use App\Models\Intern;
use Carbon\Carbon;

use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;

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

        $startSetting = \App\Models\Setting::where('key', 'work_start_time')->first();
        $startTime = $startSetting ? $startSetting->value : '08:00';
        $currentTime = now()->format('H:i');
        
        $clockInStatus = ($currentTime > $startTime) ? 'terlambat' : 'tepat waktu';
        // Keep overall status for summary logic
        $status = ($clockInStatus === 'terlambat') ? 'terlambat' : 'hadir';

        $locationName = $this->getAddressFromCoords($request->lat, $request->long);

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
                'clock_in_location' => $locationName,
                'clock_in_status' => $clockInStatus,
                'rencana_kegiatan' => $request->rencana_kegiatan,
                'status' => $status
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

        $locationName = $this->getAddressFromCoords($request->lat, $request->long);

        // Check clock out status against end time
        $endSetting = \App\Models\Setting::where('key', 'work_end_time')->first();
        $endTime = $endSetting ? $endSetting->value : '17:00';
        $currentTime = now()->format('H:i');
        
        if ($currentTime < $endTime) {
            $clockOutStatus = 'terlalu cepat';
        } elseif ($currentTime > $endTime) {
            $clockOutStatus = 'terlambat';
        } else {
            $clockOutStatus = 'tepat waktu';
        }

        $updateData = [
            'clock_out' => now(),
            'clock_out_photo' => $outPath,
            'clock_out_lat' => $request->lat,
            'clock_out_long' => $request->long,
            'clock_out_location' => $locationName,
            'clock_out_status' => $clockOutStatus,
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

    // UPDATE ATTENDANCE (USER EDIT)
    public function update(Request $request, $id)
    {
        $attendance = Attendance::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($attendance->attendance_date !== now()->toDateString()) {
            return response()->json([
                'message' => 'Waktu edit telah habis. Progress kegiatan hanya dapat diedit pada hari yang sama sebelum jam 23:59.'
            ], 403);
        }

        $request->validate([
            'rencana_kegiatan' => 'nullable|string',
            'progress_kegiatan' => 'nullable|string',
            'evidence' => 'nullable|file|mimes:jpeg,png,pdf|max:5120'
        ]);

        $updateData = [];

        if ($request->has('rencana_kegiatan')) {
            $updateData['rencana_kegiatan'] = $request->rencana_kegiatan;
        }

        if ($request->has('progress_kegiatan')) {
            $updateData['progress_kegiatan'] = $request->progress_kegiatan;
        }

        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('evidence', 'public');
            $updateData['evidence'] = $evidencePath;
        }

        if (!empty($updateData)) {
            $attendance->update($updateData);
        }

        return response()->json([
            'message' => 'Perubahan berhasil disimpan',
            'data' => $attendance
        ]);
    }

    //  ADMIN - LIST ATTENDANCE
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $query = Attendance::with(['user', 'user.intern']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('attendance_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->project && $request->project !== "All Project") {
            $project = $request->project;
            $query->whereHas("user.intern", function ($q) use ($project) {
                $q->where("project", $project);
            });
        }

        if ($request->university && $request->university !== "All Universities") {
            $university = $request->university;
            $query->whereHas("user.intern", function ($q) use (
                $university
            ) {
                $q->where("university", $university);
            });
        }

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('intern', function($sq) use ($search) {
                      $sq->where('intern_id', 'like', "%$search%");
                  });
            });
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->attendance_date) {
            $query->where('attendance_date', $request->attendance_date);
        }

        $data = $query->orderBy('attendance_date', 'desc')->get();

        return response()->json([
            'message' => 'Data presensi',
            'data' => $data
        ]);
    }

    //  ADMIN - GET UNIQUE FILTER OPTIONS
    public function attendanceFilters()
    {
        $projects = Intern::whereNotNull("project")
            ->distinct()
            ->pluck("project");

        $universities = Intern::whereNotNull("university")
            ->distinct()
            ->pluck("university");

        $periods = Intern::select("start_date", "end_date")
            ->whereNotNull("start_date")
            ->whereNotNull("end_date")
            ->distinct()
            ->get();

        return response()->json([
            "projects" => $projects,
            "universities" => $universities,
            "periods" => $periods,
        ]);
    }


    //  ADMIN - VERIFY ATTENDANCE
    public function verify(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'is_verified' => 'required|boolean',
            'status' => 'sometimes|in:hadir,izin,alpha' // Optional, to reject with alpha status
        ]);

        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json(['message' => 'Data presensi tidak ditemukan'], 404);
        }

        $updateData = ['is_verified' => $request->is_verified];
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        $attendance->update($updateData);

        return response()->json([
            'message' => 'Status verifikasi presensi berhasil diubah',
            'data' => $attendance
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
            $present = Attendance::where('status', 'hadir')
                ->where('attendance_date', 'like', $month.'%')
                ->count();
            
            $late = Attendance::where('status', 'terlambat')
                ->where('attendance_date', 'like', $month.'%')
                ->count();
            
            $absent = Attendance::where('status', 'alpha')
                ->where('attendance_date', 'like', $month.'%')
                ->count();

            $startOfMonth = now()->startOfMonth();
            $today = now();
            $elapsedWorkingDays = $startOfMonth->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, clone $today);
            if (!now()->isWeekend()) {
                $elapsedWorkingDays++;
            }
            if ($elapsedWorkingDays == 0) $elapsedWorkingDays = 1;

            $totalInterns = User::where('role', 'user')->count();
            
            // Calculate rates
            $totalPresent = $present + $late;
            $avgAttendance = $totalInterns > 0 ? round(($totalPresent / ($totalInterns * $elapsedWorkingDays)) * 100, 1) : 0;
            if ($avgAttendance > 100) $avgAttendance = 100;
            
            $onTimeRate = $totalPresent > 0 ? round(($present / $totalPresent) * 100, 1) : 0;

            $projectDistribution = Intern::select('project', \DB::raw('count(*) as count'))
                ->groupBy('project')
                ->get();

            return response()->json([
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'avg_attendance' => $avgAttendance,
                'on_time_rate' => $onTimeRate,
                'total_leaves' => Leave::count(),
                'total_interns' => $totalInterns,
                'total_departments' => Intern::distinct('department')->count('department'),
                'project_distribution' => $projectDistribution
            ]);
        }


        $present = Attendance::where('user_id', $user->id)
            ->whereIn('status', ['hadir', 'terlambat'])
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

    /**
     * Helper to get human readable address from coordinates using Nominatim API (OSM)
     */
    private function getAddressFromCoords($lat, $lng)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'PresensiMagangApp/1.0'
            ])->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['display_name'] ?? "Unknown Location";
            }
        } catch (\Exception $e) {
            \Log::error("Reverse Geocoding Error: " . $e->getMessage());
        }

        return "Location name unavailable";
    }
}