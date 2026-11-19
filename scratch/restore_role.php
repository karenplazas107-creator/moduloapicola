<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::find(124);
$adminRole = Modules\SICA\Entities\Role::where('slug', 'apicola.admin')->first();
$aprendizRole = Modules\SICA\Entities\Role::where('slug', 'apicola.aprendiz')->first();
$u->roles()->detach([$aprendizRole->id]);
$u->roles()->syncWithoutDetaching([$adminRole->id]);
echo "Restored role to admin.\n";
