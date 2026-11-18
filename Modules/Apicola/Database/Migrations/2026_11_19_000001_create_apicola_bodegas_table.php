<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bodegas del módulo apícola. Desde ahora existe una sola bodega (la del apiario) y se pueden
 * crear más cuando se necesiten. Los elementos y movimientos de la antigua "Bodega Central"
 * se pasan a la bodega del apiario.
 */
return new class extends Migration
{
    private const PRINCIPAL = 'Bodega del apiario';

    public function up(): void
    {
        if (!Schema::hasTable('apicola_bodegas')) {
            Schema::create('apicola_bodegas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150)->unique();
                $table->timestamps();
            });
        }

        DB::table('apicola_bodegas')->updateOrInsert(['nombre' => self::PRINCIPAL], ['updated_at' => now(), 'created_at' => now()]);

        if (!Schema::hasTable('apicola_inventarios')) {
            return;
        }

        // 1) Unificar mayúsculas: "Bodega del Apiario" -> "Bodega del apiario"
        DB::table('apicola_inventarios')->whereRaw('LOWER(bodega) = ?', ['bodega del apiario'])->update(['bodega' => self::PRINCIPAL]);

        // 2) Pasar la Bodega Central a la del apiario, sumando cantidades de elementos repetidos
        $centrales = DB::table('apicola_inventarios')->whereRaw('LOWER(bodega) = ?', ['bodega central'])->get();
        foreach ($centrales as $c) {
            $destino = DB::table('apicola_inventarios')
                ->where('bodega', self::PRINCIPAL)
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($c->nombre)])
                ->where('categoria', $c->categoria)
                ->where('id', '!=', $c->id)
                ->first();

            if ($destino) {
                DB::table('apicola_inventarios')->where('id', $destino->id)->update([
                    'cantidad' => (int) $destino->cantidad + (int) $c->cantidad,
                    'stock_minimo' => max((int) $destino->stock_minimo, (int) $c->stock_minimo),
                    'updated_at' => now(),
                ]);
                foreach (['apicola_inventario_movimientos', 'apicola_movimiento_detalles'] as $t) {
                    if (Schema::hasTable($t) && Schema::hasColumn($t, 'inventario_id')) {
                        DB::table($t)->where('inventario_id', $c->id)->update(['inventario_id' => $destino->id]);
                    }
                }
                DB::table('apicola_inventarios')->where('id', $c->id)->delete();
            } else {
                DB::table('apicola_inventarios')->where('id', $c->id)->update(['bodega' => self::PRINCIPAL, 'updated_at' => now()]);
            }
        }

        // 3) Los comprobantes de movimientos también quedan en la bodega del apiario
        if (Schema::hasTable('apicola_movimientos')) {
            DB::table('apicola_movimientos')
                ->whereRaw('LOWER(bodega) IN (?, ?)', ['bodega central', 'bodega del apiario'])
                ->update(['bodega' => self::PRINCIPAL]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('apicola_bodegas');
    }
};
