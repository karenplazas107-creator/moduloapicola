<?php

namespace Modules\Apicola\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\SICA\Entities\App;
use Modules\SICA\Entities\Role;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\EPS;
use Modules\SICA\Entities\PopulationGroup;
use Modules\SICA\Entities\PensionEntity;
use App\Models\User;

class ApicolaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds for the Apicola module.
     */
    public function run(): void
    {
        // 1. Obtener la App original de Apícola (ID: 30)
        $app = App::where('name', 'like', '%apicol%')
                  ->orWhere('url', 'like', '%apicol%')
                  ->first();

        if (!$app) {
            if (isset($this->command)) {
                $this->command->error('No se encontró la aplicación Apícola en la base de datos.');
            }
            return;
        }

        // 2. Crear o actualizar el rol exclusivo de Administrador Apícola para esta App (compatible con SoftDeletes)
        $roleAdmin = Role::withTrashed()->where('slug', 'apicola.admin')->first();
        if ($roleAdmin) {
            if (method_exists($roleAdmin, 'trashed') && $roleAdmin->trashed()) {
                $roleAdmin->restore();
            }
            $roleAdmin->update([
                'name' => 'Administrador Apícola',
                'description' => 'Administrador exclusivo del módulo Apícola.',
                'description_english' => 'Exclusive Administrator for the Beekeeping module.',
                'app_id' => $app->id,
            ]);
        } else {
            $roleAdmin = Role::create([
                'slug' => 'apicola.admin',
                'name' => 'Administrador Apícola',
                'description' => 'Administrador exclusivo del módulo Apícola.',
                'description_english' => 'Exclusive Administrator for the Beekeeping module.',
                'app_id' => $app->id,
            ]);
        }

        // 3. Entidades base para la persona
        $eps = EPS::firstOrCreate(['name' => 'NO REGISTRA']);
        $populationGroup = PopulationGroup::firstOrCreate(['name' => 'NINGUNA']);
        $pensionEntity = PensionEntity::firstOrCreate(['name' => 'NO REGISTRA']);

        // 4. Crear o actualizar la persona del Administrador Apícola
        $person = Person::withTrashed()->where('document_number', 1075258901)->first();
        if ($person) {
            if (method_exists($person, 'trashed') && $person->trashed()) {
                $person->restore();
            }
            $person->update([
                'document_type' => 'Cédula de ciudadanía',
                'first_name' => 'SERGIO',
                'first_last_name' => 'BARRERA',
                'eps_id' => $eps->id,
                'population_group_id' => $populationGroup->id,
                'pension_entity_id' => $pensionEntity->id,
            ]);
        } else {
            $person = Person::create([
                'document_number' => 1075258901,
                'document_type' => 'Cédula de ciudadanía',
                'first_name' => 'SERGIO',
                'first_last_name' => 'BARRERA',
                'eps_id' => $eps->id,
                'population_group_id' => $populationGroup->id,
                'pension_entity_id' => $pensionEntity->id,
            ]);
        }

        // 5. Crear o actualizar el usuario exclusivo para Apícola
        $user = User::withTrashed()->where('email', 'admin.apicola@sena.edu.co')->first();
        if ($user) {
            if (method_exists($user, 'trashed') && $user->trashed()) {
                $user->restore();
            }
            $user->update([
                'nickname' => 'adminapicola',
                'person_id' => $person->id,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]);
        } else {
            $user = User::create([
                'nickname' => 'adminapicola',
                'email' => 'admin.apicola@sena.edu.co',
                'person_id' => $person->id,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]);
        }

        // 6. Asignar el rol exclusivo 'apicola.admin'
        $user->roles()->sync([$roleAdmin->id]);

        // 7. Crear o actualizar el rol de Aprendiz Apícola para esta App
        $roleAprendiz = Role::withTrashed()->where('slug', 'apicola.aprendiz')->first();
        if ($roleAprendiz) {
            if (method_exists($roleAprendiz, 'trashed') && $roleAprendiz->trashed()) {
                $roleAprendiz->restore();
            }
            $roleAprendiz->update([
                'name' => 'Aprendiz Apícola',
                'description' => 'Rol de Aprendiz para el módulo Apícola con acceso a consultas y actividades formativas.',
                'description_english' => 'Apprentice Role for the Beekeeping module.',
                'app_id' => $app->id,
            ]);
        } else {
            $roleAprendiz = Role::create([
                'slug' => 'apicola.aprendiz',
                'name' => 'Aprendiz Apícola',
                'description' => 'Rol de Aprendiz para el módulo Apícola con acceso a consultas y actividades formativas.',
                'description_english' => 'Apprentice Role for the Beekeeping module.',
                'app_id' => $app->id,
            ]);
        }

        // 8. Crear o actualizar la persona del Aprendiz Apícola
        $personAprendiz = Person::withTrashed()->where('document_number', 1075258902)->first();
        if ($personAprendiz) {
            if (method_exists($personAprendiz, 'trashed') && $personAprendiz->trashed()) {
                $personAprendiz->restore();
            }
            $personAprendiz->update([
                'document_type' => 'Cédula de ciudadanía',
                'first_name' => 'APRENDIZ',
                'first_last_name' => 'SENA',
                'eps_id' => $eps->id,
                'population_group_id' => $populationGroup->id,
                'pension_entity_id' => $pensionEntity->id,
            ]);
        } else {
            $personAprendiz = Person::create([
                'document_number' => 1075258902,
                'document_type' => 'Cédula de ciudadanía',
                'first_name' => 'APRENDIZ',
                'first_last_name' => 'SENA',
                'eps_id' => $eps->id,
                'population_group_id' => $populationGroup->id,
                'pension_entity_id' => $pensionEntity->id,
            ]);
        }

        // 9. Crear o actualizar el usuario exclusivo para Aprendiz Apícola
        $userAprendiz = User::withTrashed()->where('email', 'aprendiz.apicola@sena.edu.co')->first();
        if ($userAprendiz) {
            if (method_exists($userAprendiz, 'trashed') && $userAprendiz->trashed()) {
                $userAprendiz->restore();
            }
            $userAprendiz->update([
                'nickname' => 'aprendizapicola',
                'person_id' => $personAprendiz->id,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]);
        } else {
            $userAprendiz = User::create([
                'nickname' => 'aprendizapicola',
                'email' => 'aprendiz.apicola@sena.edu.co',
                'person_id' => $personAprendiz->id,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]);
        }

        // 10. Asignar el rol exclusivo 'apicola.aprendiz'
        $userAprendiz->roles()->sync([$roleAprendiz->id]);

        // 11. Sembrar Colmenas base sincronizadas
        $this->call(ColmenasTableSeeder::class);

        if (isset($this->command)) {
            $this->command->info('=============================================');
            $this->command->info('✅ Administrador de Apícola configurado con éxito:');
            $this->command->info('   - App: ' . $app->name . ' (ID: ' . $app->id . ')');
            $this->command->info('   - Rol: ' . $roleAdmin->name . ' (' . $roleAdmin->slug . ')');
            $this->command->info('   - Persona: ' . $person->first_name . ' ' . $person->first_last_name);
            $this->command->info('   - Usuario: ' . $user->email . ' / 12345678');
            $this->command->info('---------------------------------------------');
            $this->command->info('✅ Aprendiz de Apícola configurado con éxito:');
            $this->command->info('   - Rol: ' . $roleAprendiz->name . ' (' . $roleAprendiz->slug . ')');
            $this->command->info('   - Persona: ' . $personAprendiz->first_name . ' ' . $personAprendiz->first_last_name);
            $this->command->info('   - Usuario: ' . $userAprendiz->email . ' / 12345678');
            $this->command->info('=============================================');
        }
    }
}
