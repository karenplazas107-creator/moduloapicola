<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('registro_inspeccion') && !Schema::hasColumn('registro_inspeccion', 'estado')) {
            Schema::table('registro_inspeccion', function (Blueprint $table) {
                $table->string('estado', 50)->default('Activa')->after('observaciones');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('registro_inspeccion') && Schema::hasColumn('registro_inspeccion', 'estado')) {
            Schema::table('registro_inspeccion', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
        }
    }
};
