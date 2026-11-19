<?php

namespace Modules\Apicola\Entities;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de bajas: toda desactivación o eliminación en el módulo debe traer un motivo.
 */
class MotivoBaja extends Model
{
    protected $table = 'apicola_motivos_baja';

    protected $fillable = ['entidad', 'entidad_id', 'entidad_nombre', 'accion', 'motivo', 'user_id'];

    /** Nombre legible del tipo de elemento, para pantallas y reportes. */
    public const TIPOS = [
        'apiario' => 'Apiario',
        'colmena' => 'Colmena',
        'inspeccion' => 'Inspección',
        'produccion' => 'Producción',
        'inventario' => 'Inventario',
        'calendario' => 'Calendario floral',
        'enfermedad' => 'Enfermedad',
    ];

    public static function etiquetaTipo(?string $entidad): string
    {
        return self::TIPOS[$entidad] ?? ucfirst((string) $entidad);
    }

    /**
     * Valida que la petición traiga un motivo (mínimo 5 caracteres) y lo devuelve.
     * Lanza ValidationException (422) si falta.
     */
    public static function exigir(Request $request): string
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo.required' => 'Debes escribir el motivo de la desactivación o eliminación.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
        ]);

        return trim($data['motivo']);
    }

    /**
     * Guarda el motivo en la bitácora (no interrumpe la operación si la tabla aún no existe).
     */
    public static function registrar(string $entidad, $entidadId, ?string $nombre, string $accion, string $motivo): void
    {
        try {
            if (!Schema::hasTable('apicola_motivos_baja')) {
                return;
            }

            static::create([
                'entidad' => $entidad,
                'entidad_id' => $entidadId,
                'entidad_nombre' => $nombre,
                'accion' => $accion,
                'motivo' => $motivo,
                'user_id' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo registrar el motivo de baja: ' . $e->getMessage());
        }
    }
}
