<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Movimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'apicola_movimientos';

    protected $fillable = [
        'numero_comprobante',
        'tipo',
        'bodega',
        'fecha',
        'total_productos',
        'total_unidades',
        'responsable',
        'user_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_productos' => 'integer',
        'total_unidades' => 'integer',
    ];

    /**
     * Detalles o renglones de productos incluidos en este comprobante
     */
    public function detalles()
    {
        return $this->hasMany(MovimientoDetalle::class, 'movimiento_id');
    }

    /**
     * Usuario que registró el comprobante
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Movimientos históricos de inventario vinculados
     */
    public function inventarioMovimientos()
    {
        return $this->hasMany(InventarioMovimiento::class, 'movimiento_id');
    }

    /**
     * Genera el siguiente número de comprobante único para el año actual (ej: 0001-2026)
     */
    public static function generarSiguienteNumero($anio = null): string
    {
        $anio = $anio ?: date('Y');
        
        $ultimo = self::withTrashed()
            ->where('numero_comprobante', 'like', "%-{$anio}")
            ->orderBy('id', 'desc')
            ->first();

        $numero = 1;
        if ($ultimo && preg_match('/^(\d+)-' . $anio . '$/', $ultimo->numero_comprobante, $matches)) {
            $numero = intval($matches[1]) + 1;
        }

        return sprintf('%04d-%s', $numero, $anio);
    }
}

