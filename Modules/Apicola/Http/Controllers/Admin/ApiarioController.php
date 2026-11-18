<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use Modules\Apicola\Entities\MotivoBaja;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Apicola\Entities\Apiario;
use Modules\Apicola\Entities\Colmena;
use Modules\Apicola\Entities\Responsable;

class ApiarioController extends Controller
{
    /**
     * Muestra la vista principal del módulo de Apiarios cargando datos reales de la BD.
     */
    public function index(Request $request)
    {
        $apiarios = Apiario::withCount('colmenas')->orderBy('id_apiarios', 'asc')->get();

        $apiariosFormatted = $apiarios->map(function ($item) {
            return [
                'id' => $item->id_apiarios,
                'nombre' => $item->nombre_apiario,
                'responsable' => $item->responsable ?? (auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : 'Sin asignar'),
                'lat' => (float) ($item->latitud ?? 2.6857),
                'lng' => (float) ($item->longitud ?? -75.3241),
                'colmenas' => (int) $item->colmenas_count,
                'colmenasLista' => $this->colmenasLista($item->id_apiarios),
                'estado' => $item->estado ?? 'Activo',
                'isSena' => (bool) (stripos($item->nombre_apiario, 'sena') !== false || stripos($item->nombre_apiario, 'angostura') !== false),
                'notas' => 'Apiario registrado en la base de datos MySQL (tabla apiario).'
            ];
        });

        $metrics = [
            'total' => Apiario::count(),
            'activos' => Apiario::activos()->count(),
            'inactivos' => Apiario::inactivos()->count(),
            'colmenas' => Colmena::count(),
        ];

        // Lista compartida de responsables (la misma en apiarios, colmenas e inspecciones)
        $responsablesSugeridos = Responsable::todos();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $apiariosFormatted,
                'metrics' => $metrics,
                'responsables' => $responsablesSugeridos,
            ]);
        }

        return view('apicola::Admin.Apiario.index', compact('apiarios', 'apiariosFormatted', 'metrics', 'responsablesSugeridos'));
    }

    /**
     * Guarda un nuevo apiario en la base de datos MySQL (tabla apiario).
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre_apiario' => ['required', 'string', 'max:100', 'regex:/^[^0-9]+$/u'],
                'responsable' => ['required', 'string', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$/u'],
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
                'estado' => 'required|string|in:Activo,Inactivo',
                'colmenas' => 'required|integer|min:0',
            ], [
                'nombre_apiario.regex' => 'El nombre del apiario es inválido: no puede contener números, solo texto.',
                'responsable.regex' => 'El responsable es inválido: no puede contener números, solo nombres reales de personas.',
                'latitud.numeric' => 'La latitud es inválida: no puede contener letras, solo números reales.',
                'latitud.between' => 'La latitud debe estar en un rango geográfico real entre -90 y 90.',
                'longitud.numeric' => 'La longitud es inválida: no puede contener letras, solo números reales.',
                'longitud.between' => 'La longitud debe estar en un rango geográfico real entre -180 y 180.',
            ]);

            $currentUserName = auth()->check() && auth()->user()->person
                ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name)
                : (auth()->check() ? auth()->user()->nickname : 'Sin asignar');

            $nombre = trim($validated['nombre_apiario']);
            $responsable = !empty($validated['responsable']) ? Responsable::registrar($validated['responsable']) : $currentUserName;

            $apiario = Apiario::create([
                'nombre_apiario' => $nombre,
                'responsable' => $responsable,
                'latitud' => $validated['latitud'] ?? 2.6857,
                'longitud' => $validated['longitud'] ?? -75.3241,
                'estado' => $validated['estado'] ?? 'Activo',
            ]);

            $formatted = [
                'id' => $apiario->id_apiarios,
                'nombre' => $apiario->nombre_apiario,
                'responsable' => $apiario->responsable ?? $currentUserName,
                'lat' => (float) ($apiario->latitud ?? 2.6857),
                'lng' => (float) ($apiario->longitud ?? -75.3241),
                'colmenas' => $apiario->colmenas()->count(),
                'colmenasLista' => $this->colmenasLista($apiario->id_apiarios),
                'estado' => $apiario->estado,
                'isSena' => (bool) (stripos($apiario->nombre_apiario, 'sena') !== false || stripos($apiario->nombre_apiario, 'angostura') !== false),
                'notas' => 'Apiario registrado en la base de datos MySQL (tabla apiario).'
            ];

            $metrics = [
                'total' => Apiario::count(),
                'activos' => Apiario::activos()->count(),
                'inactivos' => Apiario::inactivos()->count(),
                'colmenas' => Colmena::count(),
            ];

            return response()->json([
                'success' => true,
                'message' => "¡Apiario \"{$apiario->nombre_apiario}\" guardado exitosamente en la base de datos!",
                'data' => $formatted,
                'metrics' => $metrics,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(' ', array_map(fn($e) => implode(', ', $e), $ve->errors())),
                'errors' => $ve->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el apiario: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un apiario existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        try {
            $realId = $id ?: $request->input('id');
            $apiario = Apiario::findOrFail($realId);
            $oldNombre = $apiario->nombre_apiario;

            $validated = $request->validate([
                'nombre_apiario' => ['required', 'string', 'max:100', 'regex:/^[^0-9]+$/u'],
                'responsable' => ['required', 'string', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$/u'],
                'latitud' => 'nullable|numeric|between:-90,90',
                'longitud' => 'nullable|numeric|between:-180,180',
                'estado' => 'nullable|string|in:Activo,Inactivo',
                'colmenas' => 'nullable|integer|min:0',
            ], [
                'nombre_apiario.regex' => 'El nombre del apiario es inválido: no puede contener números, solo texto.',
                'responsable.regex' => 'El responsable es inválido: no puede contener números, solo nombres reales de personas.',
                'latitud.numeric' => 'La latitud es inválida: no puede contener letras, solo números reales.',
                'latitud.between' => 'La latitud debe estar en un rango geográfico real entre -90 y 90.',
                'longitud.numeric' => 'La longitud es inválida: no puede contener letras, solo números reales.',
                'longitud.between' => 'La longitud debe estar en un rango geográfico real entre -180 y 180.',
            ]);

            // Si el apiario pasa de Activo a Inactivo desde el formulario, el motivo es obligatorio
            $motivo = (($validated['estado'] ?? $apiario->estado) === 'Inactivo' && $apiario->estado !== 'Inactivo')
                ? MotivoBaja::exigir($request)
                : null;

            $newNombre = trim($validated['nombre_apiario']);
            $currentUserName = auth()->check() && auth()->user()->person
                ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name)
                : (auth()->check() ? auth()->user()->nickname : 'Sin asignar');

            $responsable = !empty($validated['responsable']) ? Responsable::registrar($validated['responsable']) : ($apiario->responsable ?? $currentUserName);

            $apiario->update([
                'nombre_apiario' => $newNombre,
                'responsable' => $responsable,
                'latitud' => $validated['latitud'] ?? $apiario->latitud,
                'longitud' => $validated['longitud'] ?? $apiario->longitud,
                'estado' => $validated['estado'] ?? $apiario->estado,
            ]);

            if ($motivo) {
                MotivoBaja::registrar('apiario', $apiario->id_apiarios, $apiario->nombre_apiario, 'desactivar', $motivo);
            }

            // Si el nombre del apiario cambió, actualizar en cascada en las colmenas
            if ($oldNombre !== $newNombre) {
                Colmena::where('apiario_id', $apiario->id_apiarios)
                    ->orWhere('apiario_name', $oldNombre)
                    ->update([
                        'apiario_name' => $newNombre,
                        'apiario_id' => $apiario->id_apiarios,
                    ]);

                if (\Illuminate\Support\Facades\Schema::hasTable('colmenas')) {
                    \Illuminate\Support\Facades\DB::table('colmenas')
                        ->where('id_apiario', $apiario->id_apiarios)
                        ->update(['id_apiario' => $apiario->id_apiarios]);
                }
            }

            $formatted = [
                'id' => $apiario->id_apiarios,
                'nombre' => $apiario->nombre_apiario,
                'responsable' => $apiario->responsable ?? $currentUserName,
                'lat' => (float) ($apiario->latitud ?? 2.6857),
                'lng' => (float) ($apiario->longitud ?? -75.3241),
                'colmenas' => $apiario->colmenas()->count(),
                'colmenasLista' => $this->colmenasLista($apiario->id_apiarios),
                'estado' => $apiario->estado,
                'isSena' => (bool) (stripos($apiario->nombre_apiario, 'sena') !== false || stripos($apiario->nombre_apiario, 'angostura') !== false),
                'notas' => 'Apiario actualizado en la base de datos MySQL (tabla apiario).'
            ];

            return response()->json([
                'success' => true,
                'message' => "¡Apiario \"{$apiario->nombre_apiario}\" actualizado correctamente!",
                'data' => $formatted,
                'metrics' => [
                    'total' => Apiario::count(),
                    'activos' => Apiario::activos()->count(),
                    'inactivos' => Apiario::inactivos()->count(),
                    'colmenas' => Colmena::count(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(' ', array_map(fn($e) => implode(', ', $e), $ve->errors())),
                'errors' => $ve->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el apiario: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Colmenas asociadas a un apiario (para el checklist de reubicación).
     */
    private function colmenasLista($apiarioId): array
    {
        return Colmena::where('apiario_id', $apiarioId)
            ->orderBy('code')
            ->get(['id', 'code', 'status'])
            ->map(fn ($c) => ['id' => $c->id, 'code' => $c->code, 'estado' => $c->status])
            ->values()
            ->all();
    }

    /**
     * Formato común de un apiario para las respuestas JSON de reubicación.
     */
    private function formatear(Apiario $a): array
    {
        return [
            'id' => $a->id_apiarios,
            'nombre' => $a->nombre_apiario,
            'responsable' => $a->responsable ?? 'Sin asignar',
            'lat' => (float) ($a->latitud ?? 2.6857),
            'lng' => (float) ($a->longitud ?? -75.3241),
            'colmenas' => $a->colmenas()->count(),
            'colmenasLista' => $this->colmenasLista($a->id_apiarios),
            'estado' => $a->estado,
            'isSena' => (bool) (stripos($a->nombre_apiario, 'sena') !== false || stripos($a->nombre_apiario, 'angostura') !== false),
            'notas' => 'Apiario registrado en la base de datos MySQL (tabla apiario).',
        ];
    }

    /**
     * Mueve las colmenas seleccionadas de este apiario a otro apiario.
     */
    public function moverColmenas(Request $request, $id)
    {
        $origen = Apiario::findOrFail($id);

        $validated = $request->validate([
            'colmena_ids' => ['required', 'array', 'min:1'],
            'colmena_ids.*' => ['integer'],
            'destino_id' => ['required', 'integer'],
        ], [
            'colmena_ids.required' => 'Selecciona al menos una colmena para mover.',
            'colmena_ids.min' => 'Selecciona al menos una colmena para mover.',
            'destino_id.required' => 'Selecciona el apiario de destino.',
        ]);

        $destino = Apiario::find($validated['destino_id']);
        if (!$destino || $destino->id_apiarios === $origen->id_apiarios) {
            return response()->json([
                'success' => false,
                'message' => 'Selecciona un apiario de destino distinto al actual.',
            ], 422);
        }

        // Solo se mueven colmenas que realmente pertenecen a este apiario
        $colmenas = Colmena::where('apiario_id', $origen->id_apiarios)
            ->whereIn('id', $validated['colmena_ids'])
            ->get();

        if ($colmenas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Las colmenas seleccionadas no pertenecen a este apiario.',
            ], 422);
        }

        foreach ($colmenas as $colmena) {
            $colmena->update([
                'apiario_id' => $destino->id_apiarios,
                'apiario_name' => $destino->nombre_apiario,
            ]);

            // Mantener sincronizada la tabla física 'colmenas'
            try {
                \Illuminate\Support\Facades\DB::table('colmenas')
                    ->where('codigo', $colmena->code)
                    ->update(['id_apiario' => $destino->id_apiarios, 'updated_at' => now()]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("No se pudo sincronizar la colmena {$colmena->code}: " . $e->getMessage());
            }
        }

        $n = $colmenas->count();

        return response()->json([
            'success' => true,
            'message' => ($n === 1 ? '1 colmena movida' : "{$n} colmenas movidas") . " de \"{$origen->nombre_apiario}\" a \"{$destino->nombre_apiario}\".",
            'origen' => $this->formatear($origen->fresh()),
            'destino' => $this->formatear($destino->fresh()),
            'metrics' => [
                'total' => Apiario::count(),
                'activos' => Apiario::activos()->count(),
                'inactivos' => Apiario::inactivos()->count(),
                'colmenas' => Colmena::count(),
            ],
        ]);
    }

    /**
     * Alterna el estado de un apiario (Activo / Inactivo) directamente en MySQL.
     */
    public function toggleStatus(Request $request, $id)
    {
        $apiario = Apiario::findOrFail($id);
        // Para desactivar es obligatorio indicar el motivo
        $motivo = $apiario->estado === 'Activo' ? MotivoBaja::exigir($request) : null;
        $apiario->estado = ($apiario->estado === 'Activo') ? 'Inactivo' : 'Activo';
        $apiario->save();
        if ($motivo) {
            MotivoBaja::registrar('apiario', $apiario->id_apiarios, $apiario->nombre_apiario, 'desactivar', $motivo);
        }

        return response()->json([
            'success' => true,
            'nuevo_estado' => $apiario->estado,
            'message' => "Apiario \"{$apiario->nombre_apiario}\" marcado como {$apiario->estado}.",
            'metrics' => [
                'total' => Apiario::count(),
                'activos' => Apiario::activos()->count(),
                'inactivos' => Apiario::inactivos()->count(),
                'colmenas' => Colmena::count(),
            ],
        ]);
    }
}
