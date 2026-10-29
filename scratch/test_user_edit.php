<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Modules\Apicola\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;

echo "--- Testing UserController show & update ---\n";

$user = User::whereHas('roles', function ($q) {
    $q->whereIn('slug', ['apicola.admin', 'apicola.aprendiz']);
})->first();

if (!$user) {
    echo "No apicola user found.\n";
    exit;
}

echo "Found user: ID={$user->id}, Nickname={$user->nickname}, Email={$user->email}\n";

$controller = new UserController();

// Test show
try {
    $showResp = $controller->show($user->id);
    echo "Show status: " . $showResp->status() . "\n";
    echo "Show content: " . $showResp->getContent() . "\n";
} catch (\Throwable $e) {
    echo "Show error: " . $e->getMessage() . "\n";
}

// Test update
$payload = [
    'first_name' => $user->person?->first_name ?? 'TestName',
    'last_name' => ($user->person?->first_last_name ?? 'TestLastName') . ' ' . ($user->person?->second_last_name ?? ''),
    'document_number' => $user->person?->document_number ?? '12345678',
    'email' => $user->email,
    'password' => '', // empty password as sent by form!
    'role_slug' => 'apicola.aprendiz',
    'ficha' => '3230026',
    'programa' => 'ANALISIS Y DESARROLLO DE SOFTWARE',
];

$request = Request::create('/apicola/usuarios/' . $user->id, 'PUT', $payload);
$request->headers->set('Accept', 'application/json');

try {
    $updateResp = $controller->update($request, $user->id);
    echo "Update status: " . $updateResp->status() . "\n";
    echo "Update content: " . $updateResp->getContent() . "\n";
} catch (\Throwable $e) {
    echo "Update error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
