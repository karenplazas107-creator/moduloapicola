<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Validator;

$rules = [
    'password' => 'nullable|string|min:6',
];

$data1 = ['password' => ''];
$v1 = Validator::make($data1, $rules);
echo "Validation with empty string: " . ($v1->fails() ? 'FAILED: ' . json_encode($v1->errors()) : 'PASSED') . "\n";

$data2 = ['password' => null];
$v2 = Validator::make($data2, $rules);
echo "Validation with null: " . ($v2->fails() ? 'FAILED: ' . json_encode($v2->errors()) : 'PASSED') . "\n";
