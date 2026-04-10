<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Setting;
use App\Notifications\LateAttendanceNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CheckLateArrivals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:late-arrivals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for interns who are more than 15 minutes late and notify admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now();
        $todayDate = $today->toDateString();

        // ❄️ Skip Weekends
        if ($today->dayOfWeek == 0 || $today->dayOfWeek == 6) {
            $this->info('Weekend: No processing.');
            return Command::SUCCESS;
        }

        // 🛡️ Avoid duplicate notification today
        if (Cache::has("late_notified_$todayDate")) {
            $this->info("Late notification for $todayDate was already sent. Skipping.");
            return Command::SUCCESS;
        }

        // Get system configurations
        $settings = Setting::all()->pluck('value', 'key');
        $startTimeStr = $settings->get('work_start_time', '08:00');
        
        // Define the "Late" threshold (Start Time + 15 minutes)
        $workStart = Carbon::createFromFormat('H:i', $startTimeStr);
        $lateThreshold = (clone $workStart)->addMinutes(15);

        // Current time matches the threshold exactly? 
        // Or has it PASSED the threshold and we haven't notified yet?
        // We run this command periodically (e.g., every 5 mins).
        
        $currentTime = now()->format('H:i');
        $thresholdStr = $lateThreshold->format('H:i');

        $this->info("Checking for late arrivals (Threshold: $thresholdStr, Current: $currentTime)");

        // Only process if we are within a reasonable window after the threshold (e.g. 10 mins window)
        // to avoid notifying all day if the cron keeps running.
        if (now()->lessThan($lateThreshold)) {
            $this->info("It is not yet 15 minutes past start time ($thresholdStr). Skipping.");
            return Command::SUCCESS;
        }

        // Fetch all interns who have NOT clocked in today
        $lateInterns = [];
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            $todayDate = now()->toDateString();

            // Check if already has attendance entry (could be sick/leave/alpha/hadir)
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('attendance_date', $todayDate)
                ->first();

            // Check if has approved leave covering today
            $leave = Leave::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $todayDate)
                ->where('end_date', '>=', $todayDate)
                ->first();

            if (!$attendance && !$leave) {
                // This intern is considered LATE (or absent)
                $lateInterns[] = $user;
            }
        }

        if (count($lateInterns) > 0) {
            $this->info("Found " . count($lateInterns) . " late intern(s). Notifying admins...");

            // Get admins who want late alerts
            $admins = User::where('role', 'admin')
                ->where('notify_late_alerts', true)
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new LateAttendanceNotification($lateInterns));
            }

            // ✅ Mark as notified for today
            Cache::put("late_notified_$todayDate", true, now()->endOfDay());
        } else {
            $this->info("No late interns detected yet.");
        }

        return Command::SUCCESS;
    }
}
