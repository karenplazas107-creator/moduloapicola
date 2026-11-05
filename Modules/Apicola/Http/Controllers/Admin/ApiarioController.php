<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Apicola\Entities\Apiario;
use Modules\Apicola\Entities\Colmena;

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

        // Solo SERGIO BARRERA y opción de añadir a otro
        $responsablesSugeridos = ['SERGIO BARRERA'];

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
            $responsable = !empty($validated['responsable']) ? trim($validated['responsable']) : $currentUserName;

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

            $newNombre = trim($validated['nombre_apiario']);
            $currentUserName = auth()->check() && auth()->user()->person
                ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name)
                : (auth()->check() ? auth()->user()->nickname : 'Sin asignar');

            $responsable = !empty($validated['responsable']) ? trim($validated['responsable']) : ($apiario->responsable ?? $currentUserName);

            $apiario->update([
                'nombre_apiario' => $newNombre,
                'responsable' => $responsable,
                'latitud' => $validated['latitud'] ?? $apiario->latitud,
                'longitud' => $validated['longitud'] ?? $apiario->longitud,
                'estado' => $validated['estado'] ?? $apiario->estado,
            ]);

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
     * Alterna el estado de un apiario (Activo / Inactivo) directamente en MySQL.
     */
    public function toggleStatus(Request $request, $id)
    {
        $apiario = Apiario::findOrFail($id);
        $apiario->estado = ($apiario->estado === 'Activo') ? 'Inactivo' : 'Activo';
        $apiario->save();

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

    /**
     * Elimina un apiario de la base de datos MySQL de forma permanente.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $apiario = Apiario::findOrFail($id);
            $nombre = $apiario->nombre_apiario;

            $colmenasCount = $apiario->colmenas()->count();
            if ($colmenasCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar el apiario \"{$nombre}\" porque tiene {$colmenasCount} colmena(s) asociadas. Primero debes reubicar o eliminar sus colmenas."
                ], 422);
            }

            $apiario->delete();

            $metrics = [
                'total' => Apiario::count(),
                'activos' => Apiario::activos()->count(),
                'inactivos' => Apiario::inactivos()->count(),
                'colmenas' => Colmena::count(),
            ];

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Apiario \"{$nombre}\" eliminado con éxito de la base de datos.",
                    'metrics' => $metrics,
                ]);
            }

            return redirect()->back()->with('success', "Apiario \"{$nombre}\" eliminado con éxito.");
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el apiario: ' . $th->getMessage()
            ], 500);
        }
    }
}
