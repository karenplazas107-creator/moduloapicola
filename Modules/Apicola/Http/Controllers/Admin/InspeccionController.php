<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Apicola\Entities\Inspeccion;
use Modules\Apicola\Entities\Colmena;

class InspeccionController extends Controller
{
    /**
     * Muestra la vista de Registro e Inspección de colmenas.
     */
    public function index(Request $request)
    {
        $colmenas = Colmena::orderBy('code', 'asc')->get();
        $firstCode = $colmenas->first() ? ($colmenas->first()->code ?? $colmenas->first()->codigo ?? '') : '';
        $selectedColmena = $request->get('colmena', $firstCode);
        $visitasInit = $this->getVisitasFromDb();
        return view('apicola::Admin.Colmenas.inspeccion', compact('selectedColmena', 'colmenas', 'visitasInit'));
    }

    /**
     * Inspecciones de una colmena específica por código.
     */
    public function byColmena($code)
    {
        $colmenas = Colmena::orderBy('code', 'asc')->get();
        $selectedColmena = $code;
        $visitasInit = $this->getVisitasFromDb();
        return view('apicola::Admin.Colmenas.inspeccion', compact('selectedColmena', 'colmenas', 'visitasInit'));
    }

    /**
     * Cargar todas las inspecciones desde la tabla física registro_inspeccion y sus enfermedades.
     * El Administrador puede ver todo lo registrado en el sistema.
     */
    private function getVisitasFromDb()
    {
        try {
            $visitasDb = DB::table('registro_inspeccion')
                ->leftJoin('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
                ->select('registro_inspeccion.*', 'colmenas.codigo as colmena_codigo')
                ->orderBy('registro_inspeccion.fecha', 'desc')
                ->get();

            $enfermedades = DB::table('enfermedades_registro')->get()->groupBy('id_registro');

            // Mapa de usuarios para mostrar nombres reales de aprendices y administradores
            $usersMap = DB::table('users')
                ->leftJoin('people', 'users.person_id', '=', 'people.id')
                ->select('users.id', 'users.nickname', 'people.first_name', 'people.first_last_name')
                ->get()
                ->keyBy('id');

            return $visitasDb->map(function ($r) use ($enfermedades, $usersMap) {
                $enfs = $enfermedades->has($r->id_registro) 
                    ? $enfermedades[$r->id_registro]->pluck('nombre_enfermedad')->toArray() 
                    : [];

                $responsable = 'Sergio Barrera';
                if (!empty($r->observaciones) && preg_match('/Responsable:\s*([^|]+)/', $r->observaciones, $m)) {
                    $responsable = trim($m[1]);
                } elseif (!empty($r->user_id) && isset($usersMap[$r->user_id])) {
                    $u = $usersMap[$r->user_id];
                    $responsable = trim(($u->first_name ?? '') . ' ' . ($u->first_last_name ?? '')) ?: $u->nickname;
                }

                return [
                    'id' => (int)$r->id_registro,
                    'regNumber' => $r->numero_registro ?: 'REG-' . str_pad($r->id_registro, 4, '0', STR_PAD_LEFT),
                    'colmena' => $r->colmena_codigo ?: 'Col-01',
                    'fecha' => \Carbon\Carbon::parse($r->fecha)->format('d/m/Y'),
                    'estadoGeneral' => $r->estado_colmena ?: 'Bueno',
                    'presenciaMiel' => $r->presencia_miel ? 'Bueno' : 'Regular',
                    'mielKg' => (float)$r->miel_cosechada,
                    'reinaVista' => (bool)$r->reina_vista,
                    'reinaMarcada' => (bool)$r->reina_marcada,
                    'colorMarca' => $r->color_reina ?: 'Sin marcar',
                    'responsable' => $responsable,
                    'enfermedades' => $enfs,
                    'labores' => [],
                    'alimenSuministrada' => $r->alimentacion_suministrada ?: '',
                    'notas' => $r->observaciones ?: '',
                    'estado' => !empty($r->estado) ? $r->estado : (!empty($r->deleted_at) ? 'Inactiva' : 'Activa'),
                ];
            });
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Almacenar una nueva inspección / visita en registro_inspeccion y enfermedades_registro.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|max:50',
            'colmena_code' => 'required|string|max:50',
            'date' => 'required|date',
            'responsable' => 'required|string|max:150',
            'general_state' => 'required|in:Bueno,Regular,Malo',
            'honey_presence' => 'required|in:Bueno,Regular,Malo',
            'harvested_honey_kg' => 'nullable|numeric|min:0',
            'queen_seen' => 'nullable|boolean',
            'queen_marked' => 'nullable|boolean',
            'queen_color' => 'nullable|string|max:50',
            'diseases' => 'nullable|array',
            'tasks' => 'nullable|array',
            'feed_supplied' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();

        // 1. Obtener id_colmena de la tabla colmenas
        $colmenaDb = DB::table('colmenas')->where('codigo', $request->input('colmena_code'))->first();
        if (!$colmenaDb) {
            $colmenaDb = DB::table('colmenas')->first();
        }
        $idColmena = $colmenaDb ? $colmenaDb->id_colmenas : 1;

        // Formatear notas con el responsable si no hay columna responsable
        $notas = $request->input('notes');
        $responsableText = $request->input('responsable');
        $observacionesFinal = $responsableText ? "Responsable: {$responsableText}" . ($notas ? " | {$notas}" : "") : $notas;

        // 2. Insertar en registro_inspeccion vinculando el usuario administrador
        $idRegistro = DB::table('registro_inspeccion')->insertGetId([
            'user_id' => $user?->id,
            'id_colmena' => $idColmena,
            'numero_registro' => $request->input('registration_number'),
            'fecha' => $request->input('date'),
            'miel_cosechada' => $request->input('harvested_honey_kg') ?? 0,
            'estado_colmena' => $request->input('general_state') ?? 'Bueno',
            'presencia_miel' => ($request->input('honey_presence') === 'Bueno' || $request->input('honey_presence') == 1) ? 1 : 0,
            'reina_vista' => $request->boolean('queen_seen') ? 1 : 0,
            'reina_marcada' => $request->boolean('queen_marked') ? 1 : 0,
            'color_reina' => $request->input('queen_color') ?? 'Sin marcar',
            'alimentacion_suministrada' => $request->input('feed_supplied'),
            'observaciones' => $observacionesFinal,
            'estado' => 'Activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Insertar enfermedades detectadas en enfermedades_registro
        if ($request->has('diseases') && is_array($request->input('diseases'))) {
            foreach ($request->input('diseases') as $enf) {
                if (!empty($enf)) {
                    DB::table('enfermedades_registro')->insert([
                        'id_registro' => $idRegistro,
                        'nombre_enfermedad' => $enf,
                        'estado' => 'Detectada',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Visita registrada con éxito en registro_inspeccion.',
                'id_registro' => $idRegistro
            ], 201);
        }

        return redirect()->back()->with('success', 'Visita registrada con éxito.');
    }

    /**
     * Alterna el estado de una inspección / visita (Activa / Inactiva).
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $registro = DB::table('registro_inspeccion')->where('id_registro', $id)->first();
            if (!$registro) {
                return response()->json(['success' => false, 'message' => 'Inspección no encontrada.'], 404);
            }

            $currentEstado = $registro->estado ?? ($registro->deleted_at ? 'Inactiva' : 'Activa');
            $nuevoEstado = ($currentEstado === 'Inactiva') ? 'Activa' : 'Inactiva';
            $deletedAt = ($nuevoEstado === 'Inactiva') ? now() : null;

            DB::table('registro_inspeccion')->where('id_registro', $id)->update([
                'estado' => $nuevoEstado,
                'deleted_at' => $deletedAt,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'nuevo_estado' => $nuevoEstado,
                'message' => "Visita \"{$registro->numero_registro}\" marcada como {$nuevoEstado}.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado de la inspección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactiva una inspección / visita sin eliminarla de la base de datos.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $registro = DB::table('registro_inspeccion')->where('id_registro', $id)->first();
            if (!$registro) {
                return response()->json(['success' => false, 'message' => 'Inspección no encontrada.'], 404);
            }

            DB::table('registro_inspeccion')->where('id_registro', $id)->update([
                'estado' => 'Inactiva',
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

            $numero = $registro->numero_registro ?: 'REG-' . $id;

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'nuevo_estado' => 'Inactiva',
                    'message' => "Visita \"{$numero}\" desactivada con éxito.",
                ]);
            }

            return redirect()->back()->with('success', "Visita \"{$numero}\" desactivada con éxito.");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar la inspección: ' . $e->getMessage()
            ], 500);
        }
    }
}
