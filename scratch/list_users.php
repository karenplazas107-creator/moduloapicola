<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::withTrashed()
    ->with(['person.apprentices.course.program', 'roles'])
    ->whereHas('roles', function ($q) {
        $q->whereIn('slug', ['apicola.admin', 'apicola.aprendiz']);
    })->get();

foreach ($users as $u) {
    echo "ID: {$u->id} | Nickname: {$u->nickname} | Email: {$u->email} | Person: " . ($u->person ? "{$u->person->first_name} {$u->person->first_last_name} (Doc: {$u->person->document_number})" : "None") . "\n";
}
