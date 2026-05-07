<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Leave;
use App\Models\Attendance;
use Carbon\Carbon;

$leaves = Leave::where('status', 'approved')->get();
$count = 0;

foreach ($leaves as $leave) {
    $startDate = Carbon::parse($leave->start_date);
    $endDate = Carbon::parse($leave->end_date);
    
    while ($startDate->lte($endDate)) {
        if (!$startDate->isWeekend()) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $leave->user_id,
                    'attendance_date' => $startDate->toDateString()
                ],
                [
                    'status' => $leave->type,
                    'is_verified' => true,
                    'rencana_kegiatan' => 'Cuti: ' . $leave->reason,
                    'progress_kegiatan' => 'Cuti: ' . $leave->reason,
                ]
            );
            $count++;
        }
        $startDate->addDay();
    }
}

echo "Berhasil memproses $count record presensi dari cuti yang sudah di-approve sebelumnya.\n";
