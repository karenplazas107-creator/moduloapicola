<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de producción (cosecha de miel) de una colmena.
 */
class Produccion extends Model
{
    protected $table = 'apicola_producciones';

    protected $fillable = [
        'numero_registro',
        'colmena_id',
        'fecha',
        'miel_kg',
        'responsable',
        'observaciones',
        'estado',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'miel_kg' => 'float',
    ];

    public function colmena(): BelongsTo
    {
        return $this->belongsTo(Colmena::class, 'colmena_id');
    }

    /**
     * Resumen de producción por código de colmena (para mostrar la relación dentro de Inspección).
     * Devuelve: código => ['registros' => n, 'kg' => total, 'lista' => últimos 5 registros].
     */
    public static function resumenPorColmena(): array
    {
        $resumen = [];

        foreach (static::with('colmena')->where('estado', 'Activa')->orderByDesc('fecha')->orderByDesc('id')->get() as $p) {
            $code = $p->colmena->code ?? null;
            if (!$code) {
                continue;
            }

            $resumen[$code]['registros'] = ($resumen[$code]['registros'] ?? 0) + 1;
            $resumen[$code]['kg'] = round(($resumen[$code]['kg'] ?? 0) + $p->miel_kg, 2);
            if (count($resumen[$code]['lista'] ?? []) < 5) {
                $resumen[$code]['lista'][] = [
                    'regNumber' => $p->numero_registro ?: sprintf('PRO-%04d', $p->id),
                    'fecha' => $p->fecha->format('d/m/Y'),
                    'mielKg' => (float) $p->miel_kg,
                ];
            }
        }

        return $resumen;
    }

    /**
     * Miel cosechada = la registrada en Producción + la registrada dentro de las inspecciones
     * (a veces también se cosecha miel durante una inspección). Solo cuentan registros vigentes.
     */
    public static function mielTotal(?string $desde = null, ?string $hasta = null): float
    {
        $total = 0.0;

        $prod = \Illuminate\Support\Facades\DB::table('apicola_producciones')->where('estado', 'Activa');
        if ($desde && $hasta) {
            $prod->whereBetween('fecha', [$desde, $hasta]);
        }
        $total += (float) $prod->sum('miel_kg');

        if (\Illuminate\Support\Facades\Schema::hasTable('registro_inspeccion')) {
            $insp = \Illuminate\Support\Facades\DB::table('registro_inspeccion')->whereNull('deleted_at');
            if ($desde && $hasta) {
                $insp->whereBetween('fecha', [$desde, $hasta]);
            }
            $total += (float) $insp->sum('miel_cosechada');
        }

        return round($total, 2);
    }

    /**
     * Miel por mes de los últimos N meses (producción + inspecciones), para las gráficas.
     */
    public static function mielPorMes(int $meses = 6, ?int $colmenaId = null): array
    {
        $nombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $labels = [];
        $produccion = [];
        $inspeccion = [];

        $codigo = $colmenaId ? \Illuminate\Support\Facades\DB::table('apicola_colmenas')->where('id', $colmenaId)->value('code') : null;
        $idLegacy = $codigo ? \Illuminate\Support\Facades\DB::table('colmenas')->where('codigo', $codigo)->value('id_colmenas') : null;

        for ($i = $meses - 1; $i >= 0; $i--) {
            $inicio = \Carbon\Carbon::now()->startOfMonth()->subMonths($i);
            $fin = (clone $inicio)->endOfMonth();
            $labels[] = $nombres[$inicio->month - 1] . ' ' . $inicio->format('y');

            $q = \Illuminate\Support\Facades\DB::table('apicola_producciones')
                ->where('estado', 'Activa')
                ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()]);
            if ($colmenaId) {
                $q->where('colmena_id', $colmenaId);
            }
            $produccion[] = round((float) $q->sum('miel_kg'), 2);

            $qi = \Illuminate\Support\Facades\DB::table('registro_inspeccion')
                ->whereNull('deleted_at')
                ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()]);
            if ($colmenaId) {
                $qi->where('id_colmena', $idLegacy ?: 0);
            }
            $inspeccion[] = round((float) $qi->sum('miel_cosechada'), 2);
        }

        return [
            'labels' => $labels,
            'produccion' => $produccion,
            'inspeccion' => $inspeccion,
            'total' => array_map(fn ($a, $b) => round($a + $b, 2), $produccion, $inspeccion),
        ];
    }

    /**
     * Miel (kg) registrada en inspecciones vigentes, por código de colmena.
     */
    public static function mielInspeccionesPorColmena(): array
    {
        return \Illuminate\Support\Facades\DB::table('registro_inspeccion')
            ->join('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
            ->whereNull('registro_inspeccion.deleted_at')
            ->groupBy('colmenas.codigo')
            ->selectRaw('colmenas.codigo as codigo, SUM(registro_inspeccion.miel_cosechada) as kg')
            ->pluck('kg', 'codigo')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }

    /**
     * Miel cosechada durante inspecciones agrupada por mes y colmena (para la gráfica de Producción).
     * Devuelve una lista de ['mes' => 'YYYY-MM', 'colmena' => código, 'kg' => total].
     */
    public static function mielInspeccionesMensual(int $meses = 12): array
    {
        $desde = \Carbon\Carbon::now()->startOfMonth()->subMonths($meses - 1)->toDateString();

        return \Illuminate\Support\Facades\DB::table('registro_inspeccion')
            ->join('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
            ->whereNull('registro_inspeccion.deleted_at')
            ->where('registro_inspeccion.miel_cosechada', '>', 0)
            ->where('registro_inspeccion.fecha', '>=', $desde)
            ->selectRaw("DATE_FORMAT(registro_inspeccion.fecha, '%Y-%m') as mes, colmenas.codigo as colmena, SUM(registro_inspeccion.miel_cosechada) as kg")
            ->groupBy('mes', 'colmena')
            ->get()
            ->map(fn ($r) => ['mes' => $r->mes, 'colmena' => $r->colmena, 'kg' => round((float) $r->kg, 2)])
            ->all();
    }
}
