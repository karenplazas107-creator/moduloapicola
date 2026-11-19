<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('evidencias')) {
            Schema::create('evidencias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('titulo');
                $table->text('descripcion')->nullable();
                $table->string('archivo')->nullable();           // ruta del archivo subido
                $table->string('tipo')->default('documento');   // documento, imagen, video, otro
                $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
                $table->text('observaciones')->nullable();       // notas del revisor
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};
