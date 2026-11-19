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
        Schema::create('apiario_especie', function (Blueprint $table) {
            $table->id('id_apiario_especie');
            $table->foreignId('id_apiario')->constrained('apiario', 'id_apiarios')->onDelete('cascade');
            $table->foreignId('id_especie')->constrained('especie_malifera', 'id_especie')->onDelete('cascade');
            $table->decimal('distancia_metros', 8, 2)->nullable();
            $table->date('fecha_registro');
            $table->string('estado', 50)->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apiario_especie');
    }
};
