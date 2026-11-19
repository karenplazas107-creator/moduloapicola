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
        Schema::create('enfermedades_registro', function (Blueprint $table) {
            $table->id('id_enfermedades');
            $table->foreignId('id_registro')->constrained('registro_inspeccion', 'id_registro')->onDelete('cascade');
            $table->string('nombre_enfermedad', 150);
            $table->string('estado', 50)->default('Detectada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfermedades_registro');
    }
};
