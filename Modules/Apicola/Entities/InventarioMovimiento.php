<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventarioMovimiento extends Model
{
    use HasFactory;

    protected $table = 'apicola_inventario_movimientos';

    protected $fillable = [
        'inventario_id',
        'movimiento_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'responsable',
        'fecha',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
        'fecha' => 'datetime',
    ];

    /**
     * Relación con el elemento de inventario
     */
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }
}
