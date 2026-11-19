<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'apicola_inventarios';

    protected $fillable = [
        'nombre',
        'codigo',
        'categoria',
        'bodega',
        'unidad_medida',
        'cantidad',
        'stock_minimo',
        'estado',
        'responsable',
        'notas',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_minimo' => 'integer',
    ];

    protected $appends = [
        'es_bajo_stock',
    ];

    /**
     * Relación con los movimientos del elemento
     */
    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class, 'inventario_id')->orderBy('fecha', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Atributo para saber si está en o por debajo del stock mínimo
     */
    public function getEsBajoStockAttribute(): bool
    {
        return $this->cantidad <= $this->stock_minimo;
    }

    /**
     * Scopes para filtrado
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    public function scopeInactivos($query)
    {
        return $query->where('estado', 'Inactivo');
    }

    public function scopeBajoStock($query)
    {
        return $query->whereColumn('cantidad', '<=', 'stock_minimo');
    }
    /**
     * Catálogo de elementos agrupado por tipo (Herramienta / Insumo): los elementos base del
     * apiario más los ya registrados en el inventario según su propio tipo.
     */
    public static function catalogoPorTipo(): array
    {
        $base = [
            'Herramienta' => ['Traje apícola', 'Ahumador', 'Palanca apícola', 'Cepillo apícola', 'Guantes de cuero', 'Pala', 'Alza'],
            'Insumo' => ['Cera estampada', 'Cuadros', 'Azúcar', 'Jarabe de alimentación', 'Frascos para miel', 'Ácido oxálico'],
        ];

        $catalogo = [];
        foreach ($base as $tipo => $nombres) {
            $registrados = static::where('categoria', $tipo)->orderBy('nombre')->pluck('nombre')->all();
            $unicos = [];
            foreach (array_merge($nombres, $registrados) as $n) {
                $n = trim((string) $n);
                if ($n !== '' && !isset($unicos[mb_strtolower($n)])) {
                    $unicos[mb_strtolower($n)] = $n;
                }
            }
            $catalogo[$tipo] = array_values($unicos);
        }

        return $catalogo;
    }
}
