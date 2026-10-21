<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apiario extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabla física en la base de datos MySQL
     */
    protected $table = 'apiario';

    /**
     * Llave primaria definida en la migración
     */
    protected $primaryKey = 'id_apiarios';

    /**
     * Atributos asignables masivamente
     */
    protected $fillable = [
        'nombre_apiario',
        'responsable',
        'latitud',
        'longitud',
        'estado',
    ];

    /**
     * Conversión de tipos
     */
    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    /**
     * Scope para filtrar apiarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * Scope para filtrar apiarios inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado', 'Inactivo');
    }

    /**
     * Relación con colmenas del módulo
     */
    public function colmenas()
    {
        return $this->hasMany(Colmena::class, 'apiario_id', 'id_apiarios');
    }
}
