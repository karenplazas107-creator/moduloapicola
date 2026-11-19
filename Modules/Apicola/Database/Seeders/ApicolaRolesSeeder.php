<?php

namespace Modules\Apicola\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Apicola\Database\Seeders\ApicolaDatabaseSeeder;

class ApicolaRolesSeeder extends Seeder
{
    /**
     * Run the database seeds for Apicola roles and admin user.
     */
    public function run(): void
    {
        $this->call(ApicolaDatabaseSeeder::class);
    }
}
