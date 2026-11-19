<?php

namespace Modules\Evidencias\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Evidencia extends Model
{
    use SoftDeletes;

    protected $table = 'evidencias';

    protected $fillable = [
        'user_id',
        'titulo',
        'descripcion',
        'archivo',
        'tipo',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Estados disponibles para una evidencia.
     */
    public const ESTADOS = [
        'pendiente'  => 'Pendiente',
        'aprobada'   => 'Aprobada',
        'rechazada'  => 'Rechazada',
    ];

    /**
     * Tipos de evidencia disponibles.
     */
    public const TIPOS = [
        'documento' => 'Documento',
        'imagen'    => 'Imagen',
        'video'     => 'Video',
        'otro'      => 'Otro',
    ];

    /**
     * Relación: una evidencia pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: devuelve la etiqueta legible del estado.
     */
    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? ucfirst($this->estado);
    }

    /**
     * Accessor: clase Bootstrap badge según el estado.
     */
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'aprobada'  => 'success',
            'rechazada' => 'danger',
            default     => 'warning',
        };
    }
}
