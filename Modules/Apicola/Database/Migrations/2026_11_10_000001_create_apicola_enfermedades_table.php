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
        if (!Schema::hasTable('apicola_enfermedades')) {
            Schema::create('apicola_enfermedades', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150)->unique();
                $table->text('descripcion')->nullable();
                $table->string('estado', 50)->default('Activa');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apicola_enfermedades');
    }
};

