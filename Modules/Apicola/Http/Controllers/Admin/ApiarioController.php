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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $apiariosFormatted,
                'metrics' => $metrics,
            ]);
        }

        return view('apicola::Admin.Apiario.index', compact('apiarios', 'apiariosFormatted', 'metrics'));
    }

    /**
     * Guarda un nuevo apiario en la base de datos MySQL (tabla apiario).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_apiario' => 'required|string|max:100',
            'responsable' => 'nullable|string|max:150',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'estado' => 'nullable|string|in:Activo,Inactivo',
            'colmenas' => 'nullable|integer|min:0',
        ]);

        $currentUserName = auth()->check() && auth()->user()->person
            ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name)
            : (auth()->check() ? auth()->user()->nickname : 'Sin asignar');

        $apiario = Apiario::create([
            'nombre_apiario' => trim($validated['nombre_apiario']),
            'responsable' => !empty($validated['responsable']) ? trim($validated['responsable']) : $currentUserName,
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
    }

    /**
     * Actualiza un apiario existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $apiario = Apiario::findOrFail($id);
        $oldNombre = $apiario->nombre_apiario;

        $validated = $request->validate([
            'nombre_apiario' => 'required|string|max:100',
            'responsable' => 'nullable|string|max:150',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'estado' => 'nullable|string|in:Activo,Inactivo',
            'colmenas' => 'nullable|integer|min:0',
        ]);

        $newNombre = trim($validated['nombre_apiario']);
        $currentUserName = auth()->check() && auth()->user()->person
            ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name)
            : (auth()->check() ? auth()->user()->nickname : 'Sin asignar');

        $apiario->update([
            'nombre_apiario' => $newNombre,
            'responsable' => !empty($validated['responsable']) ? trim($validated['responsable']) : ($apiario->responsable ?? $currentUserName),
            'latitud' => $validated['latitud'] ?? $apiario->latitud,
            'longitud' => $validated['longitud'] ?? $apiario->longitud,
            'estado' => $validated['estado'] ?? $apiario->estado,
        ]);

        // Si el nombre del apiario cambió, actualizar en cascada el apiario_name en las colmenas
        if ($oldNombre !== $newNombre) {
            Colmena::where('apiario_id', $apiario->id_apiarios)
                ->orWhere('apiario_name', $oldNombre)
                ->update([
                    'apiario_name' => $newNombre,
                    'apiario_id' => $apiario->id_apiarios,
                ]);
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
     * Desactiva un apiario en la base de datos MySQL (cambia su estado a Inactivo).
     */
    public function destroy(Request $request, $id)
    {
        $apiario = Apiario::findOrFail($id);
        $nombre = $apiario->nombre_apiario;
        $apiario->estado = 'Inactivo';
        $apiario->save();

        $metrics = [
            'total' => Apiario::count(),
            'activos' => Apiario::activos()->count(),
            'inactivos' => Apiario::inactivos()->count(),
            'colmenas' => Colmena::count(),
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'nuevo_estado' => 'Inactivo',
                'message' => "Apiario \"{$nombre}\" desactivado con éxito.",
                'metrics' => $metrics,
            ]);
        }

        return redirect()->back()->with('success', "Apiario \"{$nombre}\" desactivado con éxito.");
    }
}
