<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;

class CalendarioFloral extends Model
{
    protected $table = 'calendario_floral';
    protected $primaryKey = 'id_calendario';

    protected $fillable = [
        'id_especie',
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'meses',
        'etapa',
    ];

    protected $casts = [
        'meses' => 'array',
        'anio' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Especie melífera asociada
     */
    public function especie()
    {
        return $this->belongsTo(EspecieMelifera::class, 'id_especie', 'id_especie');
    }
}
