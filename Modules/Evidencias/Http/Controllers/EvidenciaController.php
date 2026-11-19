<?php

namespace Modules\Evidencias\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Evidencias\Entities\Evidencia;

class EvidenciaController extends Controller
{
    /**
     * Dashboard principal: lista de evidencias + tarjetas resumen.
     */
    public function index()
    {
        $evidencias = Evidencia::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $total      = Evidencia::where('user_id', Auth::id())->count();
        $pendientes = Evidencia::where('user_id', Auth::id())->where('estado', 'pendiente')->count();
        $aprobadas  = Evidencia::where('user_id', Auth::id())->where('estado', 'aprobada')->count();
        $rechazadas = Evidencia::where('user_id', Auth::id())->where('estado', 'rechazada')->count();

        return view('evidencias::index', compact(
            'evidencias',
            'total',
            'pendientes',
            'aprobadas',
            'rechazadas'
        ));
    }

    /**
     * Formulario para registrar una nueva evidencia.
     */
    public function create()
    {
        $tipos   = Evidencia::TIPOS;
        $estados = Evidencia::ESTADOS;

        return view('evidencias::create', compact('tipos', 'estados'));
    }

    /**
     * Almacena la nueva evidencia en base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string|max:2000',
            'tipo'         => 'required|in:documento,imagen,video,otro',
            'archivo'      => 'nullable|file|max:10240', // 10 MB máximo
            'estado'       => 'required|in:pendiente,aprobada,rechazada',
            'observaciones'=> 'nullable|string|max:2000',
        ]);

        // Manejar carga del archivo si se envió
        $rutaArchivo = null;
        if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
            $rutaArchivo = $request->file('archivo')
                ->store('evidencias/' . Auth::id(), 'public');
        }

        Evidencia::create([
            'user_id'       => Auth::id(),
            'titulo'        => $validated['titulo'],
            'descripcion'   => $validated['descripcion'] ?? null,
            'tipo'          => $validated['tipo'],
            'archivo'       => $rutaArchivo,
            'estado'        => $validated['estado'],
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('evidencias.index')
            ->with('success', 'Evidencia registrada correctamente.');
    }

    /**
     * Muestra el detalle de una evidencia.
     */
    public function show(int $id)
    {
        $evidencia = Evidencia::where('user_id', Auth::id())->findOrFail($id);

        return view('evidencias::show', compact('evidencia'));
    }

    /**
     * Formulario para editar una evidencia existente.
     */
    public function edit(int $id)
    {
        $evidencia = Evidencia::where('user_id', Auth::id())->findOrFail($id);
        $tipos     = Evidencia::TIPOS;
        $estados   = Evidencia::ESTADOS;

        return view('evidencias::edit', compact('evidencia', 'tipos', 'estados'));
    }

    /**
     * Actualiza una evidencia en base de datos.
     */
    public function update(Request $request, int $id)
    {
        $evidencia = Evidencia::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string|max:2000',
            'tipo'         => 'required|in:documento,imagen,video,otro',
            'archivo'      => 'nullable|file|max:10240',
            'estado'       => 'required|in:pendiente,aprobada,rechazada',
            'observaciones'=> 'nullable|string|max:2000',
        ]);

        // Reemplazar archivo solo si se envía uno nuevo
        if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
            // Eliminar el archivo anterior si existe
            if ($evidencia->archivo) {
                Storage::disk('public')->delete($evidencia->archivo);
            }
            $validated['archivo'] = $request->file('archivo')
                ->store('evidencias/' . Auth::id(), 'public');
        }

        $evidencia->update([
            'titulo'        => $validated['titulo'],
            'descripcion'   => $validated['descripcion'] ?? null,
            'tipo'          => $validated['tipo'],
            'archivo'       => $validated['archivo'] ?? $evidencia->archivo,
            'estado'        => $validated['estado'],
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('evidencias.index')
            ->with('success', 'Evidencia actualizada correctamente.');
    }

    /**
     * Elimina (soft delete) una evidencia.
     */
    public function destroy(int $id)
    {
        $evidencia = Evidencia::where('user_id', Auth::id())->findOrFail($id);
        $evidencia->delete();

        return redirect()->route('evidencias.index')
            ->with('success', 'Evidencia eliminada correctamente.');
    }
}
