<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Apicola\Http\Controllers\Admin\UserController;

$controller = new UserController();
$userIds = [103, 124, 125, 142];

foreach ($userIds as $id) {
    echo "=== Testing show for user ID $id ===\n";
    try {
        $resp = $controller->show($id);
        echo "Status: " . $resp->status() . "\n";
        echo "Content: " . $resp->getContent() . "\n";
    } catch (\Throwable $e) {
        echo "ERROR for user ID $id: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    }
}
