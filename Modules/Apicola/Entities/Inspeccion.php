<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspeccion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registro_inspeccion';
    protected $primaryKey = 'id_registro';

    protected $fillable = [
        'id_aprendiz',
        'id_colmena',
        'numero_registro',
        'fecha',
        'miel_cosechada',
        'estado_colmena',
        'presencia_miel',
        'reina_vista',
        'reina_marcada',
        'color_reina',
        'alimentacion_suministrada',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'miel_cosechada' => 'decimal:2',
        'presencia_miel' => 'boolean',
        'reina_vista' => 'boolean',
        'reina_marcada' => 'boolean',
    ];

    /**
     * Relación con Colmena
     */
    public function colmena()
    {
        return $this->belongsTo(Colmena::class, 'id_colmena', 'id_colmenas');
    }
}
