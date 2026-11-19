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
        Schema::create('especie_malifera', function (Blueprint $table) {
            $table->id('id_especie');
            $table->string('nombre_comun', 100);
            $table->string('nombre_cientifico', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado', 50)->default('Activo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especie_malifera');
    }
};
