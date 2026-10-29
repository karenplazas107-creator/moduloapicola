@extends('apicola::Admin.layout')

@section('title', 'Inventario de las bodegas • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.inventario.index') }}">Inventario</a>
    <span>/</span>
    <span class="active-page">Inventario de las bodegas</span>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/inventario.css') }}">
@endsection

@section('content')

    <!-- Encabezado de la Vista (Imagen 1) -->
    <div class="inv-header-row">
        <div>
            <span class="inv-subtitle-tag">INVENTARIO</span>
            <h1 class="inv-main-title">Inventario de las bodegas</h1>
        </div>
        <div class="inv-user-chip">
            <div class="inv-avatar">
                {{ Auth::user()?->initials ?? 'JR' }}
            </div>
            <div>
                <div class="inv-user-name">
                    {{ Auth::user()?->full_name ?? 'Jhon Ramírez Ortiz' }}
                </div>
                <div class="inv-user-role">
                    Rol: Apicultor
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Tarjetas de Métricas (Imagen 1) -->
    <div class="inv-metrics-grid">
        <!-- 1. ELEMENTOS REGISTRADOS -->
        <div class="inv-metric-card border-green">
            <div class="inv-metric-title">ELEMENTOS REGISTRADOS</div>
            <div class="inv-metric-num" id="cardTotalElementos">{{ $metrics['elementos_registrados'] }}</div>
            <div class="inv-metric-sub">En las dos bodegas</div>
        </div>

        <!-- 2. BODEGA CENTRAL -->
        <div class="inv-metric-card border-green">
            <div class="inv-metric-title">BODEGA CENTRAL</div>
            <div class="inv-metric-num" id="cardBodegaCentral">{{ $metrics['bodega_central'] }}</div>
            <div class="inv-metric-sub">Centro de Formación</div>
        </div>

        <!-- 3. BODEGA DEL APIARIO -->
        <div class="inv-metric-card border-amber">
            <div class="inv-metric-title">BODEGA DEL APIARIO</div>
            <div class="inv-metric-num" id="cardBodegaApiario">{{ $metrics['bodega_apiario'] }}</div>
            <div class="inv-metric-sub">Apiario La Angostura</div>
        </div>

        <!-- 4. EN ALERTA DE STOCK -->
        <div class="inv-metric-card border-red">
            <div class="inv-metric-title">EN ALERTA DE STOCK</div>
            <div class="inv-metric-num" id="cardAlertaStock" style="color: #dc2626;">{{ $metrics['alerta_stock'] }}</div>
            <div class="inv-metric-sub">Bajo el mínimo</div>
        </div>
    </div>

    <!-- Sección Principal: Existencias por bodega -->
    <div class="inv-main-card">
        <div class="inv-section-header">
            <h2 class="inv-section-title">Existencias por bodega</h2>
            <p class="inv-section-desc">Un mismo elemento puede estar en las dos bodegas con cantidades distintas.</p>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="inv-filter-bar">
            <div class="inv-filter-left">
                <!-- Buscar por nombre... -->
                <div class="inv-search-wrap">
                    <input type="text" id="searchInput" class="inv-search-input" placeholder="Buscar por nombre..." oninput="filtrarInventario()">
                </div>

                <!-- Bodega: todas -->
                <select id="bodegaFilter" class="inv-select-filter" onchange="filtrarInventario()">
                    <option value="todas">Bodega: todas</option>
                    <option value="Bodega central">Bodega central</option>
                    <option value="Bodega del apiario">Bodega del apiario</option>
                    @if(isset($bodegasUnicas))
                        @foreach($bodegasUnicas as $b)
                            @if(!in_array(strtolower(trim($b)), ['bodega central', 'bodega del apiario']))
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endif
                        @endforeach
                    @endif
                </select>

                <!-- Categoría: todas -->
                <select id="categoriaFilter" class="inv-select-filter" onchange="filtrarInventario()">
                    <option value="todas">Categoría: todas</option>
                    <option value="Herramienta">Herramienta</option>
                    <option value="Insumo">Insumo</option>
                    @foreach($categorias as $cat)
                        @if(!in_array($cat, ['Herramienta', 'Insumo']))
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endif
                    @endforeach
                </select>

                <!-- Estado: todos -->
                <select id="estadoFilter" class="inv-select-filter" onchange="filtrarInventario()">
                    <option value="todos">Estado: todos</option>
                    <option value="Activo">Activos</option>
                    <option value="Inactivo">Inactivos</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('apicola.admin.inventario.movimientos.index') }}" class="inv-btn-movimientos-link" style="display: inline-flex; align-items: center; gap: 8px; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 700; font-size: 0.88rem; text-decoration: none; background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; transition: all 0.2s ease;">
                    <i class="fas fa-arrow-right-arrow-left"></i> Registrar movimientos
                </a>
                <!-- + Agregar al inventario -->
                <button type="button" class="inv-btn-add" onclick="abrirModalAgregar()">
                    + Agregar al inventario
                </button>
            </div>
        </div>

        <!-- Tabla de Existencias -->
        <div class="inv-table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>CÓDIGO</th>
                        <th>ELEMENTO</th>
                        <th>CATEGORÍA</th>
                        <th>BODEGA</th>
                        <th>DISPONIBLE</th>
                        <th>MÍNIMO</th>
                        <th>ESTADO</th>
                        <th style="text-align: center;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="inventarioTableBody">
                    <!-- Filas renderizadas por JS -->
                </tbody>
            </table>
        </div>

        <!-- Footer Informativo -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; font-size: 0.8rem; color: #94a3b8; flex-wrap: wrap; gap: 8px;">
            <div>
                Mostrando <strong id="shownCount" style="color: #0f172a;">{{ count($items) }}</strong> de <strong id="totalCount" style="color: #0f172a;">{{ count($items) }}</strong> registros
            </div>
            <div style="font-style: italic;">
                Las cantidades se actualizan mediante registros de movimiento para trazabilidad completa.
            </div>
        </div>
    </div>

@endsection

@section('modals')

    <!-- ==================== MODAL: AGREGAR AL INVENTARIO (Imágenes 2, 3 y 4) ==================== -->
    <div class="ap-modal-backdrop" id="modalAgregarBackdrop">
        <div class="ap-modal-card">
            <div class="ap-modal-inner">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <h2 class="ap-modal-heading" style="margin-bottom:0;" id="modalAgregarTitulo">Agregar al inventario</h2>
                    <button type="button" class="ap-modal-close-btn" onclick="cerrarModalAgregar()" title="Cerrar">&times;</button>
                </div>

                <form id="formAgregarInventario" onsubmit="guardarInventario(event)">
                    <!-- 1. TIPO (Herramienta / Insumo) -->
                    <div class="ap-field-group">
                        <label class="ap-label-text">TIPO</label>
                        <input type="hidden" id="inputTipo" value="Herramienta">
                        <div class="tipo-pill-group">
                            <button type="button" id="btnHerramienta" class="tipo-pill-btn active" onclick="seleccionarTipo('Herramienta')">Herramienta</button>
                            <button type="button" id="btnInsumo" class="tipo-pill-btn" onclick="seleccionarTipo('Insumo')">Insumo</button>
                        </div>
                    </div>

                    <!-- 2. ELEMENTO (Dropdown con opción '+ Registrar uno nuevo...') -->
                    <div class="ap-field-group">
                        <label class="ap-label-text" for="selectElemento">ELEMENTO</label>
                        <select id="selectElemento" class="ap-form-control" onchange="toggleRegistrarNuevo(this.value)" required>
                            <option value="Traje apícola">Traje apícola</option>
                            <option value="Ahumador">Ahumador</option>
                            <option value="Palanca apícola">Palanca apícola</option>
                            <option value="Alza">Alza</option>
                            @foreach($elementosUnicos as $elem)
                                @if(!in_array($elem->nombre, ['Traje apícola', 'Ahumador', 'Palanca apícola', 'Alza']))
                                    <option value="{{ $elem->nombre }}">{{ $elem->nombre }}</option>
                                @endif
                            @endforeach
                            <option value="__nuevo__">+ Registrar uno nuevo...</option>
                        </select>
                    </div>

                    <!-- 2.1 NUEVO ELEMENTO (Aparece sólo si selecciona '+ Registrar uno nuevo...') (Imagen 4) -->
                    <div id="seccionRegistrarNuevo" style="display: none; margin-bottom: 1.25rem;">
                        <div class="modal-grid-2">
                            <div>
                                <label class="ap-label-text" for="inputNombreNuevo">NOMBRE DEL ELEMENTO</label>
                                <input type="text" id="inputNombreNuevo" class="ap-form-control" placeholder="Ej: Traje apícola">
                            </div>
                            <div>
                                <label class="ap-label-text" for="selectUnidadMedida">UNIDAD DE MEDIDA</label>
                                <select id="selectUnidadMedida" class="ap-form-control">
                                    <option value="unidad">Unidad</option>
                                    <option value="kg">kg</option>
                                    <option value="litro">Litro</option>
                                    <option value="par">Par</option>
                                    <option value="caja">Caja</option>
                                    <option value="frasco">Frasco</option>
                                    <option value="rollo">Rollo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 3. BODEGA (Dropdown con opción '+ Registrar nueva bodega...') -->
                    <div class="ap-field-group">
                        <label class="ap-label-text" for="selectBodega">BODEGA</label>
                        <select id="selectBodega" class="ap-form-control" onchange="toggleRegistrarNuevaBodega(this.value)" required>
                            <option value="Bodega central">Bodega central · Centro de Formación</option>
                            <option value="Bodega del apiario">Bodega del apiario · Apiario La Angostura</option>
                            @if(isset($bodegasUnicas))
                                @foreach($bodegasUnicas as $b)
                                    @if(!in_array(strtolower(trim($b)), ['bodega central', 'bodega del apiario']))
                                        <option value="{{ $b }}">{{ $b }}</option>
                                    @endif
                                @endforeach
                            @endif
                            <option value="__nueva__">+ Registrar nueva bodega...</option>
                        </select>
                        <p class="ap-field-note">
                            El mismo elemento puede estar en distintas bodegas con cantidades diferentes.
                        </p>
                    </div>

                    <!-- 3.1 NUEVA BODEGA (Aparece si selecciona '+ Registrar nueva bodega...') -->
                    <div id="seccionRegistrarNuevaBodega" style="display: none; margin-bottom: 1.25rem;">
                        <label class="ap-label-text" for="inputNombreNuevaBodega">NOMBRE DE LA NUEVA BODEGA</label>
                        <input type="text" id="inputNombreNuevaBodega" class="ap-form-control" placeholder="Ej: Bodega Norte, Almacén de Insumos, Bodega 3...">
                    </div>

                    <!-- 4. CANTIDAD INICIAL Y STOCK MÍNIMO (2 Columnas) -->
                    <div class="modal-grid-2 ap-field-group">
                        <div>
                            <label class="ap-label-text" for="inputCantidadInicial">CANTIDAD INICIAL</label>
                            <input type="number" id="inputCantidadInicial" class="ap-form-control" min="0" value="0" required>
                        </div>
                        <div>
                            <label class="ap-label-text" for="inputStockMinimo">STOCK MÍNIMO</label>
                            <input type="number" id="inputStockMinimo" class="ap-form-control" min="0" value="5" required>
                        </div>
                    </div>

                    <!-- Botones Cancelar / Guardar -->
                    <div class="ap-modal-actions">
                        <button type="button" class="ap-btn-cancel" onclick="cerrarModalAgregar()">Cancelar</button>
                        <button type="submit" class="ap-btn-submit" id="btnSubmitAgregar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: EDITAR ELEMENTO ==================== -->
    <div class="ap-modal-backdrop" id="modalEditarBackdrop">
        <div class="ap-modal-card">
            <div class="ap-modal-inner">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <h2 class="ap-modal-heading" style="margin-bottom:0.2rem;">Editar elemento</h2>
                        <span id="editCodigoBadge" style="font-size:0.8rem; font-weight:700; color:#64748b;">INV-001</span>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="cerrarModalEditar()" title="Cerrar">&times;</button>
                </div>

                <form id="formEditarInventario" onsubmit="guardarEdicionInventario(event)">
                    <input type="hidden" id="editItemId" value="">

                    <!-- 1. TIPO / CATEGORÍA (Herramienta / Insumo) -->
                    <div class="ap-field-group">
                        <label class="ap-label-text">TIPO / CATEGORÍA</label>
                        <input type="hidden" id="editInputTipo" value="Herramienta">
                        <div class="tipo-pill-group">
                            <button type="button" id="editBtnHerramienta" class="tipo-pill-btn active" onclick="seleccionarTipoEdicion('Herramienta')">Herramienta</button>
                            <button type="button" id="editBtnInsumo" class="tipo-pill-btn" onclick="seleccionarTipoEdicion('Insumo')">Insumo</button>
                        </div>
                    </div>

                    <!-- 2. NOMBRE DEL ELEMENTO -->
                    <div class="ap-field-group">
                        <label class="ap-label-text" for="editNombre">NOMBRE DEL ELEMENTO</label>
                        <input type="text" id="editNombre" class="ap-form-control" required>
                    </div>

                    <!-- 3. BODEGA Y UNIDAD DE MEDIDA (2 Columnas) -->
                    <div class="modal-grid-2 ap-field-group">
                        <div>
                            <label class="ap-label-text" for="editSelectBodega">BODEGA</label>
                            <select id="editSelectBodega" class="ap-form-control" onchange="toggleEditarNuevaBodega(this.value)" required>
                                <option value="Bodega central">Bodega central</option>
                                <option value="Bodega del apiario">Bodega del apiario</option>
                                @if(isset($bodegasUnicas))
                                    @foreach($bodegasUnicas as $b)
                                        @if(!in_array(strtolower(trim($b)), ['bodega central', 'bodega del apiario']))
                                            <option value="{{ $b }}">{{ $b }}</option>
                                        @endif
                                    @endforeach
                                @endif
                                <option value="__nueva__">+ Registrar nueva bodega...</option>
                            </select>
                            <div id="seccionEditNuevaBodega" style="display: none; margin-top: 6px;">
                                <input type="text" id="editNombreNuevaBodega" class="ap-form-control" placeholder="Nombre de la nueva bodega...">
                            </div>
                        </div>

                        <div>
                            <label class="ap-label-text" for="editSelectUnidadMedida">UNIDAD DE MEDIDA</label>
                            <select id="editSelectUnidadMedida" class="ap-form-control">
                                <option value="unidad">Unidad</option>
                                <option value="caja">Caja</option>
                                <option value="frasco">Frasco</option>
                                <option value="kg">kg</option>
                                <option value="litro">Litro</option>
                                <option value="par">Par</option>
                                <option value="rollo">Rollo</option>
                            </select>
                        </div>
                    </div>

                    <!-- 4. CANTIDAD DISPONIBLE Y STOCK MÍNIMO (2 Columnas) -->
                    <div class="modal-grid-2 ap-field-group">
                        <div>
                            <label class="ap-label-text" for="editCantidad">CANTIDAD DISPONIBLE</label>
                            <input type="number" id="editCantidad" class="ap-form-control" min="0" required>
                        </div>
                        <div>
                            <label class="ap-label-text" for="editStockMinimo">STOCK MÍNIMO</label>
                            <input type="number" id="editStockMinimo" class="ap-form-control" min="0" required>
                        </div>
                    </div>

                    <!-- 5. ESTADO -->
                    <div class="ap-field-group">
                        <label class="ap-label-text" for="editSelectEstado">ESTADO</label>
                        <select id="editSelectEstado" class="ap-form-control">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                    <!-- Botones Cancelar / Guardar cambios -->
                    <div class="ap-modal-actions">
                        <button type="button" class="ap-btn-cancel" onclick="cerrarModalEditar()">Cancelar</button>
                        <button type="submit" class="ap-btn-submit" id="btnSubmitEditar">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: REGISTRAR MOVIMIENTO ==================== -->
    <div class="ap-modal-backdrop" id="modalMovimientoBackdrop">
        <div class="ap-modal-card">
            <div class="ap-modal-inner">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.35rem;">
                    <div>
                        <h2 class="ap-modal-heading" style="margin-bottom: 0.35rem;">Registrar movimiento</h2>
                        <p style="font-size: 0.82rem; color: #64748b; margin-top: 0; margin-bottom: 1.25rem;">
                            Actualiza la existencia física en bodega y conserva trazabilidad.
                        </p>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="cerrarModalMovimiento()" title="Cerrar">&times;</button>
                </div>

                <!-- Resumen de elemento -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div id="movItemNombre" style="font-weight: 800; font-size: 0.95rem; color: #0f172a;">-</div>
                        <div style="font-size: 0.78rem; color: #64748b;">
                            <span id="movItemCodigo">-</span> · <span id="movItemBodega">-</span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Disponible</div>
                        <div id="movItemDisponible" style="font-size: 1.25rem; font-weight: 900; color: #059669;">-</div>
                    </div>
                </div>

                <form id="formMovimiento" onsubmit="guardarMovimiento(event)">
                    <input type="hidden" id="movItemId" value="">
                    <input type="hidden" id="movTipo" value="Entrada">

                    <!-- Tipo de movimiento -->
                    <div class="ap-field-group">
                        <label class="ap-label-text">TIPO DE MOVIMIENTO</label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            <button type="button" id="btnTipoEntrada" class="tipo-pill-btn active" onclick="setTipoMovimiento('Entrada')">
                                Entrada (+)
                            </button>
                            <button type="button" id="btnTipoSalida" class="tipo-pill-btn" onclick="setTipoMovimiento('Salida')">
                                Salida (-)
                            </button>
                            <button type="button" id="btnTipoAjuste" class="tipo-pill-btn" onclick="setTipoMovimiento('Ajuste')">
                                Ajuste (=)
                            </button>
                        </div>
                    </div>

                    <!-- Cantidad y Fecha -->
                    <div class="modal-grid-2 ap-field-group">
                        <div>
                            <label class="ap-label-text" for="movCantidad">CANTIDAD</label>
                            <input type="number" id="movCantidad" class="ap-form-control" min="1" value="1" required>
                        </div>
                        <div>
                            <label class="ap-label-text" for="movFecha">FECHA</label>
                            <input type="date" id="movFecha" class="ap-form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Motivo -->
                    <div class="ap-field-group">
                        <label class="ap-label-text" for="movMotivo">MOTIVO / CONCEPTO</label>
                        <input type="text" id="movMotivo" class="ap-form-control" placeholder="Ej: Abastecimiento, práctica pedagógica, reposición" required>
                    </div>

                    <!-- Responsable -->
                    <div class="ap-field-group">
                        <label class="ap-label-text" for="movResponsable">RESPONSABLE</label>
                        <input type="text" id="movResponsable" class="ap-form-control" value="{{ Auth::user()?->full_name ?? 'Jhon Ramírez Ortiz' }}" required>
                    </div>

                    <div class="ap-modal-actions">
                        <button type="button" class="ap-btn-cancel" onclick="cerrarModalMovimiento()">Cancelar</button>
                        <button type="submit" class="ap-btn-submit" id="btnSubmitMovimiento">Confirmar movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: HISTORIAL DE MOVIMIENTOS ==================== -->
    <div class="ap-modal-backdrop" id="modalHistorialBackdrop">
        <div class="ap-modal-card wide">
            <div class="ap-modal-inner">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
                    <div>
                        <span id="histCategoria" class="inv-subtitle-tag">HERRAMIENTA</span>
                        <h2 id="histNombre" class="ap-modal-heading" style="margin-bottom: 0.2rem; font-size: 1.4rem;">Traje apícola</h2>
                        <div style="font-size: 0.82rem; color: #64748b;">
                            Código: <strong id="histCodigo" style="color: #0f172a;">INV-001</strong> · Bodega: <strong id="histBodega" style="color: #0f172a;">Bodega central</strong>
                        </div>
                    </div>
                    <div style="display:flex; align-items:flex-start; gap:12px;">
                        <div style="text-align: right;">
                            <span id="histEstadoBadge" class="inv-badge-disponible">Disponible</span>
                            <div style="font-size: 1.35rem; font-weight: 900; color: #0f172a; margin-top: 4px;" id="histDisponible">
                                12 unidad
                            </div>
                        </div>
                        <button type="button" class="ap-modal-close-btn" onclick="cerrarModalHistorial()" title="Cerrar">&times;</button>
                    </div>
                </div>

                <!-- Tabla de Historial -->
                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 1.25rem;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>FECHA</th>
                                <th>TIPO</th>
                                <th>CANTIDAD</th>
                                <th>STOCK RESULTANTE</th>
                                <th>MOTIVO</th>
                                <th>RESPONSABLE</th>
                            </tr>
                        </thead>
                        <tbody id="historialTableBody">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>
                </div>

                <div class="ap-modal-actions" style="justify-content: space-between;">
                    <a href="{{ route('apicola.admin.inventario.movimientos.index') }}" class="inv-action-link" style="font-size: 0.88rem; text-decoration: none;">
                        <i class="fas fa-arrow-right-arrow-left"></i> Ir a Movimientos
                    </a>
                    <button type="button" class="ap-btn-cancel" onclick="cerrarModalHistorial()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    window.InventarioConfig = {
        inventarioData: @json($items),
        storeUrl: "{{ route('apicola.admin.inventario.store') }}",
        baseUrl: "{{ url('apicola/inventario') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/inventario.js') }}?v={{ time() }}"></script>
@endsection
