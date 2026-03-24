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
        $leaves = Leave::with('user.intern')->orderBy('created_at', 'desc')->get();
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

        // Kirim notifikasi ke user yang mengajukan
        $leave->user->notify(new LeaveApprovedNotification($leave));

        return response()->json([
            'message' => 'Status diperbarui',
            'data' => $leave
        ]);
    }
}
