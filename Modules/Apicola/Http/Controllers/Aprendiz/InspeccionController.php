<?php

namespace Modules\Apicola\Http\Controllers\Aprendiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Apicola\Entities\Inspeccion;
use Modules\Apicola\Entities\Colmena;

class InspeccionController extends Controller
{
    /**
     * Muestra la vista de Registro e Inspección de colmenas para el Aprendiz.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $person = $user?->person;
        $aprendizNombre = $person ? trim($person->first_name . ' ' . $person->first_last_name) : trim($user?->name ?? $user?->nickname ?? 'Aprendiz');

        $colmenas = Colmena::orderBy('code', 'asc')->get();
        $firstCode = $colmenas->first() ? ($colmenas->first()->code ?? $colmenas->first()->codigo ?? '') : '';
        $selectedColmena = $request->get('colmena', $firstCode);
        $visitasInit = $this->getVisitasFromDb();

        $enfermedades = DB::table('apicola_enfermedades')
            ->where('estado', 'Activa')
            ->orderBy('nombre', 'asc')
            ->get();
        $enfermedadesDisponibles = $enfermedades->pluck('nombre')->toArray();

        return view('apicola::Aprendiz.Colmenas.inspeccion', compact('selectedColmena', 'colmenas', 'visitasInit', 'enfermedadesDisponibles', 'enfermedades', 'aprendizNombre'));
    }

    /**
     * Inspecciones de una colmena específica por código.
     */
    public function byColmena($code)
    {
        $user = auth()->user();
        $person = $user?->person;
        $aprendizNombre = $person ? trim($person->first_name . ' ' . $person->first_last_name) : trim($user?->name ?? $user?->nickname ?? 'Aprendiz');

        $selectedColmena = $code;
        $colmenas = Colmena::orderBy('code', 'asc')->get();
        $visitasInit = $this->getVisitasFromDb();

        $enfermedades = DB::table('apicola_enfermedades')
            ->where('estado', 'Activa')
            ->orderBy('nombre', 'asc')
            ->get();
        $enfermedadesDisponibles = $enfermedades->pluck('nombre')->toArray();

        return view('apicola::Aprendiz.Colmenas.inspeccion', compact('selectedColmena', 'colmenas', 'visitasInit', 'enfermedadesDisponibles', 'enfermedades', 'aprendizNombre'));
    }

    /**
     * Cargar inspecciones desde la tabla física registro_inspeccion y sus enfermedades.
     * Solo retorna las inspecciones registradas por el aprendiz autenticado.
     */
    private function getVisitasFromDb()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return collect();
            }

            $userId = $user->id;
            $apprenticeId = $user->person?->apprentices?->first()?->id;
            $person = $user->person;
            $fullName = $person ? trim($person->first_name . ' ' . $person->first_last_name) : trim($user->name ?? $user->nickname);
            $nickname = trim($user->nickname ?? '');

            $query = DB::table('registro_inspeccion')
                ->leftJoin('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
                ->select('registro_inspeccion.*', 'colmenas.codigo as colmena_codigo');

            // Filtro estricto: el aprendiz solo puede ver lo que él mismo registró
            $query->where(function ($q) use ($userId, $apprenticeId, $fullName, $nickname) {
                $hasCondition = false;

                if ($userId) {
                    $q->where('registro_inspeccion.user_id', $userId);
                    $hasCondition = true;
                }

                if ($apprenticeId) {
                    if ($hasCondition) {
                        $q->orWhere('registro_inspeccion.id_aprendiz', $apprenticeId);
                    } else {
                        $q->where('registro_inspeccion.id_aprendiz', $apprenticeId);
                        $hasCondition = true;
                    }
                }

                if ($fullName) {
                    $q->orWhere('registro_inspeccion.observaciones', 'like', "%Responsable: {$fullName}%");
                }
                if ($nickname) {
                    $q->orWhere('registro_inspeccion.observaciones', 'like', "%Responsable: {$nickname}%");
                }
            });

            $visitasDb = $query->orderBy('registro_inspeccion.fecha', 'desc')->get();

            $enfermedades = DB::table('enfermedades_registro')->get()->groupBy('id_registro');

            return $visitasDb->map(function ($r) use ($enfermedades, $fullName, $nickname) {
                $enfs = $enfermedades->has($r->id_registro) 
                    ? $enfermedades[$r->id_registro]->pluck('nombre_enfermedad')->toArray() 
                    : [];

                $responsable = $fullName ?: $nickname;
                if (!empty($r->observaciones) && preg_match('/Responsable:\s*([^|]+)/', $r->observaciones, $m)) {
                    $responsable = trim($m[1]);
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
     * Almacenar una nueva inspección / visita realizada por el Aprendiz.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|max:50',
            'colmena_code' => 'required|string|max:50',
            'date' => 'required|date',
            'responsable' => 'nullable|string|max:150',
            'general_state' => 'required|in:Bueno,Regular,Malo',
            'honey_presence' => 'required|in:Bueno,Regular,Malo',
            'harvested_honey_kg' => 'nullable|numeric|min:0',
            'queen_seen' => 'nullable|boolean',
            'queen_marked' => 'nullable|boolean',
            'queen_color' => 'nullable|string|max:50',
            'diseases' => 'nullable|array',
            'tasks' => 'nullable|array',
            'feed_supplied' => ['nullable', 'string', 'max:150', 'regex:/^[^\d]*$/'],
            'notes' => 'nullable|string',
        ], [
            'feed_supplied.regex' => 'No se permiten números en la alimentación suministrada. Solo se permiten letras.',
        ]);

        $user = auth()->user();
        $apprenticeId = $user?->person?->apprentices?->first()?->id;

        // 1. Obtener id_colmena de la tabla colmenas
        $colmenaDb = DB::table('colmenas')->where('codigo', $request->input('colmena_code'))->first();
        if (!$colmenaDb) {
            $colmenaDb = DB::table('colmenas')->first();
        }
        $idColmena = $colmenaDb ? $colmenaDb->id_colmenas : 1;

        // Formatear notas con el responsable identificado
        $notas = $request->input('notes');
        $responsableText = $request->input('responsable');
        if (empty($responsableText) && $user) {
            $responsableText = $user->person ? trim($user->person->first_name . ' ' . $user->person->first_last_name) : ($user->nickname ?? 'Aprendiz');
        }
        $observacionesFinal = $responsableText ? "Responsable: {$responsableText}" . ($notas ? " | {$notas}" : "") : $notas;

        // 2. Insertar en registro_inspeccion vinculado estrictamente al usuario y aprendiz autenticado
        $idRegistro = DB::table('registro_inspeccion')->insertGetId([
            'user_id' => $user?->id,
            'id_aprendiz' => $apprenticeId,
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
     * El aprendiz no tiene permisos para cambiar el estado o desactivar inspecciones.
     */
    public function toggleStatus(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'El rol de aprendiz no tiene permisos para desactivar o modificar el estado de las inspecciones.',
        ], 403);
    }

    /**
     * El aprendiz no tiene permisos para desactivar inspecciones.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'El rol de aprendiz no tiene permisos para desactivar inspecciones.',
            ], 403);
        }

        return redirect()->back()->with('error', 'El rol de aprendiz no tiene permisos para desactivar inspecciones.');
    }

    /**
     * Obtener listado de enfermedades registradas en formato JSON.
     */
    public function getEnfermedades()
    {
        try {
            $enfermedades = DB::table('apicola_enfermedades')
                ->where('estado', 'Activa')
                ->orderBy('nombre', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $enfermedades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener enfermedades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar una nueva enfermedad en el catálogo oficial apícola.
     */
    public function storeEnfermedad(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:150',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-\.]+$/'
            ]
        ], [
            'nombre.required' => 'El nombre de la enfermedad es obligatorio.',
            'nombre.regex' => 'Enfermedad inválida: no se permiten números, solo letras.',
            'nombre.min' => 'El nombre debe tener al menos 2 letras.',
        ]);

        $nombreLimpio = trim($request->input('nombre'));
        $nombreFormateado = mb_convert_case($nombreLimpio, MB_CASE_TITLE, 'UTF-8');

        $existe = DB::table('apicola_enfermedades')
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreLimpio, 'UTF-8')])
            ->first();

        if ($existe) {
            if ($existe->estado !== 'Activa') {
                DB::table('apicola_enfermedades')->where('id', $existe->id)->update([
                    'estado' => 'Activa',
                    'updated_at' => now()
                ]);
                $all = DB::table('apicola_enfermedades')->where('estado', 'Activa')->orderBy('nombre', 'asc')->get();
                return response()->json([
                    'success' => true,
                    'message' => "La enfermedad \"{$existe->nombre}\" fue reactivada exitosamente.",
                    'enfermedad' => $existe,
                    'data' => $all
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => "La enfermedad \"{$existe->nombre}\" ya se encuentra registrada."
            ], 422);
        }

        $id = DB::table('apicola_enfermedades')->insertGetId([
            'nombre' => $nombreFormateado,
            'estado' => 'Activa',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $nueva = DB::table('apicola_enfermedades')->where('id', $id)->first();
        $all = DB::table('apicola_enfermedades')->where('estado', 'Activa')->orderBy('nombre', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => "Enfermedad \"{$nombreFormateado}\" registrada con éxito.",
            'enfermedad' => $nueva,
            'data' => $all
        ], 201);
    }

    /**
     * Eliminar una enfermedad del catálogo.
     */
    public function destroyEnfermedad($id)
    {
        try {
            $enf = DB::table('apicola_enfermedades')->where('id', $id)->first();
            if (!$enf) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enfermedad no encontrada.'
                ], 404);
            }

            DB::table('apicola_enfermedades')->where('id', $id)->delete();
            $all = DB::table('apicola_enfermedades')->where('estado', 'Activa')->orderBy('nombre', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => "Enfermedad \"{$enf->nombre}\" eliminada correctamente.",
                'data' => $all
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la enfermedad: ' . $e->getMessage()
            ], 500);
        }
    }
}
