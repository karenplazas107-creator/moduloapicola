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
        Schema::create('registro_inspeccion', function (Blueprint $table) {
            $table->id('id_registro');
            $table->foreignId('id_aprendiz')->nullable()->constrained('apprentices')->nullOnDelete();
            $table->foreignId('id_colmena')->constrained('colmenas', 'id_colmenas')->onDelete('cascade');
            $table->string('numero_registro', 50)->nullable();
            $table->date('fecha');
            $table->decimal('miel_cosechada', 8, 2)->default(0);
            $table->string('estado_colmena', 100)->nullable();
            $table->boolean('presencia_miel')->default(false);
            $table->boolean('reina_vista')->default(false);
            $table->boolean('reina_marcada')->default(false);
            $table->string('color_reina', 50)->nullable();
            $table->string('alimentacion_suministrada', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_inspeccion');
    }
};
