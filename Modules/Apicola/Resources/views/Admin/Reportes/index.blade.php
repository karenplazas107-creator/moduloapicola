@extends('apicola::Admin.layout')

@section('title', 'Generador de Reportes • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.dashboard') }}">Inicio</a>
    <span>/</span>
    <span class="active-page">Generador de reportes</span>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/reportes.css') }}">
@endsection

@section('content')
<div class="rep-container">

    <!-- 1. Encabezado principal con título y perfil de usuario -->
    <div class="rep-header-row">
        <div>
            <span class="rep-header-tag">REPORTES</span>
            <h1 class="rep-header-title">Generador de reportes</h1>
        </div>

        <div class="rep-user-chip">
            <div class="rep-user-avatar">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->nickname ?? 'AC', 0, 2)) : 'AC' }}
            </div>
            <div class="rep-user-info">
                <span class="rep-user-name">
                    {{ auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : (auth()->check() ? auth()->user()->nickname : 'Andrés Felipe Cuéllar') }}
                </span>
                <span class="rep-user-role">
                    Rol: {{ auth()->check() && auth()->user()->roles->first() ? auth()->user()->roles->first()->name : 'Administrador' }} <i class="fas fa-chevron-down" style="font-size: 0.65rem; margin-left: 2px;"></i>
                </span>
            </div>
        </div>
    </div>

    <!-- 2. Grid Principal (2 Columnas: Izquierda formulario, Derecha métricas e info) -->
    <div class="rep-grid-layout">

        <!-- ================= COLUMNA IZQUIERDA: FORMULARIO GENERAR REPORTE ================= -->
        <div class="rep-card">
            <h2 class="rep-card-title">Generar reporte</h2>
            <p class="rep-card-desc">Elige el tipo y ajusta los filtros disponibles.</p>

            <form id="formFiltroReportes" onsubmit="event.preventDefault(); aplicarFiltros();">
                <input type="hidden" name="tipo" id="inputTipoReporte" value="{{ $tipoReporte }}">

                <!-- Selector: Tipo de Reporte -->
                <div class="rep-form-group">
                    <span class="rep-section-label">TIPO DE REPORTE</span>
                    <div class="rep-type-options">
                        
                        <!-- Opción 1: Producción de miel -->
                        <div class="rep-type-card {{ $tipoReporte === 'miel' ? 'active' : '' }}" onclick="seleccionarTipoReporte('miel', this)">
                            <div class="rep-type-icon">
                                <i class="fas fa-droplet"></i>
                            </div>
                            <div class="rep-type-body">
                                <span class="rep-type-name">Producción de miel</span>
                                <span class="rep-type-desc">Kilogramos por apiario, colmena y período.</span>
                            </div>
                        </div>

                        <!-- Opción 2: Estado de colmenas -->
                        <div class="rep-type-card {{ $tipoReporte === 'colmenas' ? 'active' : '' }}" onclick="seleccionarTipoReporte('colmenas', this)">
                            <div class="rep-type-icon">
                                <i class="fas fa-border-all"></i>
                            </div>
                            <div class="rep-type-body">
                                <span class="rep-type-name">Estado de colmenas</span>
                                <span class="rep-type-desc">Estado sanitario y última visita de cada colmena.</span>
                            </div>
                        </div>

                        <!-- Opción 3: Inventario -->
                        <div class="rep-type-card {{ $tipoReporte === 'inventario' ? 'active' : '' }}" onclick="seleccionarTipoReporte('inventario', this)">
                            <div class="rep-type-icon">
                                <i class="fas fa-box-archive"></i>
                            </div>
                            <div class="rep-type-body">
                                <span class="rep-type-name">Inventario</span>
                                <span class="rep-type-desc">Existencias, stock mínimo y movimientos.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtro: Apiario / Bodega -->
                <div class="rep-form-group">
                    <label class="rep-form-label" id="labelApiarioFiltro">APIARIO</label>
                    <select name="apiario" id="selectApiario" class="rep-select" onchange="aplicarFiltros()">
                        <option value="all">Todos los apiarios</option>
                        @foreach($apiarios as $ap)
                            <option value="{{ $ap->nombre_apiario }}" {{ $apiarioFiltro == $ap->nombre_apiario ? 'selected' : '' }}>
                                {{ $ap->nombre_apiario }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro: Rango de Fechas (Desde / Hasta) -->
                <div class="rep-form-group" id="groupFechas">
                    <div class="rep-date-row">
                        <div class="rep-date-wrap">
                            <label class="rep-form-label">DESDE</label>
                            <input type="date" name="desde" id="inputDesde" value="{{ $desde }}" onchange="aplicarFiltros()">
                        </div>
                        <div class="rep-date-wrap">
                            <label class="rep-form-label">HASTA</label>
                            <input type="date" name="hasta" id="inputHasta" value="{{ $hasta }}" onchange="aplicarFiltros()">
                        </div>
                    </div>
                </div>

                <!-- Filtro: Estado -->
                <div class="rep-form-group">
                    <label class="rep-form-label" id="labelEstadoFiltro">ESTADO DE LA COLMENA</label>
                    <select name="estado" id="selectEstado" class="rep-select" onchange="aplicarFiltros()">
                        <option value="all">Todos los estados</option>
                        <option value="Activa">Activa</option>
                        <option value="En revisión">En revisión</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                </div>

                <!-- Botón: Reporte -->
                <button type="button" class="rep-btn-submit" onclick="abrirModalReporteFlotante()">
                    <i class="fas fa-file-invoice"></i> Reporte
                </button>
            </form>
        </div>

        <!-- ================= COLUMNA DERECHA: MÉTRICAS Y DETALLES ================= -->
        <div class="rep-right-column">

            <!-- Fila Superior de 3 Métricas -->
            <div class="rep-metrics-row">
                <!-- 1. Miel del Período -->
                <div class="rep-metric-card border-amber">
                    <div class="rep-metric-label">MIEL DEL PERÍODO</div>
                    <div class="rep-metric-value" id="valMielPeriodo">{{ $metrics['miel_kg'] }} kg</div>
                    <div class="rep-metric-sub" id="subMielPeriodo">{{ $metrics['periodo_label'] }}</div>
                </div>

                <!-- 2. Visitas del Período -->
                <div class="rep-metric-card border-emerald">
                    <div class="rep-metric-label">VISITAS DEL PERÍODO</div>
                    <div class="rep-metric-value" id="valVisitasPeriodo">{{ $metrics['visitas_total'] }}</div>
                    <div class="rep-metric-sub" id="subVisitasPeriodo">En {{ $metrics['apiarios_activos'] }} apiarios activos</div>
                </div>

                <!-- 3. Reportes Generados -->
                <div class="rep-metric-card border-darkgreen">
                    <div class="rep-metric-label">REPORTES GENERADOS</div>
                    <div class="rep-metric-value" id="valReportesGenerados">{{ $metrics['reportes_generados'] }}</div>
                    <div class="rep-metric-sub">Últimos tres meses</div>
                </div>
            </div>

            <!-- Tarjeta Central de Detalle del Reporte -->
            <div class="rep-detail-card">
                <div class="rep-detail-header">
                    <h2 class="rep-detail-title" id="infoReporteTitulo">Producción de miel</h2>
                    <p class="rep-detail-subtitle" id="infoReporteDesc">
                        Suma los kilogramos registrados en las visitas vigentes. Las visitas anuladas no se cuentan.
                    </p>
                </div>

                <!-- Columnas Incluidas -->
                <div class="rep-columns-box">
                    <span class="rep-section-label">COLUMNAS INCLUIDAS</span>
                    <div class="rep-chips-wrap" id="chipsColumnas">
                        <!-- Chips dinámicos según el tipo de reporte -->
                        <span class="rep-column-chip">Apiario</span>
                        <span class="rep-column-chip">Colmena</span>
                        <span class="rep-column-chip">Última visita</span>
                        <span class="rep-column-chip">Visitas</span>
                        <span class="rep-column-chip">Miel (kg)</span>
                        <span class="rep-column-chip">Responsable</span>
                        <span class="rep-column-chip">Estado</span>
                    </div>
                </div>

                <!-- Últimos Reportes de este Tipo -->
                <div class="rep-history-box">
                    <span class="rep-section-label">ÚLTIMOS REPORTES DE ESTE TIPO</span>
                    <div class="rep-history-list" id="listaUltimosReportes">
                        @foreach($ultimosReportes[$tipoReporte] ?? [] as $rep)
                            <div class="rep-history-item">
                                <div class="rep-history-left">
                                    <span class="rep-history-item-title">{{ $rep['titulo'] }}</span>
                                    <div class="rep-history-item-meta">
                                        <span class="rep-history-badge {{ strtolower($rep['formato']) }}">{{ $rep['formato'] }}</span>
                                        <span>·</span>
                                        <span>{{ $rep['registros'] }} registros</span>
                                        <span>·</span>
                                        <span>{{ $rep['autor'] }}</span>
                                    </div>
                                </div>
                                <div>
                                    <a href="javascript:void(0)" onclick="descargarHistorico('{{ $rep['formato'] }}', '{{ $rep['tipo'] }}')" class="rep-history-download-link">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ================= SECCIÓN DE VISTA PREVIA INTERACTIVA ================= -->
            <div class="rep-preview-section" id="seccionVistaPrevia">
                <div class="rep-preview-top-bar">
                    <div class="rep-preview-top-left">
                        <h3 class="rep-preview-title">Vista previa de datos</h3>
                        <span class="rep-records-pill" id="badgeTotalRegistros">{{ count($previewData) }} registros</span>
                    </div>

                    <div class="rep-preview-actions">
                        <!-- Botón Reporte -->
                        <button type="button" class="rep-btn-pdf" onclick="abrirModalReporteFlotante()">
                            <i class="fas fa-file-invoice"></i> Reporte
                        </button>

                        <!-- Botón Descargar Excel -->
                        <button type="button" class="rep-btn-excel" onclick="exportarExcel()">
                            <i class="far fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>

                <!-- Spinner de Carga -->
                <div id="repLoadingSpinner" class="rep-loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p style="margin-top: 8px; font-size: 0.88rem;">Generando vista previa con datos en tiempo real...</p>
                </div>

                <!-- Contenedor de la Tabla -->
                <div class="rep-table-wrapper" id="repTableWrapper">
                    <table class="rep-table" id="tablaPreview">
                        <thead id="tablaPreviewHead">
                            <!-- Inyectado por JS según tipo -->
                        </thead>
                        <tbody id="tablaPreviewBody">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= MODAL FLOTANTE DE REPORTE OFICIAL ================= -->
<div class="ap-modal-backdrop" id="modalReporteFlotanteBackdrop" onclick="if(event.target === this) cerrarModalReporteFlotante()">
    <div class="ap-modal-window rep-modal-window">
        
        <!-- Barra Superior del Modal Flotante -->
        <div class="rep-modal-topbar">
            <div class="rep-modal-topbar-left">
                <div class="rep-modal-topbar-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h3 class="rep-modal-topbar-title" id="modalFloatingReportTitle">Reporte Oficial • SENA APÍCOLA</h3>
                    <span class="rep-modal-topbar-sub">Vista previa flotante · Formato ejecutivo optimizado para 1 página</span>
                </div>
            </div>

            <div class="rep-modal-topbar-actions">
                <button type="button" class="rep-modal-btn print" onclick="imprimirReporteAhiMismo()">
                    <i class="fas fa-print"></i> Imprimir / Guardar en PDF
                </button>
                <button type="button" class="rep-modal-btn excel" onclick="exportarExcel()">
                    <i class="far fa-file-excel"></i> Exportar Excel
                </button>
                <button type="button" class="rep-modal-btn close" onclick="cerrarModalReporteFlotante()" title="Cerrar ventana">
                    ✕
                </button>
            </div>
        </div>

        <!-- Cuerpo del Modal: Hoja Oficial Estilizada -->
        <div class="rep-modal-body">
            <div class="rep-float-sheet" id="repFloatSheet">
                <!-- Contenido inyectado dinámicamente -->
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    window.ReportesConfig = {
        tipoReporte: '{{ $tipoReporte }}',
        previewData: @json($previewData),
        ultimosReportes: @json($ultimosReportes),
        previewUrl: "{{ route('apicola.admin.reportes.preview') }}",
        excelUrl: "{{ route('apicola.admin.reportes.excel') }}",
        pdfUrl: "{{ route('apicola.admin.reportes.pdf') }}",
        userName: '{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? "Administrador") }}',
        logoUrl: "{{ asset('modules/apicola/img/logo.png') }}",
        logoFallback: "{{ asset('apicola-assets/assets/img/logo-apicola-bee-original.png') }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/reportes.js') }}"></script>
@endsection
