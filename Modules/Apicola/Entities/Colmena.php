<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Colmena extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'apicola_colmenas';

    protected $fillable = [
        'code',
        'apiario_name',
        'apiario_id',
        'responsable',
        'tipo_abeja',
        'installation_date',
        'last_visit_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'last_visit_date' => 'date',
    ];

    /**
     * Scopes para filtrado rápido de estados
     */
    public function scopeActivas($query)
    {
        return $query->where('status', 'Activa');
    }

    public function scopeEnRevision($query)
    {
        return $query->where('status', 'En revisión');
    }

    public function scopeInactivas($query)
    {
        return $query->where('status', 'Inactiva');
    }

    public function apiario()
    {
        return $this->belongsTo(Apiario::class, 'apiario_id', 'id_apiarios');
    }

    /**
     * Devuelve el motivo por el que NO se pueden registrar inspecciones ni producciones en esta colmena,
     * o null si se puede (colmena activa y en un apiario activo).
     */
    public function motivoNoRegistrable(): ?string
    {
        if ($this->status === 'Inactiva') {
            return "La colmena {$this->code} está inactiva: no se pueden registrar inspecciones ni producciones.";
        }

        $apiario = $this->apiario;
        if ($apiario && $apiario->estado === 'Inactivo') {
            return "El apiario \"{$apiario->nombre_apiario}\" está desactivado: no se pueden registrar inspecciones ni producciones en sus colmenas.";
        }

        return null;
    }

    /**
     * Actualiza "última visita" con la fecha de la inspección vigente más reciente de la colmena.
     */
    public static function recalcularUltimaVisita(?string $code): void
    {
        if (!$code) {
            return;
        }

        try {
            $colmena = static::where('code', $code)->first();
            $idLegacy = \Illuminate\Support\Facades\DB::table('colmenas')->where('codigo', $code)->value('id_colmenas');
            if (!$colmena || !$idLegacy) {
                return;
            }

            $ultima = \Illuminate\Support\Facades\DB::table('registro_inspeccion')
                ->where('id_colmena', $idLegacy)
                ->whereNull('deleted_at')
                ->max('fecha');

            $colmena->last_visit_date = $ultima ?: $colmena->installation_date;
            $colmena->save();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("No se pudo actualizar la última visita de {$code}: " . $e->getMessage());
        }
    }
}
