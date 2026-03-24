<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Intern;
use App\Models\Leave;

// 1. Get user
$user = User::where('role', 'user')->first();
if (!$user) {
    echo "USER_NOT_FOUND\n";
    exit;
}

// 2. Clear old leaves for this user to avoid confusion
Leave::where('user_id', $user->id)->delete();

// 3. Ensure Intern Profile exists
$intern = Intern::updateOrCreate(
    ['user_id' => $user->id],
    [
        'intern_id' => 'INT-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
        'full_name' => $user->name,
        'email' => $user->email,
        'university' => 'Gajah Mada University',
        'status' => 'active'
    ]
);
echo "INTERN_SYNCED: " . $intern->intern_id . "\n";

// 4. Create a Pending Leave
$leave = Leave::create([
    'user_id' => $user->id,
    'start_date' => now()->toDateString(),
    'end_date' => now()->addDays(2)->toDateString(),
    'type' => 'izin',
    'reason' => 'Verifikasi Data Real Admin',
    'status' => 'pending'
]);
echo "LEAVE_CREATED: ID " . $leave->id . "\n";
