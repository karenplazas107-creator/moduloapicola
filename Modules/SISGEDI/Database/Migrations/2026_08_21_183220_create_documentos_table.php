<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('documentos')) {
            Schema::create('documentos', function (Blueprint $table) {
                $table->id();
                $table->string('codigo')->unique(); // Ej: GTH-P-001
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('documentos');
    }
};