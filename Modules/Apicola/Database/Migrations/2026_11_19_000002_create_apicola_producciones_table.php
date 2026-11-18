<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Submódulo "Producción" de Colmenas: cada registro es una cosecha de miel de una colmena.
 * Queda ligada a la colmena igual que las inspecciones. La miel total de una colmena es la suma de
 * Producción + la miel registrada dentro de las inspecciones, por eso no se copian datos entre ambas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('apicola_producciones')) {
            return;
        }

        Schema::create('apicola_producciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero_registro', 20)->nullable()->unique();
            $table->unsignedBigInteger('colmena_id')->index();   // apicola_colmenas.id
            $table->date('fecha');
            $table->decimal('miel_kg', 8, 2);
            $table->string('responsable', 150);
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('Activa');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apicola_producciones');
    }
};
