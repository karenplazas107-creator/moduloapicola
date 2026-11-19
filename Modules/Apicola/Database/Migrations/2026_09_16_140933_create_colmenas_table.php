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
        Schema::create('colmenas', function (Blueprint $table) {
            $table->id('id_colmenas');
            $table->string('codigo', 50)->unique();
            $table->date('fecha_instalacion');
            $table->string('estado', 50)->default('Activa');
            $table->foreignId('id_apiario')->constrained('apiario', 'id_apiarios')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colmenas');
    }
};
