<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla principal de colmenas y núcleos. Solo se crea si no existe, así que en las bases
 * que ya la tienen no hace nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('apicola_colmenas')) {
            return;
        }

        Schema::create('apicola_colmenas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('apiario_name', 150);
            $table->unsignedBigInteger('apiario_id')->nullable();
            $table->string('responsable', 150);
            $table->string('tipo_abeja', 100)->default('Apis mellifera');
            $table->date('installation_date');
            $table->date('last_visit_date')->nullable();
            $table->enum('status', ['Activa', 'En revisión', 'Inactiva'])->default('Activa');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apicola_colmenas');
    }
};
