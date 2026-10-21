<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Apicola\Entities\Inventario;
use Modules\Apicola\Entities\InventarioMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InventarioController extends Controller
{
    /**
     * Muestra la vista principal del módulo de inventario.
     */
    public function index(Request $request)
    {
        $query = Inventario::with(['movimientos']);

        // Filtro de búsqueda por texto (nombre o código)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        // Filtro por bodega
        if ($request->filled('bodega') && !in_array($request->bodega, ['todas', 'all'])) {
            $query->where('bodega', $request->bodega);
        }

        // Filtro por categoría
        if ($request->filled('categoria') && !in_array($request->categoria, ['todas', 'all'])) {
            $query->where('categoria', $request->categoria);
        }

        // Filtro por estado
        if ($request->filled('estado') && !in_array($request->estado, ['todos', 'all'])) {
            $query->where('estado', $request->estado);
        }

        $items = $query->orderBy('codigo', 'asc')->orderBy('id', 'asc')->get();

        // Métricas dinámicas exactas según diseño de las 4 tarjetas
        $totalElementos = Inventario::count();
        $bodegaCentralCount = Inventario::where('bodega', 'like', '%Bodega central%')->count();
        $bodegaApiarioCount = Inventario::where('bodega', 'like', '%Bodega del apiario%')->count();
        $alertaStockCount = Inventario::whereColumn('cantidad', '<=', 'stock_minimo')->count();

        $metrics = [
            'elementos_registrados' => $totalElementos,
            'bodega_central' => $bodegaCentralCount,
            'bodega_apiario' => $bodegaApiarioCount,
            'alerta_stock' => $alertaStockCount,
        ];

        // Lista de elementos únicos para el selector en el modal
        $elementosUnicos = Inventario::select('nombre', 'unidad_medida', 'categoria')
            ->distinct()
            ->get()
            ->unique('nombre')
            ->values();

        // Categorías únicas
        $categorias = Inventario::select('categoria')
            ->distinct()
            ->pluck('categoria');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $items,
                'metrics' => $metrics,
                'categorias' => $categorias,
                'elementos' => $elementosUnicos,
            ]);
        }

        return view('apicola::Admin.Inventario.index', compact('items', 'metrics', 'categorias', 'elementosUnicos'));
    }

    /**
     * Guarda un nuevo elemento en el inventario.
     */
    public function store(Request $request)
    {
        $tipo = $request->input('tipo', 'Herramienta');
        $bodegaRaw = $request->input('bodega', 'Bodega central');
        
        // Normalizar nombre de bodega
        $bodega = 'Bodega central';
        if (str_contains($bodegaRaw, 'apiario') || str_contains($bodegaRaw, 'Apiario')) {
            $bodega = 'Bodega del apiario';
        }

        // Determinar nombre y unidad de medida
        $isNuevo = $request->input('elemento') === '__nuevo__' || $request->filled('nombre_nuevo');
        if ($isNuevo) {
            $nombre = trim($request->input('nombre_nuevo'));
            $unidadMedida = trim($request->input('unidad_medida', 'unidad'));
        } else {
            $nombre = trim($request->input('elemento'));
            // Intentar buscar la unidad de medida si ya existe un elemento con el mismo nombre
            $existente = Inventario::where('nombre', $nombre)->first();
            $unidadMedida = $request->input('unidad_medida', $existente ? $existente->unidad_medida : 'unidad');
        }

        if (empty($nombre)) {
            return response()->json([
                'success' => false,
                'message' => 'El nombre del elemento es requerido.',
            ], 422);
        }

        // Generar código autoincrementable único (INV-001, INV-002, ...)
        $codigo = $request->input('codigo');
        if (empty($codigo)) {
            $lastItem = Inventario::withTrashed()
                ->where('codigo', 'like', 'INV-%')
                ->orderByRaw('CAST(SUBSTRING(codigo, 5) AS UNSIGNED) DESC')
                ->first();

            $nextNum = 1;
            if ($lastItem && preg_match('/INV-(\d+)/', $lastItem->codigo, $matches)) {
                $nextNum = intval($matches[1]) + 1;
            }
            $codigo = sprintf('INV-%03d', $nextNum);
        }

        $cantidad = max(0, intval($request->input('cantidad_inicial', $request->input('cantidad', 0))));
        $stockMinimo = max(0, intval($request->input('stock_minimo', 5)));
        $responsable = Auth::user()?->full_name ?? Auth::user()?->nickname ?? 'Jhon Ramírez Ortiz';

        $item = Inventario::create([
            'nombre' => $nombre,
            'codigo' => $codigo,
            'categoria' => $tipo,
            'bodega' => $bodega,
            'unidad_medida' => $unidadMedida ?: 'unidad',
            'cantidad' => $cantidad,
            'stock_minimo' => $stockMinimo,
            'estado' => 'Activo',
            'responsable' => $responsable,
            'notas' => $request->input('notas', "Elemento registrado en {$bodega}."),
        ]);

        // Registrar movimiento inicial si se especificó cantidad > 0
        if ($item->cantidad > 0) {
            InventarioMovimiento::create([
                'inventario_id' => $item->id,
                'tipo' => 'Entrada',
                'cantidad' => $item->cantidad,
                'stock_anterior' => 0,
                'stock_nuevo' => $item->cantidad,
                'motivo' => 'Registro inicial en ' . $bodega,
                'responsable' => $responsable,
                'fecha' => now(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Elemento '{$item->nombre}' agregado al inventario con éxito.",
                'data' => $item->load('movimientos'),
            ], 201);
        }

        return redirect()->back()->with('success', "Elemento '{$item->nombre}' registrado.");
    }

    /**
     * Obtiene el detalle de un elemento con su historial de movimientos.
     */
    public function show($id)
    {
        $item = Inventario::with('movimientos')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * Actualiza un elemento de inventario existente.
     */
    public function update(Request $request, $id)
    {
        $item = Inventario::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'categoria' => 'required|string|max:100',
            'bodega' => 'nullable|string|max:150',
            'unidad_medida' => 'nullable|string|max:50',
            'stock_minimo' => 'required|integer|min:0',
            'estado' => 'nullable|in:Activo,Inactivo',
            'notas' => 'nullable|string',
        ]);

        $item->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Elemento '{$item->nombre}' actualizado con éxito.",
                'data' => $item->load('movimientos'),
            ]);
        }

        return redirect()->back()->with('success', "Elemento '{$item->nombre}' actualizado.");
    }

    /**
     * Alterna el estado (Activo / Inactivo) de un elemento sin eliminarlo.
     */
    public function toggleStatus($id)
    {
        $item = Inventario::findOrFail($id);
        $item->estado = ($item->estado === 'Inactivo') ? 'Activo' : 'Inactivo';
        $item->save();

        return response()->json([
            'success' => true,
            'message' => "Estado de '{$item->nombre}' cambiado a {$item->estado}.",
            'data' => $item,
        ]);
    }

    /**
     * Registra un movimiento (Entrada, Salida o Ajuste) y actualiza el stock.
     */
    public function registrarMovimiento(Request $request, $id)
    {
        $item = Inventario::findOrFail($id);

        $validated = $request->validate([
            'tipo' => 'required|in:Entrada,Salida,Ajuste',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
            'responsable' => 'nullable|string|max:150',
            'fecha' => 'nullable|date',
        ]);

        $stockAnterior = $item->cantidad;
        $cantidad = (int) $validated['cantidad'];
        $tipo = $validated['tipo'];
        $responsable = $validated['responsable'] ?: (Auth::user()?->full_name ?? Auth::user()?->nickname ?? 'Jhon Ramírez Ortiz');

        if ($tipo === 'Entrada') {
            $stockNuevo = $stockAnterior + $cantidad;
        } elseif ($tipo === 'Salida') {
            if ($cantidad > $stockAnterior) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede retirar {$cantidad} {$item->unidad_medida}. Stock disponible actual: {$stockAnterior}.",
                ], 422);
            }
            $stockNuevo = $stockAnterior - $cantidad;
        } else { // Ajuste
            $stockNuevo = $cantidad;
        }

        $item->cantidad = $stockNuevo;
        $item->save();

        $movimiento = InventarioMovimiento::create([
            'inventario_id' => $item->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'motivo' => $validated['motivo'],
            'responsable' => $responsable,
            'fecha' => !empty($validated['fecha']) ? Carbon::parse($validated['fecha']) : now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Movimiento de {$tipo} registrado correctamente.",
            'data' => [
                'item' => $item->load('movimientos'),
                'movimiento' => $movimiento,
            ],
        ]);
    }

    /**
     * Desactiva un elemento del inventario (marca su estado como Inactivo sin eliminarlo ni borrar sus movimientos).
     */
    public function destroy(Request $request, $id)
    {
        $item = Inventario::findOrFail($id);
        $nombre = $item->nombre;
        $codigo = $item->codigo;
        $item->estado = 'Inactivo';
        $item->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'nuevo_estado' => 'Inactivo',
                'message' => "Elemento {$codigo} ({$nombre}) desactivado con éxito del inventario.",
                'data' => $item->load('movimientos'),
            ]);
        }

        return redirect()->back()->with('success', "Elemento {$codigo} desactivado con éxito.");
    }
}
