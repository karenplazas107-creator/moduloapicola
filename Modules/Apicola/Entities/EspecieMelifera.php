<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EspecieMelifera extends Model
{
    use SoftDeletes;

    protected $table = 'especie_malifera';
    protected $primaryKey = 'id_especie';

    protected $fillable = [
        'nombre_comun',
        'nombre_cientifico',
        'descripcion',
        'aporte',
        'estado',
    ];

    /**
     * Registros de floración asociados a esta especie
     */
    public function calendarios()
    {
        return $this->hasMany(CalendarioFloral::class, 'id_especie', 'id_especie');
    }
}
