<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Holiday;
use App\Notifications\AlphaNotification;

class CheckAttendance extends Command
{
    protected $signature = 'check:attendance';
    protected $description = 'Check attendance and auto set alpha if absent';

    public function handle()
    {
        $today = now();

        // ❄️ Skip Weekend
        if ($today->dayOfWeek == 0 || $today->dayOfWeek == 6) {
            $this->info('Hari ini Weekend. Tidak diproses.');
            return Command::SUCCESS;
        }

        $todayDate = $today->toDateString();

        // 📅 Skip Hari Libur Nasional
        $holiday = Holiday::where('date', $todayDate)->first();

        if ($holiday) {
            $this->info('Hari libur nasional: ' . $holiday->description);
            return Command::SUCCESS;
        }

        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('attendance_date', $todayDate)
                ->first();

            $leave = Leave::where('user_id', $user->id)
                ->where('date', $todayDate)
                ->where('status', 'approved')
                ->first();

            if (!$attendance && !$leave) {
                Attendance::create([
                    'user_id' => $user->id,
                    'attendance_date' => $todayDate,
                    'status' => 'alpha',
                    'is_auto' => true
                ]);

                // 🔔 Kirim notifikasi ke semua admin
                $admins = User::where('role', 'admin')->get();

                foreach ($admins as $admin) {
                    $admin->notify(new AlphaNotification());
                }

                $this->info("Alpha dibuat untuk user ID: " . $user->id);
            }
        }

        $this->info('Check attendance selesai.');

        return Command::SUCCESS;
    }
}