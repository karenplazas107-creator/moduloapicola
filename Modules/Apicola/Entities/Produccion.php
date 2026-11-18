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
}
