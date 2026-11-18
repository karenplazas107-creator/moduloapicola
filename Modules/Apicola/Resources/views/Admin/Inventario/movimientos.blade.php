@extends('apicola::Admin.layout')

@section('title', 'Movimientos de Inventario • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.inventario.index') }}">Inventario</a>
    <span>/</span>
    <span class="active-page">Movimientos</span>
@endsection

@section('styles')
    <style>
        .mov-chip-group { display: flex; flex-wrap: wrap; gap: 8px; }
        .mov-chip { display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #ffffff; font-size: 0.86rem; font-weight: 600; color: #334155; cursor: pointer; transition: all 0.15s ease; user-select: none; }
        .mov-chip:hover { border-color: #86c9a3; }
        .mov-chip input { accent-color: #286043; margin: 0; }
        .mov-chip.is-checked { border-color: #286043; background: #ecf6f0; color: #1d4a32; }
    </style>
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/movimientos.css') }}">
@endsection

@section('content')

    <!-- =========================================================================
         CABECERA SUPERIOR: MARCA SENA Y ESLOGAN APÍCOLA
         ========================================================================= -->
    <div class="mov-top-header">
        <div class="mov-top-brand">
            <div class="mov-top-logo">
                <img src="{{ asset('apicola-assets/assets/img/logo-apicola-honeycomb.png') }}" 
                     alt="SENA Apícola" 
                     onerror="this.onerror=null; this.src='{{ asset('modules/apicola/img/logo.png') }}';">
            </div>
            <div>
                <div class="mov-top-brand-title">Unidad Apícola SENA</div>
                <div class="mov-top-brand-sub">Sistema de Inventario</div>
            </div>
        </div>

        <div class="mov-slogan-pill">
            <span>Cuidamos nuestras abejas, cuidamos el futuro</span>
            <img src="{{ asset('modules/apicola/img/bee_mascot.jpg') }}?v={{ time() }}" 
                 alt="Abeja SENA" 
                 onerror="this.onerror=null; this.src='{{ asset('modules/apicola/img/logo.png') }}?v={{ time() }}';">
        </div>
    </div>

    <!-- =========================================================================
         LAYOUT DE 2 COLUMNAS (REGISTRO A LA IZQUIERDA, HISTORIAL Y FACTURA A LA DERECHA)
         ========================================================================= -->
    <div class="mov-dashboard-grid">

        <!-- ======================= COLUMNA IZQUIERDA ======================= -->
        <div class="mov-col-left">

            <!-- 1. TARJETA: REGISTRAR MOVIMIENTO DE INVENTARIO -->
            <div class="mov-card-box">
                <div class="mov-card-banner">
                    <h2 class="mov-card-banner-title">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        <span>Registrar movimiento de inventario</span>
                    </h2>
                </div>

                <div class="mov-card-body">
                    <form id="formMovimiento" onsubmit="event.preventDefault(); window.MovimientosApp.submitMovimiento();">
                        <!-- Botones Entrada / Salida -->
                        <div class="mov-toggle-row">
                            <button type="button" class="mov-btn-toggle" id="btnToggleEntrada" onclick="window.MovimientosApp.setTipo('Entrada')">
                                <i class="fa-solid fa-arrow-down" style="color: #286043;"></i> Entrada
                            </button>
                            <button type="button" class="mov-btn-toggle active" id="btnToggleSalida" onclick="window.MovimientosApp.setTipo('Salida')">
                                <i class="fa-solid fa-arrow-up" style="color: #286043;"></i> Salida
                            </button>
                        </div>

                        <!-- Motivo del movimiento (cambia según sea Entrada o Salida) -->
                        <div class="mov-form-group">
                            <label class="mov-form-label">Motivo <span style="color:#dc2626;">*</span></label>
                            <div id="motivoMovimientoGroup" class="mov-chip-group"></div>
                        </div>

                        <!-- Fila 1: Fecha y Bodega -->
                        <div class="mov-grid-2">
                            <div class="mov-form-group">
                                <label class="mov-form-label" for="inputFecha">Fecha</label>
                                <input type="date" 
                                       id="inputFecha" 
                                       class="mov-input-ctrl" 
                                       value="{{ date('Y-m-d') }}" 
                                       onchange="window.MovimientosApp.handleFechaChange(this.value)"
                                       required>
                            </div>

                            @if(!($variasBodegas ?? false))
                                {{-- Una sola bodega: no hay nada que elegir --}}
                                <input type="hidden" id="selectBodega" value="{{ $bodegas->first() }}">
                            @else
                            <div class="mov-form-group">
                                <label class="mov-form-label" for="selectBodega">Bodega</label>
                                <select id="selectBodega" class="mov-input-ctrl" onchange="window.MovimientosApp.handleBodegaChange(this.value)" required>
                                    @foreach($bodegas as $b)
                                        <option value="{{ $b }}" {{ $loop->first ? 'selected' : '' }}>{{ $b }}</option>
                                    @endforeach
                                    <option value="__nueva__">+ Registrar nueva bodega...</option>
                                </select>
                                <div id="seccionNuevaBodegaWrap" style="display: none; margin-top: 6px;">
                                    <input type="text" id="inputNombreNuevaBodega" class="mov-input-ctrl" placeholder="Nombre de la nueva bodega..." oninput="validarTextoSinNumerosMov(this, 'errorMovBodega')">
                                    <span id="errorMovBodega" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;"><i class="fas fa-circle-exclamation"></i> Bodega inválida: no se permiten números, solo letras.</span>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Fila 2: Tipo de producto y Producto -->
                        <div class="mov-form-group">
                            <label class="mov-form-label">Tipo de producto</label>
                            <div class="mov-chip-group" id="tipoProductoGroup">
                                <label class="mov-chip is-checked"><input type="radio" name="tipoProducto" value="Herramienta" checked onchange="window.MovimientosApp.setTipoProducto(this.value)"> Herramienta</label>
                                <label class="mov-chip"><input type="radio" name="tipoProducto" value="Insumo" onchange="window.MovimientosApp.setTipoProducto(this.value)"> Insumo</label>
                            </div>
                        </div>

                        <div class="mov-form-group">
                            <label class="mov-form-label" for="selectProducto">Producto</label>
                            <select id="selectProducto" class="mov-input-ctrl">
                                <option value="">Selecciona un producto</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id }}" data-nombre="{{ $p->nombre }}" data-bodega="{{ $p->bodega }}" data-stock="{{ $p->cantidad }}" data-unidad="{{ $p->unidad_medida }}">
                                        {{ $p->nombre }} (Stock: {{ $p->cantidad }} {{ $p->unidad_medida }})
                                    </option>
                                @endforeach
                            </select>
                            <div id="seccionNuevoProductoTexto" style="display: none; margin-top: 6px;">
                                <input type="text" id="inputNombreProductoManual" class="mov-input-ctrl" placeholder="Escribe el nombre del nuevo producto..." oninput="validarTextoSinNumerosMov(this, 'errorMovProducto')">
                                <span id="errorMovProducto" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;"><i class="fas fa-circle-exclamation"></i> Nombre inválido: no se permiten números, solo letras.</span>
                            </div>
                        </div>

                        <!-- Fila 3: Cantidad -->
                        <div class="mov-form-group">
                            <label class="mov-form-label" for="inputCantidad">Cantidad</label>
                            <input type="number" 
                                   id="inputCantidad" 
                                   class="mov-input-ctrl" 
                                   min="1" 
                                   placeholder="Ingresa la cantidad">
                        </div>

                        <!-- Fila 4: Observaciones (opcional) -->
                        <div class="mov-form-group">
                            <label class="mov-form-label" for="inputObservaciones">Observaciones (opcional)</label>
                            <textarea id="inputObservaciones" 
                                      class="mov-input-ctrl" 
                                      placeholder="Escribe una observación..."></textarea>
                        </div>

                        <!-- Botones de Acción Formulario -->
                        <div class="mov-form-actions-right">
                            <button type="button" class="mov-btn-clean" onclick="window.MovimientosApp.limpiarFormulario()">
                                Limpiar
                            </button>
                            <button type="submit" class="mov-btn-submit-main" id="btnSubmitMovimiento">
                                Registrar movimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>


        </div>

        <!-- ======================= COLUMNA DERECHA ======================= -->
        <div class="mov-col-right">

            <!-- 3. TARJETA: HISTORIAL DE MOVIMIENTOS -->
            <div class="mov-card-box">
                <div class="mov-card-banner">
                    <h2 class="mov-card-banner-title">
                        <i class="fa-regular fa-file-lines"></i>
                        <span>Historial de movimientos</span>
                    </h2>

                    <!-- Filtro desplegable -->
                    <select id="selectFiltroHistorial" class="mov-select-filter-hist" onchange="window.MovimientosApp.filtrarHistorial(this.value)">
                        <option value="all">Todos los movimientos</option>
                        <option value="Salida">Solo Salidas</option>
                        <option value="Entrada">Solo Entradas</option>
                    </select>
                </div>

                <!-- Tabla de historial -->
                <div class="mov-hist-table-wrap">
                    <table class="mov-hist-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Bodega</th>
                                <th style="text-align: center;">Total productos</th>
                                <th>Usuario</th>
                                <th style="width: 30px;"></th>
                            </tr>
                        </thead>
                        <tbody id="historialTableBody">
                            @forelse($historialComprobantes as $h)
                                <tr class="mov-hist-row {{ ($comprobanteActual && $comprobanteActual->id == $h->id) ? 'selected' : '' }}" 
                                    onclick="window.MovimientosApp.seleccionarMovimiento({{ $h->id }})">
                                    <td style="font-weight: 600;">{{ $h->fecha ? $h->fecha->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <span class="mov-badge-tipo {{ strtolower($h->tipo) }}">
                                            {{ $h->tipo }}
                                        </span>
                                    </td>
                                    <td>{{ $h->bodega }}</td>
                                    <td style="text-align: center; font-weight: 700;">{{ $h->total_unidades ?: ($h->total_productos ?: 0) }}</td>
                                    <td>{{ $h->responsable ?: 'Karen Plazas' }}</td>
                                    <td style="text-align: right;">
                                        <i class="fa-solid fa-chevron-right mov-chevron-icon"></i>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                                        <i class="fa-regular fa-folder-open" style="font-size: 1.5rem; display: block; margin-bottom: 6px;"></i>
                                        No hay movimientos registrados todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. TARJETA: FACTURA DE MOVIMIENTO / COMPROBANTE OFICIAL -->
            <div class="mov-invoice-card" id="invoiceCard">
                <div class="mov-invoice-inner">
                    <!-- Cabecera de la Factura -->
                    <div class="mov-invoice-header">
                        <div class="mov-invoice-brand">
                            <div class="mov-invoice-bee">
                                <img src="{{ asset('apicola-assets/assets/img/logo-apicola-bee-trans.png') }}" 
                                     alt="Abeja SENA" 
                                     onerror="this.onerror=null; this.src='{{ asset('modules/apicola/img/logo.png') }}';">
                            </div>
                            <div>
                                <div class="mov-invoice-brand-title">Unidad Apícola SENA</div>
                                <div class="mov-invoice-brand-sub">Sistema de Inventario</div>
                            </div>
                        </div>

                        <div class="mov-invoice-title-block">
                            <div class="mov-invoice-main-title">Factura de Movimiento</div>
                            <div class="mov-invoice-number" id="invNumeroText">
                                N.° {{ $comprobanteActual ? $comprobanteActual->numero_comprobante : $siguienteNumero }}
                            </div>
                        </div>
                    </div>

                    <!-- Metadatos de la Factura -->
                    <div class="mov-invoice-meta-grid">
                        <div class="mov-invoice-meta-item">
                            Tipo de movimiento: <strong id="invTipoText">{{ $comprobanteActual ? $comprobanteActual->tipo : 'Salida' }}</strong>
                        </div>
                        <div class="mov-invoice-meta-item">
                            Fecha: <strong id="invFechaText">{{ $comprobanteActual ? $comprobanteActual->fecha->format('d/m/Y') : date('d/m/Y') }}</strong>
                        </div>
                        <div class="mov-invoice-meta-item">
                            Bodega: <strong id="invBodegaText">{{ $comprobanteActual ? $comprobanteActual->bodega : 'Bodega Central' }}</strong>
                        </div>
                        <div class="mov-invoice-meta-item">
                            Usuario: <strong id="invUsuarioText">{{ $comprobanteActual ? ($comprobanteActual->responsable ?: 'Karen Plazas') : $responsable }}</strong>
                        </div>
                    </div>

                    <!-- Tabla de Productos de la Factura -->
                    <div class="mov-invoice-table-wrap">
                        <table class="mov-invoice-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Producto</th>
                                    <th style="width: 100px;">Cantidad</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody id="invoiceTableBody">
                                @if($comprobanteActual && $comprobanteActual->detalles->count() > 0)
                                    @foreach($comprobanteActual->detalles as $idx => $d)
                                        <tr>
                                            <td style="color: #64748b; font-weight: 700;">{{ $idx + 1 }}</td>
                                            <td style="font-weight: 700; color: #111827;">{{ $d->producto_nombre }}</td>
                                            <td style="font-weight: 800; color: #111827;">{{ $d->cantidad }}</td>
                                            <td style="color: #4b5563;">{{ $d->observaciones ?: ($comprobanteActual->tipo . ' de bodega') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                                            Seleccione un movimiento del historial para ver el detalle de la factura.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Total de productos -->
                    <div class="mov-invoice-total-box">
                        <div class="mov-invoice-total-label">Total de productos</div>
                        <div class="mov-invoice-total-num" id="invTotalProductosNum">
                            {{ $comprobanteActual ? ($comprobanteActual->total_unidades ?: $comprobanteActual->total_productos) : 0 }}
                        </div>
                    </div>

                    <!-- Pie: Nota y Firma del Responsable -->
                    <div class="mov-invoice-footer-row">
                        <div class="mov-invoice-note">
                            <i class="fa-regular fa-file-lines" style="color: var(--mov-primary); font-size: 1.1rem; flex-shrink: 0;"></i>
                            <span><strong>Nota:</strong> Este movimiento queda registrado en el historial del sistema.</span>
                        </div>

                        <div class="mov-invoice-signature">
                            <div class="mov-invoice-sig-line"></div>
                            <div class="mov-invoice-sig-name" id="invSigName">
                                {{ $comprobanteActual ? $comprobanteActual->responsable : $responsable }}
                            </div>
                            <div class="mov-invoice-sig-role">Responsable</div>
                        </div>
                    </div>

                    <!-- Botón para Imprimir / PDF -->
                    <div class="mov-invoice-actions">
                        <button type="button" class="mov-btn-invoice-print" onclick="window.MovimientosApp.abrirModalPos()">
                            <i class="fa-solid fa-receipt"></i> Imprimir comprobante POS
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@section('modals')
    <!-- =========================================================================
         MODAL FLOTANTE: COMPROBANTE TIPO POS (TIRILLA TÉRMICA 80mm)
         ========================================================================= -->
    <div class="pos-modal-backdrop" id="modalPosBackdrop" onclick="if(event.target === this) window.MovimientosApp.cerrarModalPos()">
        <div class="pos-modal-card">
            <!-- Barra superior del modal -->
            <div class="pos-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="pos-modal-badge">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <h3 class="pos-modal-title">Comprobante POS</h3>
                        <span class="pos-modal-sub">Tirilla térmica de movimiento de inventario</span>
                    </div>
                </div>
                <button type="button" class="pos-modal-close" onclick="window.MovimientosApp.cerrarModalPos()" title="Cerrar">&times;</button>
            </div>

            <!-- Cuerpo del modal: Tirilla POS física simulada -->
            <div class="pos-modal-body">
                <div class="pos-ticket-paper" id="posTicketPaper">
                    
                    <!-- Encabezado del ticket -->
                    <div class="pos-ticket-header">
                        <div class="pos-ticket-logo">
                            <img src="{{ asset('modules/apicola/img/bee_mascot.jpg') }}?v={{ time() }}" alt="SENA Apícola" onerror="this.onerror=null; this.src='{{ asset('modules/apicola/img/logo.png') }}?v={{ time() }}';">
                        </div>
                        <div class="pos-ticket-org">SENA - EMPRESA CEFA</div>
                        <div class="pos-ticket-title">UNIDAD APÍCOLA</div>
                        <div class="pos-ticket-center">Centro de Formación La Angostura</div>
                        <div class="pos-ticket-slogan">"Cuidamos nuestras abejas, cuidamos el futuro"</div>
                    </div>

                    <div class="pos-ticket-divider-double"></div>

                    <!-- Datos del Comprobante -->
                    <div class="pos-ticket-meta">
                        <div class="pos-meta-row">
                            <span class="pos-meta-lbl">COMPROBANTE:</span>
                            <span class="pos-meta-val" id="posNumero">0001-2026</span>
                        </div>
                        <div class="pos-meta-row">
                            <span class="pos-meta-lbl">TIPO:</span>
                            <span class="pos-meta-badge" id="posTipo">SALIDA</span>
                        </div>
                        <div class="pos-meta-row">
                            <span class="pos-meta-lbl">FECHA:</span>
                            <span class="pos-meta-val" id="posFecha">30/10/2026</span>
                        </div>
                        <div class="pos-meta-row">
                            <span class="pos-meta-lbl">BODEGA:</span>
                            <span class="pos-meta-val" id="posBodega">{{ $bodegas->first() }}</span>
                        </div>
                        <div class="pos-meta-row">
                            <span class="pos-meta-lbl">RESPONSABLE:</span>
                            <span class="pos-meta-val" id="posUsuario">Karen Plazas</span>
                        </div>
                    </div>

                    <div class="pos-ticket-divider-solid"></div>

                    <!-- Tabla de productos POS -->
                    <table class="pos-ticket-table">
                        <thead>
                            <tr>
                                <th style="width: 20%; text-align: center;">CANT</th>
                                <th style="width: 50%; text-align: left;">DESCRIPCIÓN</th>
                                <th style="width: 30%; text-align: right;">DETALLE</th>
                            </tr>
                        </thead>
                        <tbody id="posTicketTableBody">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>

                    <div class="pos-ticket-divider-double"></div>

                    <!-- Totales -->
                    <div class="pos-ticket-totals">
                        <div class="pos-total-row">
                            <span>TOTAL ÍTEMS:</span>
                            <strong id="posTotalItems">3</strong>
                        </div>
                        <div class="pos-total-row major">
                            <span>TOTAL UNIDADES:</span>
                            <strong id="posTotalUnidades">19</strong>
                        </div>
                    </div>

                    <div class="pos-ticket-divider-dash"></div>

                    <!-- Firma del Responsable -->
                    <div class="pos-ticket-signature">
                        <div class="pos-sig-line"></div>
                        <div class="pos-sig-name" id="posSigName">Karen Michelle Plazas Rojas</div>
                        <div class="pos-sig-title">RESPONSABLE DE BODEGA</div>
                        <div class="pos-sig-entity">SENA - REGIONAL HUILA</div>
                    </div>

                    <!-- Pie del Ticket -->
                    <div class="pos-ticket-footer">
                        <div>*** COMPROBANTE OFICIAL DE MOVIMIENTO ***</div>
                        <div>KARDEX DE INVENTARIO APÍCOLA</div>
                        <div style="margin-top: 4px; font-size: 8px;">Conserve este comprobante para control de existencias</div>
                    </div>

                </div>
            </div>

            <!-- Botones de Acción Flotantes -->
            <div class="pos-modal-actions">
                <button type="button" class="pos-btn-cancel" onclick="window.MovimientosApp.cerrarModalPos()">
                    <i class="fa-solid fa-xmark"></i> Cerrar
                </button>
                <button type="button" class="pos-btn-print" onclick="window.MovimientosApp.imprimirTicketDirecto()">
                    <i class="fa-solid fa-print"></i> Imprimir Ticket POS
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden iframe para impresión directa sin abrir pestaña ni recargar -->
    <iframe id="posPrintIframe" style="position: absolute; width: 0; height: 0; border: none; visibility: hidden;"></iframe>
@endsection

@section('scripts')
<script>
    window.MovimientosConfig = {
        productos: @json($productos),
        comprobanteActual: @json($comprobanteActual),
        historial: @json($historialComprobantes),
        storeUrl: "{{ route('apicola.admin.inventario.movimientos.store') }}",
        showUrl: "{{ url('apicola/inventario/movimientos') }}",
        printUrl: "{{ url('apicola/inventario/movimientos') }}",
        csrfToken: "{{ csrf_token() }}",
        variasBodegas: @json($variasBodegas ?? false),
        responsableDefault: "{{ $responsable }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/movimientos.js') }}?v={{ time() }}"></script>
@endsection
