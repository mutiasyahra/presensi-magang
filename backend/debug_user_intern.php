<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Intern;

$user = User::with('intern')->find(2);
if ($user) {
    echo "USER_2: " . json_encode($user->toArray()) . "\n";
} else {
    echo "USER_2_NOT_FOUND\n";
}

$intern = Intern::where('user_id', 2)->first();
if ($intern) {
    echo "INTERN_FOR_USER_2: " . json_encode($intern->toArray()) . "\n";
} else {
    echo "INTERN_FOR_USER_2_NOT_FOUND\n";
}
