<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoDetalle extends Model
{
    use HasFactory;

    protected $table = 'apicola_movimiento_detalles';

    protected $fillable = [
        'movimiento_id',
        'inventario_id',
        'producto_nombre',
        'producto_codigo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
    ];

    /**
     * Comprobante al que pertenece este detalle
     */
    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }

    /**
     * Elemento del inventario asociado
     */
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }
}

