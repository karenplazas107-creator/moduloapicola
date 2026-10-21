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
     * Almacena una nueva colmena registrada por el Aprendiz.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:apicola_colmenas,code',
            'apiario_name' => 'required|string|max:150',
            'responsable' => 'required|string|max:150',
            'tipo_abeja' => 'nullable|string|max:150',
            'installation_date' => 'required|date',
            'last_visit_date' => 'nullable|date',
            'status' => 'required|in:Activa,En revisión,Inactiva',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['last_visit_date'])) {
            $validated['last_visit_date'] = $validated['installation_date'];
        }

        if (empty($validated['tipo_abeja'])) {
            $validated['tipo_abeja'] = 'Apis mellifera';
        }

        $apiario = Apiario::where('nombre_apiario', trim($validated['apiario_name']))
            ->orWhere('id_apiarios', $request->input('apiario_id'))
            ->first();
        if ($apiario) {
            $validated['apiario_id'] = $apiario->id_apiarios;
            $validated['apiario_name'] = $apiario->nombre_apiario;
        } else {
            $defaultAp = Apiario::first();
            if ($defaultAp) {
                $validated['apiario_id'] = $defaultAp->id_apiarios;
                $validated['apiario_name'] = $defaultAp->nombre_apiario;
            }
        }

        $colmena = Colmena::create($validated);
        $this->syncToColmenasTable($colmena);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Colmena {$colmena->code} guardada con éxito.",
                'data' => $colmena
            ], 201);
        }

        return redirect()->back()->with('success', "Colmena {$colmena->code} guardada.");
    }

    /**
     * Actualiza una colmena.
     */
    public function update(Request $request, $id)
    {
        $colmena = Colmena::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:apicola_colmenas,code,' . $colmena->id,
            'apiario_name' => 'required|string|max:150',
            'responsable' => 'required|string|max:150',
            'tipo_abeja' => 'nullable|string|max:150',
            'installation_date' => 'required|date',
            'last_visit_date' => 'nullable|date',
            'status' => 'required|in:Activa,En revisión,Inactiva',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['last_visit_date'])) {
            $validated['last_visit_date'] = $colmena->last_visit_date ?? $validated['installation_date'];
        }

        if (empty($validated['tipo_abeja'])) {
            $validated['tipo_abeja'] = $colmena->tipo_abeja ?? 'Apis mellifera';
        }

        $apiario = Apiario::where('nombre_apiario', trim($validated['apiario_name']))
            ->orWhere('id_apiarios', $request->input('apiario_id'))
            ->first();
        if ($apiario) {
            $validated['apiario_id'] = $apiario->id_apiarios;
            $validated['apiario_name'] = $apiario->nombre_apiario;
        }

        $colmena->update($validated);
        $this->syncToColmenasTable($colmena);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Colmena {$colmena->code} actualizada correctamente.",
                'data' => $colmena
            ]);
        }

        return redirect()->back()->with('success', "Colmena {$colmena->code} actualizada.");
    }

    /**
     * Alterna el estado de una colmena.
     */
    public function toggleStatus($id)
    {
        $colmena = Colmena::findOrFail($id);
        $colmena->status = ($colmena->status === 'Inactiva') ? 'Activa' : 'Inactiva';
        $colmena->save();
        $this->syncToColmenasTable($colmena);

        return response()->json([
            'success' => true,
            'message' => "Estado de la colmena {$colmena->code} cambiado a {$colmena->status}.",
            'data' => $colmena
        ]);
    }

    /**
     * Desactiva una colmena (marca su estado como Inactiva sin eliminarla).
     */
    public function destroy(Request $request, $id)
    {
        $colmena = Colmena::findOrFail($id);
        $code = $colmena->code;
        $colmena->status = 'Inactiva';
        $colmena->save();
        $this->syncToColmenasTable($colmena, false);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Colmena {$code} desactivada con éxito.",
                'data' => $colmena
            ]);
        }

        return redirect()->back()->with('success', "Colmena {$code} desactivada con éxito.");
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
