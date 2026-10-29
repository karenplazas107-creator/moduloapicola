<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Apicola\Entities\Inventario;
use Modules\Apicola\Entities\InventarioMovimiento;
use Modules\Apicola\Entities\Movimiento;
use Modules\Apicola\Entities\MovimientoDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Bodegas únicas registradas
        $bodegasUnicas = Inventario::select('bodega')
            ->distinct()
            ->pluck('bodega')
            ->filter()
            ->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $items,
                'metrics' => $metrics,
                'categorias' => $categorias,
                'elementos' => $elementosUnicos,
                'bodegas' => $bodegasUnicas,
            ]);
        }

        return view('apicola::Admin.Inventario.index', compact('items', 'metrics', 'categorias', 'elementosUnicos', 'bodegasUnicas'));
    }

    /**
     * Guarda un nuevo elemento en el inventario.
     */
    public function store(Request $request)
    {
        $tipo = $request->input('tipo', 'Herramienta');
        
        // Determinar nombre de bodega (soporte para seleccionar existente o registrar una nueva)
        $bodegaRaw = trim($request->input('bodega', 'Bodega central'));
        if ($bodegaRaw === '__nueva__' || $request->filled('nombre_nueva_bodega')) {
            $bodega = trim($request->input('nombre_nueva_bodega', ''));
        } else {
            $bodega = $bodegaRaw;
        }

        if (empty($bodega)) {
            $bodega = 'Bodega central';
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
            'nombre_nueva_bodega' => 'nullable|string|max:150',
            'unidad_medida' => 'nullable|string|max:50',
            'cantidad' => 'nullable|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'estado' => 'nullable|in:Activo,Inactivo',
            'notas' => 'nullable|string',
        ]);

        if (($request->input('bodega') === '__nueva__' || $request->filled('nombre_nueva_bodega')) && $request->filled('nombre_nueva_bodega')) {
            $validated['bodega'] = trim($request->input('nombre_nueva_bodega'));
        }

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

    /**
     * Vista principal de Movimientos de Inventario (Entradas / Salidas multiproducto con comprobante).
     */
    public function movimientosIndex(Request $request)
    {
        // 1. Obtener bodegas únicas disponibles
        $bodegas = Inventario::select('bodega')
            ->distinct()
            ->pluck('bodega')
            ->filter()
            ->values();

        if ($bodegas->isEmpty()) {
            $bodegas = collect(['Bodega Central', 'Bodega del Apiario']);
        }

        // 2. Obtener productos activos organizados con su bodega y stock
        $productos = Inventario::where('estado', 'Activo')
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'codigo', 'categoria', 'bodega', 'unidad_medida', 'cantidad', 'stock_minimo']);

        // 3. Comprobante a mostrar (el último registrado o el solicitado por id)
        $comprobanteId = $request->get('comprobante_id');
        $comprobanteActual = null;
        if ($comprobanteId) {
            $comprobanteActual = Movimiento::with('detalles')->find($comprobanteId);
        }
        if (!$comprobanteActual) {
            $comprobanteActual = Movimiento::with('detalles')->orderBy('id', 'desc')->first();
        }

        // 4. Listado histórico de comprobantes con soporte de filtrado
        $query = Movimiento::with('detalles')->orderBy('id', 'desc');

        if ($request->filled('tipo') && in_array($request->tipo, ['Entrada', 'Salida'])) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('bodega') && $request->bodega !== 'all') {
            $query->where('bodega', $request->bodega);
        }

        $historialComprobantes = $query->take(50)->get();

        // 5. Responsable por defecto
        $responsable = Auth::user()?->full_name ?? Auth::user()?->nickname ?? 'Karen Michelle Plazas Rojas';

        // 6. Siguiente número de comprobante sugerido
        $siguienteNumero = Movimiento::generarSiguienteNumero();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'comprobante' => $comprobanteActual,
                'historial' => $historialComprobantes,
                'productos' => $productos,
                'siguiente_numero' => $siguienteNumero,
            ]);
        }

        return view('apicola::Admin.Inventario.movimientos', compact(
            'bodegas',
            'productos',
            'comprobanteActual',
            'historialComprobantes',
            'responsable',
            'siguienteNumero'
        ));
    }

    /**
     * Registra un nuevo comprobante con múltiples productos (Entrada o Salida)
     */
    public function movimientosStore(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'bodega' => 'required|string|max:150',
            'tipo' => 'required|in:Entrada,Salida',
            'productos' => 'required|array|min:1',
            'productos.*.nombre' => 'required|string|max:150',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.inventario_id' => 'nullable|integer',
            'productos.*.observaciones' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $tipo = $validated['tipo'];
        $bodega = $validated['bodega'];
        $fecha = Carbon::parse($validated['fecha']);
        $responsable = Auth::user()?->full_name ?? Auth::user()?->nickname ?? 'Karen Michelle Plazas Rojas';

        // Validar existencias si es Salida
        if ($tipo === 'Salida') {
            foreach ($validated['productos'] as $prod) {
                $nombre = trim($prod['nombre']);
                $cant = (int)$prod['cantidad'];
                
                $item = null;
                if (!empty($prod['inventario_id'])) {
                    $item = Inventario::find($prod['inventario_id']);
                }
                if (!$item) {
                    $item = Inventario::where('nombre', $nombre)->where('bodega', $bodega)->first();
                }

                if (!$item) {
                    return response()->json([
                        'success' => false,
                        'message' => "El producto '{$nombre}' no existe en {$bodega}.",
                    ], 422);
                }

                if ($item->cantidad < $cant) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para '{$nombre}' en {$bodega}. Stock disponible: {$item->cantidad} {$item->unidad_medida}, solicitados: {$cant}.",
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            $numeroComprobante = Movimiento::generarSiguienteNumero($fecha->year);
            $totalUnidades = array_sum(array_column($validated['productos'], 'cantidad'));

            $movimiento = Movimiento::create([
                'numero_comprobante' => $numeroComprobante,
                'tipo' => $tipo,
                'bodega' => $bodega,
                'fecha' => $fecha,
                'total_productos' => count($validated['productos']),
                'total_unidades' => $totalUnidades,
                'responsable' => $responsable,
                'user_id' => Auth::id(),
                'observaciones' => $validated['observaciones'] ?? ($tipo . ' de bodega'),
            ]);

            foreach ($validated['productos'] as $prod) {
                $nombre = trim($prod['nombre']);
                $cantidad = (int)$prod['cantidad'];
                $obs = !empty($prod['observaciones']) ? trim($prod['observaciones']) : ($tipo . ' de bodega');

                $item = null;
                if (!empty($prod['inventario_id'])) {
                    $item = Inventario::find($prod['inventario_id']);
                }
                if (!$item) {
                    $item = Inventario::where('nombre', $nombre)->where('bodega', $bodega)->first();
                }

                if (!$item) {
                    $codigo = sprintf('INV-%03d', Inventario::withTrashed()->count() + 1);
                    $item = Inventario::create([
                        'nombre' => $nombre,
                        'codigo' => $codigo,
                        'categoria' => 'Herramienta',
                        'bodega' => $bodega,
                        'unidad_medida' => 'unidad',
                        'cantidad' => 0,
                        'stock_minimo' => 5,
                        'estado' => 'Activo',
                        'responsable' => $responsable,
                        'notas' => "Creado automáticamente desde movimiento {$numeroComprobante}",
                    ]);
                }

                $stockAnterior = $item->cantidad;
                $stockNuevo = ($tipo === 'Entrada') ? ($stockAnterior + $cantidad) : max(0, $stockAnterior - $cantidad);

                $item->cantidad = $stockNuevo;
                $item->save();

                MovimientoDetalle::create([
                    'movimiento_id' => $movimiento->id,
                    'inventario_id' => $item->id,
                    'producto_nombre' => $item->nombre,
                    'producto_codigo' => $item->codigo,
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'observaciones' => $obs,
                ]);

                // Registrar en apicola_inventario_movimientos para trazabilidad individual
                InventarioMovimiento::create([
                    'inventario_id' => $item->id,
                    'movimiento_id' => $movimiento->id,
                    'tipo' => $tipo,
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo' => "Comprobante {$numeroComprobante} - " . $obs,
                    'responsable' => $responsable,
                    'fecha' => $fecha,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Movimiento {$movimiento->numero_comprobante} registrado con éxito.",
                'movimiento' => $movimiento->load('detalles'),
                'siguiente_numero' => Movimiento::generarSiguienteNumero(),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el movimiento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consulta los datos de un comprobante específico vía AJAX.
     */
    public function movimientosShow($id)
    {
        $movimiento = Movimiento::with('detalles')->findOrFail($id);

        return response()->json([
            'success' => true,
            'comprobante' => $movimiento,
        ]);
    }

    /**
     * Vista de impresión oficial del comprobante para imprimir o guardar como PDF.
     */
    public function movimientosPrint($id)
    {
        $movimiento = Movimiento::with('detalles')->findOrFail($id);

        return view('apicola::Admin.Inventario.print_comprobante', compact('movimiento'));
    }
}
