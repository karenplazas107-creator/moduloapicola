<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Apicola\Entities\Apiario;
use Modules\Apicola\Entities\Colmena;
use Modules\Apicola\Entities\Produccion;

class ApicolaController extends Controller
{
    /**
     * Dashboard del Administrador Apícola (ApiGestión SENA).
     */
    public function dashboard()
    {
        $inicioMes = Carbon::now()->startOfMonth();

        // 1. Apiarios activos (y cuántos se registraron este mes)
        $totalApiarios = Apiario::activos()->count();
        $apiariosNuevosMes = Apiario::activos()->where('created_at', '>=', $inicioMes)->count();

        // 2. Colmenas (las que están activas) y cuántas se registraron este mes
        $totalColmenas = Colmena::where('status', '!=', 'Inactiva')->count();
        $colmenasProduccion = Colmena::where('status', 'Activa')->count();
        $colmenasNuevasMes = Colmena::where('status', '!=', 'Inactiva')->where('created_at', '>=', $inicioMes)->count();

        // 3. Producción de miel (kg): sale de Producción y de las inspecciones (a veces también se cosecha en una inspección)
        $mielTotal = round(Produccion::mielTotal(), 1);
        $mielDelMes = round(Produccion::mielTotal($inicioMes->toDateString(), Carbon::now()->endOfMonth()->toDateString()), 1);

        // 4. Inventario con stock bajo
        $totalInventarios = DB::table('apicola_inventarios')->where('estado', 'Activo')->whereNull('deleted_at')->count();
        $stockBajo = DB::table('apicola_inventarios')->where('estado', 'Activo')->whereNull('deleted_at')->whereRaw('cantidad <= stock_minimo')->count();

        // 5. Historial de producción de miel de los últimos meses
        $porMes = Produccion::mielPorMes(6);
        $chartLabels = $porMes['labels'];
        $chartData = $porMes['total'];

        // 6. Actividad reciente: solo lo que realmente se ha registrado, con su hora real
        $actividades = [];
        $agregar = function (string $tipo, string $icono, string $color, string $bg, string $titulo, string $detalle, $fecha) use (&$actividades) {
            if (!$fecha) {
                return;
            }
            $momento = Carbon::parse($fecha);
            $actividades[] = [
                'tipo' => $tipo,
                'icono' => $icono,
                'color' => $color,
                'bg' => $bg,
                'titulo' => $titulo,
                'detalle' => $detalle,
                'tiempo' => $momento->locale('es')->diffForHumans(),
                'timestamp' => $momento->timestamp,
            ];
        };

        foreach (Colmena::latest()->take(3)->get() as $col) {
            $agregar('colmena', 'fas fa-cubes-stacked', '#059669', '#ecfdf5', 'Nueva colmena registrada',
                ($col->apiario_name ?: 'Sin apiario') . ' - ' . $col->code, $col->created_at);
        }

        $inspecciones = DB::table('registro_inspeccion')
            ->leftJoin('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
            ->whereNull('registro_inspeccion.deleted_at')
            ->select('registro_inspeccion.miel_cosechada', 'registro_inspeccion.numero_registro', 'colmenas.codigo', 'registro_inspeccion.created_at')
            ->orderByDesc('registro_inspeccion.created_at')
            ->take(3)
            ->get();
        foreach ($inspecciones as $ins) {
            $agregar('inspeccion', 'fas fa-clipboard-check', '#0d9488', '#f0fdfa', 'Inspección registrada',
                ($ins->numero_registro ?: 'Inspección') . ' - Colmena ' . ($ins->codigo ?? ''), $ins->created_at);
        }

        $producciones = DB::table('apicola_producciones')
            ->leftJoin('apicola_colmenas', 'apicola_producciones.colmena_id', '=', 'apicola_colmenas.id')
            ->where('apicola_producciones.estado', 'Activa')
            ->select('apicola_producciones.miel_kg', 'apicola_colmenas.code', 'apicola_producciones.created_at')
            ->orderByDesc('apicola_producciones.created_at')
            ->take(3)
            ->get();
        foreach ($producciones as $pro) {
            $agregar('cosecha', 'fas fa-jar', '#d97706', '#fffbeb', 'Cosecha de miel registrada',
                rtrim(rtrim(number_format((float) $pro->miel_kg, 2, '.', ''), '0'), '.') . ' kg - Colmena ' . ($pro->code ?? ''), $pro->created_at);
        }

        $movimientos = DB::table('apicola_movimientos')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->take(3)
            ->get();
        foreach ($movimientos as $mov) {
            $agregar('inventario', 'fas fa-box-archive', '#0d9488', '#f0fdfa',
                ($mov->tipo === 'Salida' ? 'Salida' : 'Entrada') . ' de inventario',
                'Comprobante ' . $mov->numero_comprobante . ' - ' . $mov->total_unidades . ' unidades', $mov->created_at);
        }

        usort($actividades, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        $actividades = array_slice($actividades, 0, 5);

        return view('apicola::Admin.dashboard', compact(
            'totalApiarios',
            'apiariosNuevosMes',
            'totalColmenas',
            'colmenasProduccion',
            'colmenasNuevasMes',
            'mielTotal',
            'mielDelMes',
            'totalInventarios',
            'stockBajo',
            'chartLabels',
            'chartData',
            'actividades'
        ));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('apicola::Admin.dashboard');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('apicola::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('apicola::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('apicola::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
