<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('apicola_motivos_baja')) {
            return;
        }

        Schema::create('apicola_motivos_baja', function (Blueprint $table) {
            $table->id();
            $table->string('entidad', 40);            // apiario, colmena, inventario, calendario, inspeccion...
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->string('entidad_nombre', 190)->nullable();
            $table->string('accion', 20);             // desactivar | eliminar
            $table->text('motivo');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apicola_motivos_baja');
    }
};
