<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Modules\Apicola\Entities\Apiario;
use Modules\Apicola\Entities\Bodega;
use Modules\Apicola\Entities\MotivoBaja;
use Modules\Apicola\Entities\Colmena;
use Modules\Apicola\Entities\Inspeccion;
use Modules\Apicola\Entities\Inventario;
use Modules\Apicola\Entities\Produccion;

class ReporteController extends Controller
{
    /**
     * Muestra la vista principal del Generador de Reportes con datos 100% reales de la base de datos.
     */
    public function index(Request $request)
    {
        // 1. Apiarios reales registrados
        $apiarios = Apiario::orderBy('nombre_apiario', 'asc')->get();

        // 2. Rango de fechas por defecto (desde inicio del año en curso hasta fin de mes)
        $desde = $request->get('desde', Carbon::now()->startOfYear()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $tipoReporte = $request->get('tipo', 'miel'); // miel, colmenas, inventario
        $apiarioFiltro = $request->get('apiario', 'all');
        $estadoFiltro = $request->get('estado', 'all');

        // 3. Métricas reales calculadas desde la base de datos
        $metrics = $this->calculateMetrics($desde, $hasta);

        // 4. Historial de reportes
        $ultimosReportes = $this->getUltimosReportes();

        // 5. Cargar datos reales según tipo y filtros
        $previewData = $this->getReportData($tipoReporte, $apiarioFiltro, $desde, $hasta, $estadoFiltro);

        // Listas para los filtros que cambian según el tipo de reporte
        $bodegas = Bodega::nombres();

        return view('apicola::Admin.Reportes.index', compact(
            'bodegas',
            'apiarios',
            'desde',
            'hasta',
            'tipoReporte',
            'apiarioFiltro',
            'estadoFiltro',
            'metrics',
            'ultimosReportes',
            'previewData'
        ));
    }

    /**
     * Endpoint AJAX para consultar datos reales en tiempo real sin recargar página.
     */
    public function previewData(Request $request)
    {
        $tipo = $request->get('tipo', 'miel');
        $apiario = $request->get('apiario', 'all');
        $desde = $request->get('desde', Carbon::now()->startOfYear()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $estado = $request->get('estado', 'all');

        $data = $this->getReportData($tipo, $apiario, $desde, $hasta, $estado);

        return response()->json([
            'success' => true,
            'tipo' => $tipo,
            'count' => count($data),
            'data' => $data
        ]);
    }

    /**
     * Exportar reporte a formato Excel (.xls con formato oficial institucional SENA, colores, bordes y resumen).
     */
    public function exportExcel(Request $request)
    {
        $tipo = $request->get('tipo', 'miel');
        $apiario = $request->get('apiario', 'all');
        $desde = $request->get('desde', Carbon::now()->startOfYear()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $estado = $request->get('estado', 'all');

        $data = $this->getReportData($tipo, $apiario, $desde, $hasta, $estado);
        $fileName = 'Reporte_' . ucfirst($tipo) . '_' . date('Ymd_His') . '.xls';

        // Métricas para totales y resumen oficial
        $totalVisitas = array_sum(array_column($data, 'visitas'));
        $totalMiel = (float)array_sum(array_column($data, 'miel_kg'));
        $totalColmenas = count($data);
        $colmenasActivas = count(array_filter($data, fn($r) => strcasecmp($r['estado'] ?? '', 'Activa') === 0));
        $colmenasInactivas = count(array_filter($data, fn($r) => strcasecmp($r['estado'] ?? '', 'Inactiva') === 0));
        $promedioMiel = $totalColmenas > 0 ? ($totalMiel / $totalColmenas) : 0;

        $autor = Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'SERGIO BARRERA');
        $fechaEmision = Carbon::now()->format('d/m/Y H:i:s');

        $html = view('apicola::Admin.Reportes.excel', compact(
            'tipo',
            'apiario',
            'desde',
            'hasta',
            'estado',
            'data',
            'totalVisitas',
            'totalMiel',
            'totalColmenas',
            'colmenasActivas',
            'colmenasInactivas',
            'promedioMiel',
            'autor',
            'fechaEmision'
        ))->render();

        $content = "\xEF\xBB\xBF" . $html;

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Vista de impresión compacta y elegante para guardar como PDF o imprimir en 1 página.
     */
    public function exportPdf(Request $request)
    {
        $tipo = $request->get('tipo', 'miel');
        $apiario = $request->get('apiario', 'all');
        $desde = $request->get('desde', Carbon::now()->startOfYear()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $estado = $request->get('estado', 'all');

        $data = $this->getReportData($tipo, $apiario, $desde, $hasta, $estado);
        $metrics = $this->calculateMetrics($desde, $hasta);

        return view('apicola::Admin.Reportes.print', compact(
            'tipo',
            'apiario',
            'desde',
            'hasta',
            'estado',
            'data',
            'metrics'
        ));
    }

    /**
     * Calcular métricas reales directamente desde las tablas.
     */
    private function calculateMetrics($desde, $hasta)
    {
        $mielTotal = 0;
        $visitasTotal = 0;
        $apiariosTotal = 0;

        try {
            // Miel = Producción + miel registrada dentro de las inspecciones
            $mielTotal = Produccion::mielTotal($desde, $hasta);

            if (\Illuminate\Support\Facades\Schema::hasTable('registro_inspeccion')) {
                $query = DB::table('registro_inspeccion')->whereNull('deleted_at');
                if ($desde && $hasta) {
                    $query->whereBetween('fecha', [$desde, $hasta]);
                }
                $visitasTotal = (int) $query->count();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('apiario')) {
                $apiariosTotal = DB::table('apiario')->whereNull('deleted_at')->count();
            }
        } catch (\Exception $e) {
            // Silencio de error
        }

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $desdeCarbon = Carbon::parse($desde);
        $hastaCarbon = Carbon::parse($hasta);
        $periodoLabel = $meses[$desdeCarbon->month] . ' a ' . $meses[$hastaCarbon->month] . ' de ' . $hastaCarbon->year;

        return [
            'miel_kg' => number_format($mielTotal, 1, ',', '.'),
            'miel_raw' => $mielTotal,
            'periodo_label' => $periodoLabel,
            'visitas_total' => $visitasTotal,
            'apiarios_activos' => $apiariosTotal,
            'reportes_generados' => 1,
        ];
    }

    /**
     * Historial de reportes disponibles para descargar.
     */
    private function getUltimosReportes()
    {
        return [
            'miel' => [
                [
                    'id' => 1,
                    'titulo' => 'Reporte Oficial de Producción de Miel',
                    'formato' => 'PDF',
                    'registros' => DB::table('apicola_producciones')->where('estado', 'Activa')->count()
                        + DB::table('registro_inspeccion')->whereNull('deleted_at')->where('miel_cosechada', '>', 0)->count(),
                    'autor' => Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Sergio Barrera'),
                    'tipo' => 'miel',
                    'fecha' => Carbon::now()->format('d/m/Y')
                ]
            ],
            'inspeccion' => [
                [
                    'id' => 4,
                    'titulo' => 'Reporte Oficial de Inspecciones de Colmenas',
                    'formato' => 'PDF',
                    'registros' => DB::table('registro_inspeccion')->whereNull('deleted_at')->count(),
                    'autor' => Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Sergio Barrera'),
                    'tipo' => 'inspeccion',
                    'fecha' => Carbon::now()->format('d/m/Y')
                ]
            ],
            'colmenas' => [
                [
                    'id' => 2,
                    'titulo' => 'Censo General y Estado de Colmenas',
                    'formato' => 'PDF',
                    'registros' => DB::table('apicola_colmenas')->whereNull('deleted_at')->count(),
                    'autor' => Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Sergio Barrera'),
                    'tipo' => 'colmenas',
                    'fecha' => Carbon::now()->format('d/m/Y')
                ]
            ],
            'bajas' => [
                [
                    'id' => 5,
                    'titulo' => 'Historial de Bajas (desactivaciones y eliminaciones)',
                    'formato' => 'PDF',
                    'registros' => DB::table('apicola_motivos_baja')->count(),
                    'autor' => Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Sergio Barrera'),
                    'tipo' => 'bajas',
                    'fecha' => Carbon::now()->format('d/m/Y')
                ]
            ],
            'inventario' => [
                [
                    'id' => 3,
                    'titulo' => 'Inventario Oficial de Herramientas e Insumos',
                    'formato' => 'PDF',
                    'registros' => DB::table('apicola_inventarios')->whereNull('deleted_at')->count(),
                    'autor' => Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Sergio Barrera'),
                    'tipo' => 'inventario',
                    'fecha' => Carbon::now()->format('d/m/Y')
                ]
            ]
        ];
    }

    /**
     * Consulta principal según el tipo de reporte.
     */
    private function getReportData($tipo, $apiario, $desde, $hasta, $estado)
    {
        if ($tipo === 'miel' || $tipo === 'produccion') {
            return $this->getProduccionMielData($apiario, $desde, $hasta, $estado);
        } elseif ($tipo === 'inspeccion') {
            return $this->getInspeccionData($apiario, $desde, $hasta, $estado);
        } elseif ($tipo === 'colmenas') {
            return $this->getEstadoColmenasData($apiario, $desde, $hasta, $estado);
        } elseif ($tipo === 'bajas') {
            return $this->getBajasData($apiario, $desde, $hasta, $estado);
        } else {
            return $this->getInventarioData($apiario, $estado);
        }
    }

    /**
     * Miel por colmena: suma lo registrado en Producción y lo cosechado durante inspecciones.
     * "Cosechas" cuenta ambos tipos de registro.
     */
    private function getProduccionMielData($apiario, $desde, $hasta, $estado)
    {
        $porColmena = [];

        try {
            $colmenas = DB::table('apicola_colmenas')->whereNull('deleted_at')->get()->keyBy('code');

            // 1) Registros del submódulo Producción
            if (\Illuminate\Support\Facades\Schema::hasTable('apicola_producciones')) {
                $q = DB::table('apicola_producciones')
                    ->join('apicola_colmenas', 'apicola_producciones.colmena_id', '=', 'apicola_colmenas.id')
                    ->where('apicola_producciones.estado', 'Activa')
                    ->select('apicola_producciones.fecha', 'apicola_producciones.miel_kg', 'apicola_producciones.responsable', 'apicola_colmenas.code as codigo');
                if ($desde && $hasta) {
                    $q->whereBetween('apicola_producciones.fecha', [$desde, $hasta]);
                }
                foreach ($q->get() as $r) {
                    $porColmena[$r->codigo][] = ['fecha' => $r->fecha, 'kg' => (float) $r->miel_kg, 'responsable' => $r->responsable, 'origen' => 'produccion'];
                }
            }

            // 2) Miel cosechada durante inspecciones (solo las que registraron miel)
            if (\Illuminate\Support\Facades\Schema::hasTable('registro_inspeccion')) {
                $q = DB::table('registro_inspeccion')
                    ->join('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
                    ->whereNull('registro_inspeccion.deleted_at')
                    ->where('registro_inspeccion.miel_cosechada', '>', 0)
                    ->select('registro_inspeccion.fecha', 'registro_inspeccion.miel_cosechada', 'registro_inspeccion.observaciones', 'colmenas.codigo');
                if ($desde && $hasta) {
                    $q->whereBetween('registro_inspeccion.fecha', [$desde, $hasta]);
                }
                foreach ($q->get() as $r) {
                    $resp = null;
                    if (!empty($r->observaciones) && preg_match('/Responsable:\s*([^|]+)/', $r->observaciones, $m)) {
                        $resp = trim($m[1]);
                    }
                    $porColmena[$r->codigo][] = ['fecha' => $r->fecha, 'kg' => (float) $r->miel_cosechada, 'responsable' => $resp, 'origen' => 'inspeccion'];
                }
            }
        } catch (\Exception $e) {
            $colmenas = collect();
        }

        $rows = [];
        foreach ($porColmena as $code => $registros) {
            usort($registros, fn ($a, $b) => strcmp($b['fecha'], $a['fecha']));
            $ultimo = $registros[0];
            $col = $colmenas[$code] ?? null;

            $rows[] = [
                'apiario' => $col->apiario_name ?? 'Apiario SENA La Angostura',
                'colmena' => $code,
                'ultima_visita' => Carbon::parse($ultimo['fecha'])->format('d/m/Y'),   // última cosecha
                'visitas' => count($registros),                                         // cosechas (producción + inspecciones)
                'miel_kg' => round(array_sum(array_column($registros, 'kg')), 2),
                'responsable' => $ultimo['responsable'] ?: ($col->responsable ?? 'Sergio Barrera'),
                'estado' => $col->status ?? 'Activa',
            ];
        }

        usort($rows, fn ($a, $b) => strcmp($a['colmena'], $b['colmena']));

        if ($apiario !== 'all') {
            $rows = array_filter($rows, function ($item) use ($apiario) {
                return stripos($item['apiario'], $apiario) !== false || $item['apiario'] == $apiario;
            });
        }

        if ($estado !== 'all') {
            $rows = array_filter($rows, function ($item) use ($estado) {
                return strcasecmp($item['estado'], $estado) === 0;
            });
        }

        return array_values($rows);
    }

    /**
     * Inspecciones de colmenas: una fila por inspección vigente con los datos que se registran en ella.
     */
    private function getInspeccionData($apiario, $desde, $hasta, $estado)
    {
        $rows = [];

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('registro_inspeccion')) {
                $query = DB::table('registro_inspeccion')
                    ->leftJoin('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
                    ->leftJoin('apicola_colmenas', 'colmenas.codigo', '=', 'apicola_colmenas.code')
                    ->whereNull('registro_inspeccion.deleted_at')
                    ->select(
                        'registro_inspeccion.*',
                        'colmenas.codigo as colmena_codigo',
                        'apicola_colmenas.apiario_name',
                        'apicola_colmenas.responsable as colmena_responsable'
                    );

                if ($desde && $hasta) {
                    $query->whereBetween('registro_inspeccion.fecha', [$desde, $hasta]);
                }
                if ($apiario !== 'all') {
                    $query->where('apicola_colmenas.apiario_name', $apiario);
                }
                if ($estado !== 'all') {
                    $query->where('registro_inspeccion.estado_colmena', $estado);
                }

                $records = $query->orderBy('registro_inspeccion.fecha', 'desc')->orderBy('registro_inspeccion.id_registro', 'desc')->get();

                $enfermedades = DB::table('enfermedades_registro')->get()->groupBy('id_registro');

                foreach ($records as $r) {
                    $responsable = $r->colmena_responsable ?: 'Sergio Barrera';
                    if (!empty($r->observaciones) && preg_match('/Responsable:\s*([^|]+)/', $r->observaciones, $m)) {
                        $responsable = trim($m[1]);
                    }

                    $enfs = isset($enfermedades[$r->id_registro])
                        ? $enfermedades[$r->id_registro]->pluck('nombre_enfermedad')->filter()->implode(', ')
                        : '';

                    $reina = $r->reina_vista ? 'Vista' : 'No vista';
                    if ($r->reina_marcada) {
                        $reina .= ' · marcada' . (!empty($r->color_reina) && $r->color_reina !== 'Sin marcar' ? ' (' . $r->color_reina . ')' : '');
                    }

                    $rows[] = [
                        'fecha' => Carbon::parse($r->fecha)->format('d/m/Y'),
                        'registro' => $r->numero_registro ?: 'REG-' . str_pad($r->id_registro, 4, '0', STR_PAD_LEFT),
                        'colmena' => $r->colmena_codigo ?: '-',
                        'apiario' => $r->apiario_name ?: 'Apiario SENA La Angostura',
                        'estado' => $r->estado_colmena ?: 'Bueno',
                        'enfermedades' => $enfs,
                        'reina' => $reina,
                        'responsable' => $responsable,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silencio
        }

        return $rows;
    }

    /**
     * Historial de bajas: toda desactivación o eliminación con su motivo, fecha y quién la hizo.
     * Aquí el filtro "apiario" es el tipo de elemento y el filtro "estado" es la acción.
     */
    private function getBajasData($tipoElemento, $desde, $hasta, $accion)
    {
        $rows = [];

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('apicola_motivos_baja')) {
                return $rows;
            }

            $query = DB::table('apicola_motivos_baja');

            if ($desde && $hasta) {
                $query->whereBetween('created_at', [Carbon::parse($desde)->startOfDay(), Carbon::parse($hasta)->endOfDay()]);
            }
            if ($tipoElemento !== 'all') {
                $query->where('entidad', $tipoElemento);
            }
            if ($accion !== 'all') {
                $query->where('accion', $accion);
            }

            $registros = $query->orderByDesc('created_at')->orderByDesc('id')->get();

            $usuarios = \App\Models\User::whereIn('id', $registros->pluck('user_id')->filter()->unique())->get()->keyBy('id');

            foreach ($registros as $r) {
                $u = $usuarios[$r->user_id] ?? null;
                $quien = $u ? (trim((string) $u->full_name) ?: $u->nickname) : 'No registrado';

                $rows[] = [
                    'fecha' => Carbon::parse($r->created_at)->format('d/m/Y H:i'),
                    'tipo' => MotivoBaja::etiquetaTipo($r->entidad),
                    'elemento' => $r->entidad_nombre ?: '-',
                    'accion' => $r->accion === 'eliminar' ? 'Eliminación' : 'Desactivación',
                    'motivo' => $r->motivo,
                    'usuario' => $quien,
                ];
            }
        } catch (\Exception $e) {
            // Silencio
        }

        return $rows;
    }

    /**
     * Datos 100% reales de Estado de Colmenas desde apicola_colmenas.
     */
    private function getEstadoColmenasData($apiario, $desde, $hasta, $estado)
    {
        $rows = [];

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('apicola_colmenas')) {
                $query = DB::table('apicola_colmenas')->whereNull('deleted_at');

                if ($apiario !== 'all') {
                    $query->where('apiario_name', $apiario);
                }

                if ($estado !== 'all') {
                    $query->where('status', $estado);
                }

                $colmenas = $query->orderBy('code', 'asc')->get();

                foreach ($colmenas as $c) {
                    $rows[] = [
                        'codigo' => $c->code,
                        'apiario' => $c->apiario_name ?: 'Apiario SENA La Angostura',
                        'tipo_abeja' => $c->tipo_abeja ?: 'Apis mellifera',
                        'instalacion' => $c->installation_date ? Carbon::parse($c->installation_date)->format('d/m/Y') : '-',
                        'ultima_visita' => $c->last_visit_date ? Carbon::parse($c->last_visit_date)->format('d/m/Y') : '-',
                        'estado' => $c->status ?: 'Activa',
                        'responsable' => $c->responsable ?: 'Sergio Barrera'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silencio
        }

        return $rows;
    }

    /**
     * Datos 100% reales de Inventario desde apicola_inventarios.
     */
    private function getInventarioData($bodega, $estado)
    {
        $rows = [];

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('apicola_inventarios')) {
                $query = DB::table('apicola_inventarios')->whereNull('deleted_at');

                if ($bodega !== 'all') {
                    $query->where('bodega', $bodega);
                }

                if ($estado !== 'all') {
                    $query->where('estado', $estado);
                }

                $items = $query->orderBy('nombre', 'asc')->get();

                foreach ($items as $item) {
                    $rows[] = [
                        'codigo' => $item->codigo ?: 'INV-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                        'nombre' => $item->nombre,
                        'categoria' => $item->categoria ?: 'General',
                        'bodega' => $item->bodega ?: 'Bodega del Apiario',
                        'cantidad' => (int)$item->cantidad,
                        'stock_minimo' => (int)$item->stock_minimo,
                        'unidad' => $item->unidad_medida ?: 'unidad',
                        'estado' => $item->estado ?: 'Activo',
                        'responsable' => $item->responsable ?: 'Sergio Barrera'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silencio
        }

        return $rows;
    }
}
