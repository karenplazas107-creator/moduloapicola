<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Apicola\Entities\EspecieMelifera;
use Modules\Apicola\Entities\CalendarioFloral;

class CalendarioController extends Controller
{
    /**
     * Muestra la vista principal del Calendario Floral con datos reales de la BD MySQL.
     */
    public function index(Request $request)
    {
        $calendarios = CalendarioFloral::with('especie')
            ->orderBy('anio', 'desc')
            ->orderBy('id_calendario', 'desc')
            ->get();

        $plantasFormatted = $calendarios->map(function ($item) {
            $meses = $item->meses;
            if (is_string($meses)) {
                $decoded = json_decode($meses, true);
                $meses = is_array($decoded) ? $decoded : [];
            } elseif (!is_array($meses)) {
                $meses = [];
            }

            // Asegurar que las claves numéricas de meses estén presentes
            $mesesNormalizados = [];
            for ($i = 0; $i < 12; $i++) {
                $mesesNormalizados[$i] = $meses[$i] ?? $meses[(string)$i] ?? 'ninguna';
            }

            return [
                'id' => $item->id_calendario,
                'idEspecie' => $item->id_especie,
                'nombreComun' => $item->especie->nombre_comun ?? 'Sin nombre',
                'nombreCientifico' => $item->especie->nombre_cientifico ?? '',
                'aporte' => $item->especie->aporte ?? 'Néctar y polen',
                'anio' => (int) ($item->anio ?? 2026),
                'estado' => $item->especie->estado ?? 'Activa',
                'meses' => $mesesNormalizados,
            ];
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $plantasFormatted,
            ]);
        }

        return view('apicola::Admin.Calendario.index', compact('plantasFormatted'));
    }

    /**
     * Guarda una nueva planta y su fenología en la base de datos MySQL (especie_malifera y calendario_floral).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_comun' => 'required|string|max:100',
            'nombre_cientifico' => 'nullable|string|max:150',
            'aporte' => 'nullable|string|max:100',
            'anio' => 'required|integer|min:2020|max:2050',
            'estado' => 'required|string|in:Activa,Inactiva',
            'meses' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear especie melífera en especie_malifera
            $especie = EspecieMelifera::create([
                'nombre_comun' => trim($validated['nombre_comun']),
                'nombre_cientifico' => isset($validated['nombre_cientifico']) ? trim($validated['nombre_cientifico']) : null,
                'descripcion' => 'Registrada en calendario floral ' . $validated['anio'],
                'aporte' => $validated['aporte'] ?? 'Néctar y polen',
                'estado' => $validated['estado'],
            ]);

            // Normalizar meses
            $mesesData = [];
            $mesesInput = $request->input('meses', []);
            for ($i = 0; $i < 12; $i++) {
                $mesesData[$i] = $mesesInput[$i] ?? $mesesInput[(string)$i] ?? 'ninguna';
            }

            // 2. Crear registro en calendario_floral
            $calendario = CalendarioFloral::create([
                'id_especie' => $especie->id_especie,
                'anio' => (int) $validated['anio'],
                'meses' => $mesesData,
                'etapa' => 'Floración',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Planta "' . $especie->nombre_comun . '" guardada con éxito en la base de datos.',
                'data' => [
                    'id' => $calendario->id_calendario,
                    'idEspecie' => $especie->id_especie,
                    'nombreComun' => $especie->nombre_comun,
                    'nombreCientifico' => $especie->nombre_cientifico ?? '',
                    'aporte' => $especie->aporte,
                    'anio' => (int) $calendario->anio,
                    'estado' => $especie->estado,
                    'meses' => $mesesData,
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar en la base de datos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza una planta existente y su fenología en la base de datos MySQL.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_comun' => 'required|string|max:100',
            'nombre_cientifico' => 'nullable|string|max:150',
            'aporte' => 'nullable|string|max:100',
            'anio' => 'required|integer|min:2020|max:2050',
            'estado' => 'required|string|in:Activa,Inactiva',
            'meses' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $calendario = CalendarioFloral::with('especie')->findOrFail($id);

            // Normalizar meses
            $mesesData = [];
            $mesesInput = $request->input('meses', []);
            for ($i = 0; $i < 12; $i++) {
                $mesesData[$i] = $mesesInput[$i] ?? $mesesInput[(string)$i] ?? 'ninguna';
            }

            // Actualizar calendario_floral
            $calendario->update([
                'anio' => (int) $validated['anio'],
                'meses' => $mesesData,
            ]);

            // Actualizar especie asociada si existe
            if ($calendario->especie) {
                $calendario->especie->update([
                    'nombre_comun' => trim($validated['nombre_comun']),
                    'nombre_cientifico' => isset($validated['nombre_cientifico']) ? trim($validated['nombre_cientifico']) : null,
                    'aporte' => $validated['aporte'] ?? 'Néctar y polen',
                    'estado' => $validated['estado'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Planta "' . ($calendario->especie->nombre_comun ?? '') . '" actualizada exitosamente.',
                'data' => [
                    'id' => $calendario->id_calendario,
                    'idEspecie' => $calendario->id_especie,
                    'nombreComun' => $calendario->especie->nombre_comun ?? '',
                    'nombreCientifico' => $calendario->especie->nombre_cientifico ?? '',
                    'aporte' => $calendario->especie->aporte ?? '',
                    'anio' => (int) $calendario->anio,
                    'estado' => $calendario->especie->estado ?? 'Activa',
                    'meses' => $mesesData,
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Alterna el estado (Activa / Inactiva) de la planta en la base de datos.
     */
    public function toggleStatus($id)
    {
        try {
            $calendario = CalendarioFloral::with('especie')->findOrFail($id);
            if (!$calendario->especie) {
                return response()->json(['success' => false, 'message' => 'Especie no encontrada'], 404);
            }

            $current = $calendario->especie->estado;
            $newStatus = ($current === 'Activa') ? 'Inactiva' : 'Activa';
            $calendario->especie->update(['estado' => $newStatus]);

            return response()->json([
                'success' => true,
                'nuevoEstado' => $newStatus,
                'message' => 'Planta "' . $calendario->especie->nombre_comun . '" marcada como ' . strtolower($newStatus) . '.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Desactiva la planta melífera del calendario floral (cambia su estado a Inactiva).
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $calendario = CalendarioFloral::with('especie')->findOrFail($id);

            if ($calendario->especie) {
                $calendario->especie->update(['estado' => 'Inactiva']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'nuevoEstado' => 'Inactiva',
                'message' => 'Planta "' . ($calendario->especie->nombre_comun ?? '') . '" desactivada con éxito.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar planta: ' . $e->getMessage(),
            ], 500);
        }
    }
}
