<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Leave;

$records = Leave::all();
echo "TOTAL_RECORDS: " . count($records) . "\n";
foreach ($records as $index => $record) {
    echo "RECORD_$index: " . json_encode($record->toArray()) . "\n";
}
