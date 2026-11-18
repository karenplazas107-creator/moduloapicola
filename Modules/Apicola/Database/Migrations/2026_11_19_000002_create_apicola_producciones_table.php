<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Submódulo "Producción" de Colmenas: cada registro es una cosecha de miel de una colmena.
 * Queda ligada a la colmena igual que las inspecciones, y se copian como históricos los
 * registros de miel que ya existían dentro de las inspecciones.
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

        // Copiar como histórico la miel registrada en las inspecciones
        if (!Schema::hasTable('registro_inspeccion') || !Schema::hasTable('colmenas') || !Schema::hasTable('apicola_colmenas')) {
            return;
        }

        $inspecciones = DB::table('registro_inspeccion')
            ->leftJoin('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
            ->where('registro_inspeccion.miel_cosechada', '>', 0)
            ->orderBy('registro_inspeccion.fecha')
            ->orderBy('registro_inspeccion.id_registro')
            ->select('registro_inspeccion.*', 'colmenas.codigo as colmena_codigo')
            ->get();

        foreach ($inspecciones as $r) {
            $colmenaId = DB::table('apicola_colmenas')->where('code', $r->colmena_codigo)->value('id');
            if (!$colmenaId) {
                continue;
            }

            $responsable = 'SERGIO BARRERA';
            if (!empty($r->observaciones) && preg_match('/Responsable:\s*([^|]+)/', $r->observaciones, $m)) {
                $responsable = trim($m[1]);
            }

            $id = DB::table('apicola_producciones')->insertGetId([
                'colmena_id' => $colmenaId,
                'fecha' => $r->fecha,
                'miel_kg' => $r->miel_cosechada,
                'responsable' => $responsable,
                'observaciones' => 'Copiado de la inspección ' . ($r->numero_registro ?: 'REG-' . $r->id_registro),
                'estado' => (!empty($r->deleted_at) || ($r->estado ?? '') === 'Inactiva') ? 'Inactiva' : 'Activa',
                'user_id' => $r->user_id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('apicola_producciones')->where('id', $id)->update(['numero_registro' => sprintf('PRO-%04d', $id)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('apicola_producciones');
    }
};
