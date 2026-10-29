<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate login as Sergio Barrera (admin)
$user = App\Models\User::find(124);
auth()->login($user);

// Simulate PUT request
$request = Illuminate\Http\Request::create(
    '/apicola/usuarios/103',
    'PUT',
    [],
    [],
    [],
    [
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
    ],
    json_encode([
        'first_name' => 'KAREN MICHELLE',
        'last_name' => 'PLAZAS ROJAS',
        'document_number' => 1079177037,
        'ficha' => '3230026',
        'programa' => 'ANALISIS Y DESARROLLO DE SOFTWARE',
        'email' => 'karenplazas107@gmail.com',
        'password' => '',
        'role_slug' => 'apicola.aprendiz'
    ])
);

// Disable CSRF for this direct test request
$app->instance('Illuminate\Foundation\Http\Middleware\VerifyCsrfToken', new class {
    public function handle($request, $next) { return $next($request); }
});

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 800) . "\n";
