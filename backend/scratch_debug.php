<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Intern;
use Illuminate\Support\Facades\DB;

$dist = Intern::select('project', DB::raw('count(*) as count'))
    ->groupBy('project')
    ->get();

echo json_encode($dist);
