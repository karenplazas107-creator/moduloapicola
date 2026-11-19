<?php

namespace Modules\Apicola\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Apicola\Entities\Inventario;
use Modules\Apicola\Entities\InventarioMovimiento;

class ApicolaInventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventarioMovimiento::query()->forceDelete();
        Inventario::withTrashed()->forceDelete();

        // Seeder vaciado según solicitud del usuario para registro manual desde cero.
    }
}
