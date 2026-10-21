<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columna aporte a especie_malifera si no existe
        if (Schema::hasTable('especie_malifera')) {
            Schema::table('especie_malifera', function (Blueprint $table) {
                if (!Schema::hasColumn('especie_malifera', 'aporte')) {
                    $table->string('aporte', 100)->nullable()->default('Néctar y polen')->after('nombre_cientifico');
                }
            });
        }

        // 2. Agregar columna anio y modificar meses en calendario_floral si es necesario
        if (Schema::hasTable('calendario_floral')) {
            Schema::table('calendario_floral', function (Blueprint $table) {
                if (!Schema::hasColumn('calendario_floral', 'anio')) {
                    $table->integer('anio')->nullable()->default(2026)->after('id_especie');
                }
            });
            // Asegurar que meses sea tipo text o json para guardar las 12 etapas
            DB::statement("ALTER TABLE `calendario_floral` MODIFY COLUMN `meses` TEXT NULL");
        }

        // 3. Crear vista o tabla llamada 'calendario' para que aparezca directamente con ese nombre
        try {
            DB::statement("
                CREATE OR REPLACE VIEW `calendario` AS
                SELECT 
                    c.id_calendario,
                    c.id_especie,
                    e.nombre_comun,
                    e.nombre_cientifico,
                    e.aporte,
                    c.anio,
                    e.estado,
                    c.meses,
                    c.created_at,
                    c.updated_at
                FROM `calendario_floral` c
                JOIN `especie_malifera` e ON c.id_especie = e.id_especie
            ");
        } catch (\Throwable $e) {
            // Si la vista falla, crear tabla calendario
            if (!Schema::hasTable('calendario')) {
                Schema::create('calendario', function (Blueprint $table) {
                    $table->id('id_calendario');
                    $table->foreignId('id_especie')->nullable();
                    $table->string('nombre_comun', 100);
                    $table->string('nombre_cientifico', 150)->nullable();
                    $table->string('aporte', 100)->default('Néctar y polen');
                    $table->integer('anio')->default(2026);
                    $table->string('estado', 50)->default('Activa');
                    $table->text('meses')->nullable();
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("DROP VIEW IF EXISTS `calendario`");
        } catch (\Throwable $e) {}

        if (Schema::hasTable('especie_malifera') && Schema::hasColumn('especie_malifera', 'aporte')) {
            Schema::table('especie_malifera', function (Blueprint $table) {
                $table->dropColumn('aporte');
            });
        }

        if (Schema::hasTable('calendario_floral') && Schema::hasColumn('calendario_floral', 'anio')) {
            Schema::table('calendario_floral', function (Blueprint $table) {
                $table->dropColumn('anio');
            });
        }
    }
};
