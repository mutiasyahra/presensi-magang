<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Notifications\LeaveApprovedNotification;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with('user.intern')->orderBy('created_at', 'desc');
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $leaves = $query->get();
        return response()->json([
            'message' => 'Daftar pengajuan cuti',
            'data' => $leaves
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:izin,sakit',
            'reason' => 'required',
            'file' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        $filePath = null;

        if ($request->file) {
            $filePath = $request->file('file')->store('leave_files', 'public');
        }

        $leave = Leave::create([
            'user_id' => $request->user()->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'reason' => $request->reason,
            'file' => $filePath
        ]);

        // Kirim notifikasi ke admin yang mengaktifkan alert leave
        $admins = \App\Models\User::where('role', 'admin')->where('notify_leave_requests', true)->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewLeaveRequestNotification($leave, $request->user()->name));
        }

        return response()->json([
            'message' => 'Pengajuan berhasil',
            'data' => $leave
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $leave = Leave::findOrFail($id);

        $leave->update([
            'status' => $request->status
        ]);

        if ($request->status === 'approved') {
            $startDate = \Carbon\Carbon::parse($leave->start_date);
            $endDate = \Carbon\Carbon::parse($leave->end_date);
            
            while ($startDate->lte($endDate)) {
                if (!$startDate->isWeekend()) {
                    \App\Models\Attendance::updateOrCreate(
                        [
                            'user_id' => $leave->user_id,
                            'attendance_date' => $startDate->toDateString()
                        ],
                        [
                            'status' => $leave->type,
                            'is_verified' => true,
                            'rencana_kegiatan' => 'Pengajuan ' . $leave->type . ': ' . $leave->reason,
                            'progress_kegiatan' => 'Disetujui oleh Admin',
                            'evidence' => $leave->file,
                        ]
                    );
                }
                $startDate->addDay();
            }
        }

        // Kirim notifikasi ke user yang mengajukan
        $leave->user->notify(new LeaveApprovedNotification($leave));

        return response()->json([
            'message' => 'Status diperbarui',
            'data' => $leave
        ]);
    }

    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        $leave->delete();
        
        return response()->json([
            'message' => 'Pengajuan berhasil dihapus'
        ]);
    }
}
