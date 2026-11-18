<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1) La miel total sale de Producción + inspecciones, así que se eliminan las copias históricas que una
 *    versión anterior hizo de las inspecciones hacia Producción (evita contar la miel dos veces).
 * 2) "Última visita" de cada colmena pasa a ser la fecha de su inspección vigente más reciente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('apicola_producciones')) {
            DB::table('apicola_producciones')->where('observaciones', 'like', 'Copiado de la inspección%')->delete();
        }

        if (!Schema::hasTable('apicola_colmenas') || !Schema::hasTable('colmenas') || !Schema::hasTable('registro_inspeccion')) {
            return;
        }

        foreach (DB::table('apicola_colmenas')->get(['id', 'code', 'installation_date']) as $c) {
            $idLegacy = DB::table('colmenas')->where('codigo', $c->code)->value('id_colmenas');
            if (!$idLegacy) {
                continue;
            }
            $ultima = DB::table('registro_inspeccion')->where('id_colmena', $idLegacy)->whereNull('deleted_at')->max('fecha');
            DB::table('apicola_colmenas')->where('id', $c->id)->update(['last_visit_date' => $ultima ?: $c->installation_date]);
        }
    }

    public function down(): void
    {
        // No reversible: son correcciones de datos.
    }
};
