<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bodegas del módulo. La bodega del apiario siempre existe; se pueden registrar más.
 * Cuando hay una sola bodega la interfaz oculta todo lo relacionado con elegir/mover entre bodegas.
 */
class Bodega extends Model
{
    public const PRINCIPAL = 'Bodega del apiario';

    protected $table = 'apicola_bodegas';

    protected $fillable = ['nombre'];

    /**
     * Nombres de todas las bodegas, sin duplicados (sin distinguir mayúsculas), la principal primero.
     */
    public static function nombres(): array
    {
        $nombres = [self::PRINCIPAL];

        if (Schema::hasTable('apicola_bodegas')) {
            $nombres = array_merge($nombres, static::orderBy('nombre')->pluck('nombre')->all());
        }

        foreach (['apicola_inventarios', 'apicola_movimientos'] as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'bodega')) {
                $nombres = array_merge(
                    $nombres,
                    DB::table($tabla)->whereNotNull('bodega')->where('bodega', '!=', '')->distinct()->pluck('bodega')->all()
                );
            }
        }

        $unicos = [];
        foreach ($nombres as $n) {
            $n = trim((string) $n);
            if ($n !== '' && !isset($unicos[mb_strtolower($n)])) {
                $unicos[mb_strtolower($n)] = $n;
            }
        }

        return array_values($unicos);
    }

    public static function hayVarias(): bool
    {
        return count(static::nombres()) > 1;
    }

    /**
     * Registra la bodega si no existe y devuelve su nombre canónico.
     */
    public static function registrar(string $nombre): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));

        foreach (static::nombres() as $existente) {
            if (mb_strtolower($existente) === mb_strtolower($nombre)) {
                if (Schema::hasTable('apicola_bodegas')) {
                    static::firstOrCreate(['nombre' => $existente]);
                }
                return $existente;
            }
        }

        if (Schema::hasTable('apicola_bodegas')) {
            static::create(['nombre' => $nombre]);
        }

        return $nombre;
    }
}
