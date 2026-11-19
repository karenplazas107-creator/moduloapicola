<?php

namespace Modules\Apicola\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Responsables del módulo apícola. Es la lista única que alimenta los selectores de
 * apiarios, colmenas e inspecciones: lo que se agregue en uno aparece en todos.
 */
class Responsable extends Model
{
    public const BASE = 'SERGIO BARRERA';

    protected $table = 'apicola_responsables';

    protected $fillable = ['nombre'];

    /**
     * Lista unificada y sin duplicados (sin distinguir mayúsculas): el responsable base, los
     * registrados en la tabla y los que ya figuran en apiarios y colmenas.
     */
    public static function todos(): array
    {
        $nombres = [self::BASE];

        if (Schema::hasTable('apicola_responsables')) {
            $nombres = array_merge($nombres, static::orderBy('nombre')->pluck('nombre')->all());
        }

        foreach (['apiario', 'apicola_colmenas'] as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'responsable')) {
                $nombres = array_merge(
                    $nombres,
                    DB::table($tabla)->whereNotNull('responsable')->where('responsable', '!=', '')->distinct()->pluck('responsable')->all()
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

        $lista = array_values($unicos);
        usort($lista, function ($a, $b) {
            if (mb_strtolower($a) === mb_strtolower(self::BASE)) return -1;
            if (mb_strtolower($b) === mb_strtolower(self::BASE)) return 1;
            return strcasecmp($a, $b);
        });

        return $lista;
    }

    /**
     * Registra un responsable si no existe (sin distinguir mayúsculas) y devuelve su nombre canónico.
     */
    public static function registrar(string $nombre): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));

        foreach (static::todos() as $existente) {
            if (mb_strtolower($existente) === mb_strtolower($nombre)) {
                static::firstOrCreate(['nombre' => $existente]);
                return $existente;
            }
        }

        static::create(['nombre' => $nombre]);
        return $nombre;
    }
}
