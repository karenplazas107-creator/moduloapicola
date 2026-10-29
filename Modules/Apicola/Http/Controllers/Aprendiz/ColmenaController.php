<?php

namespace Modules\Apicola\Http\Controllers\Aprendiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Apicola\Entities\Colmena;
use Modules\Apicola\Entities\Apiario;
use Modules\Apicola\Entities\Inspeccion;

class ColmenaController extends Controller
{
    /**
     * Muestra la vista de Gestión de colmenas exclusiva para el Aprendiz.
     */
    public function index(Request $request)
    {
        $query = Colmena::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('responsable', 'like', "%{$search}%")
                  ->orWhere('apiario_name', 'like', "%{$search}%")
                  ->orWhere('tipo_abeja', 'like', "%{$search}%");
            });
        }

        if ($request->filled('apiario') && $request->apiario !== 'all') {
            $query->where('apiario_name', $request->apiario);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $colmenas = $query->orderBy('code', 'asc')->get();

        $metrics = [
            'total' => Colmena::count(),
            'activas' => Colmena::activas()->count(),
            'en_revision' => Colmena::enRevision()->count(),
            'inactivas' => Colmena::inactivas()->count(),
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $colmenas,
                'metrics' => $metrics,
            ]);
        }

        $apiarios = Apiario::activos()->get();
        if ($apiarios->isEmpty()) {
            $apiarios = Apiario::all();
        }

        $tiposAbejaBase = ['Apis mellifera', 'Melipona eburnea', 'Tetragonisca angustula', 'Scaptotrigona pectoralis'];
        $tiposAbejaDb = Colmena::whereNotNull('tipo_abeja')->where('tipo_abeja', '!=', '')->distinct()->pluck('tipo_abeja')->toArray();
        $tiposAbeja = array_values(array_unique(array_filter(array_merge($tiposAbejaBase, $tiposAbejaDb))));

        return view('apicola::Aprendiz.Colmenas.index', compact('colmenas', 'metrics', 'apiarios', 'tiposAbeja'));
    }

    /**
     * El aprendiz solo tiene permisos de consulta (no puede registrar colmenas).
     */
    public function store(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'El rol de aprendiz solo tiene permisos de consulta en colmenas. No puede registrar nuevas colmenas.'
            ], 403);
        }

        return redirect()->back()->with('error', 'El rol de aprendiz no tiene permisos para registrar colmenas.');
    }

    /**
     * El aprendiz solo tiene permisos de consulta (no puede editar colmenas).
     */
    public function update(Request $request, $id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'El rol de aprendiz solo tiene permisos de consulta en colmenas. No puede editar colmenas.'
            ], 403);
        }

        return redirect()->back()->with('error', 'El rol de aprendiz no tiene permisos para editar colmenas.');
    }

    /**
     * El aprendiz no tiene permisos para cambiar el estado o desactivar colmenas.
     */
    public function toggleStatus($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'El rol de aprendiz no tiene permisos para desactivar o modificar el estado de las colmenas.'
        ], 403);
    }

    /**
     * El aprendiz no tiene permisos para desactivar colmenas.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'El rol de aprendiz no tiene permisos para desactivar colmenas.'
            ], 403);
        }

        return redirect()->back()->with('error', 'El rol de aprendiz no tiene permisos para desactivar colmenas.');
    }

    /**
     * Sincroniza la colmena con la tabla física 'colmenas' de la base de datos MySQL.
     */
    protected function syncToColmenasTable($colmena, $isDelete = false)
    {
        try {
            if ($isDelete) {
                \Illuminate\Support\Facades\DB::table('colmenas')
                    ->where('codigo', $colmena->code)
                    ->orWhere('id_colmenas', $colmena->id)
                    ->delete();
                return;
            }

            $apiarioId = $colmena->apiario_id;
            if (!$apiarioId) {
                $ap = Apiario::where('nombre_apiario', $colmena->apiario_name)->first();
                $apiarioId = $ap ? $ap->id_apiarios : (Apiario::first()->id_apiarios ?? 1);
            }

            \Illuminate\Support\Facades\DB::table('colmenas')->updateOrInsert(
                ['codigo' => $colmena->code],
                [
                    'fecha_instalacion' => $colmena->installation_date,
                    'estado' => $colmena->status,
                    'id_apiario' => $apiarioId,
                    'updated_at' => now(),
                    'created_at' => $colmena->created_at ?? now(),
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error sincronizando colmena {$colmena->code} con tabla colmenas: " . $e->getMessage());
        }
    }
}
