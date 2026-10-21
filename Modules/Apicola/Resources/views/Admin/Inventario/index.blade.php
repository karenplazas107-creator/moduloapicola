@extends('apicola::Admin.layout')

@section('title', 'Inventario de las bodegas • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.inventario.index') }}">Inventario</a>
    <span>/</span>
    <span class="active-page">Inventario de las bodegas</span>
@endsection

@section('styles')
<style>
    /* ================= ESTILOS PREMIUM E INTERFAZ ELEGANTE ================= */
    @keyframes apFadeUp {
        0% {
            opacity: 0;
            transform: translateY(18px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .inv-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1.25rem;
        animation: apFadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .inv-subtitle-tag {
        font-family: var(--ap-font-main);
        font-size: 0.72rem;
        font-weight: 800;
        color: #047857;
        background: rgba(4, 120, 87, 0.08);
        padding: 4px 10px;
        border-radius: 6px;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 4px;
    }
    .inv-main-title {
        font-family: var(--ap-font-display, 'Outfit', sans-serif);
        font-size: 2.15rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.15;
        letter-spacing: -0.03em;
        margin: 0;
    }
    .inv-user-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #ffffff;
        padding: 7px 16px 7px 7px;
        border-radius: 50px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .inv-user-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }
    .inv-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--ap-font-display, 'Outfit', sans-serif);
        font-weight: 800;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(4, 120, 87, 0.28);
    }
    .inv-user-name {
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .inv-user-role {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* 4 Tarjetas de Métricas (Entrada en Cascada) */
    .inv-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.35rem;
        margin-bottom: 2.25rem;
        animation: apFadeUp 0.55s 0.08s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .inv-metric-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.85);
        padding: 1.35rem 1.5rem;
        box-shadow: 0 4px 18px -3px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 114px;
        position: relative;
        overflow: hidden;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .inv-metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.08);
    }
    .inv-metric-card.border-green {
        border-left: 5px solid #059669;
    }
    .inv-metric-card.border-amber {
        border-left: 5px solid #f59e0b;
    }
    .inv-metric-card.border-red {
        border-left: 5px solid #ef4444;
    }
    .inv-metric-title {
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        letter-spacing: 0.85px;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }
    .inv-metric-num {
        font-family: var(--ap-font-display, 'Outfit', sans-serif);
        font-size: 2.45rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        letter-spacing: -0.03em;
        font-variant-numeric: tabular-nums;
        margin-bottom: 0.35rem;
    }
    .inv-metric-sub {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Sección Principal: Existencias por bodega */
    .inv-main-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.85);
        padding: 2rem;
        box-shadow: 0 4px 22px -4px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
        animation: apFadeUp 0.65s 0.16s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .inv-section-header {
        margin-bottom: 1.5rem;
    }
    .inv-section-title {
        font-family: var(--ap-font-display, 'Outfit', sans-serif);
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }
    .inv-section-desc {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
    }

    /* Barra de Búsqueda y Filtros */
    .inv-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }
    .inv-filter-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        flex-wrap: wrap;
    }
    .inv-search-wrap {
        position: relative;
        min-width: 250px;
        max-width: 320px;
        flex: 1;
    }
    .inv-search-input {
        width: 100%;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.88rem;
        font-family: var(--ap-font-main);
        color: #0f172a;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .inv-search-input:focus {
        border-color: #059669;
        box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.12);
    }
    .inv-select-filter {
        padding: 10px 34px 10px 14px;
        border-radius: 12px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") no-repeat right 12px center;
        background-size: 16px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        font-size: 0.88rem;
        font-family: var(--ap-font-main);
        color: #334155;
        font-weight: 500;
        outline: none;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .inv-select-filter:focus {
        border-color: #059669;
        box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.12);
    }
    .inv-btn-add {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
        border: none;
        padding: 10px 22px;
        border-radius: 12px;
        font-size: 0.88rem;
        font-family: var(--ap-font-main);
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.25);
    }
    .inv-btn-add:hover {
        background: linear-gradient(135deg, #047857 0%, #064e3b 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
    }

    /* Tabla de Existencias */
    .inv-table-wrap {
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .inv-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.88rem;
    }
    .inv-table thead {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .inv-table th {
        padding: 0.95rem 1.25rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        white-space: nowrap;
    }
    .inv-table td {
        padding: 1.05rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }
    .inv-table tbody tr {
        transition: background-color 0.18s ease;
    }
    .inv-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .inv-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Filas destacadas cuando están bajo stock */
    .inv-table tbody tr.row-stock-bajo {
        background-color: #fffbfb;
    }
    .inv-table tbody tr.row-stock-bajo:hover {
        background-color: #fef2f2;
    }

    /* Badges de Estado */
    .inv-badge-disponible {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 700;
        background: #ecfdf5;
        color: #059669;
    }
    .inv-badge-bajo {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 700;
        background: #fef2f2;
        color: #dc2626;
    }

    /* Enlaces de Acción */
    .inv-action-links {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .inv-action-link {
        color: #059669;
        font-weight: 700;
        font-size: 0.84rem;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        text-decoration: none;
        transition: color 0.15s;
    }
    .inv-action-link:hover {
        color: #047857;
        text-decoration: underline;
    }

    /* ==================== ESTILOS DEL MODAL (Fieles a imágenes 2, 3 y 4) ==================== */
    .ap-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.52);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .ap-modal-backdrop.is-open {
        display: flex;
    }
    .ap-modal-card {
        background: #ffffff;
        border-radius: 24px;
        width: 100%;
        max-width: 490px;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.28);
        border: 1px solid rgba(226, 232, 240, 0.9);
        overflow: hidden;
        animation: apFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ap-modal-card.wide {
        max-width: 740px;
    }
    @keyframes apFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .ap-modal-inner {
        padding: 2.25rem 2.4rem 2rem;
    }
    .ap-modal-heading {
        font-family: var(--ap-font-display, 'Outfit', sans-serif);
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.025em;
        margin-bottom: 1.35rem;
    }
    .ap-field-group {
        margin-bottom: 1.25rem;
    }
    .ap-label-text {
        display: block;
        font-size: 0.72rem;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 0.45rem;
    }

    /* Toggle Pills para TIPO (Herramienta / Insumo) */
    .tipo-pill-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 1.25rem;
    }
    .tipo-pill-btn {
        padding: 10px 14px;
        border-radius: 10px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
    }
    .tipo-pill-btn.active {
        background: #ecfdf5;
        border-color: #059669;
        color: #059669;
        font-weight: 700;
    }

    /* Selects e Inputs en Modal */
    .ap-form-control {
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.9rem;
        color: #0f172a;
        outline: none;
        transition: all 0.2s;
    }
    .ap-form-control:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
    }
    select.ap-form-control {
        background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") no-repeat right 12px center;
        background-size: 16px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        cursor: pointer;
    }
    .ap-field-note {
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 5px;
        line-height: 1.35;
    }

    /* Modal Grid 2 columnas */
    .modal-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    /* Botones Cancelar / Guardar */
    .ap-modal-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin-top: 1.75rem;
    }
    .ap-btn-cancel {
        background: #f1f5f9;
        color: #475569;
        border: none;
        padding: 11px 24px;
        border-radius: 12px;
        font-size: 0.88rem;
        font-family: var(--ap-font-main);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ap-btn-cancel:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .ap-btn-submit {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
        border: none;
        padding: 11px 28px;
        border-radius: 12px;
        font-size: 0.88rem;
        font-family: var(--ap-font-main);
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.28);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ap-btn-submit:hover {
        background: linear-gradient(135deg, #047857 0%, #064e3b 100%);
        transform: translateY(-1.5px);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.38);
    }

    /* Historial Tabla en Modal */
    .history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
        margin-top: 10px;
    }
    .history-table th {
        background: #f8fafc;
        padding: 8px 12px;
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        text-align: left;
    }
    .history-table td {
        padding: 9px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    @media (max-width: 900px) {
        .inv-metrics-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 600px) {
        .inv-metrics-grid {
            grid-template-columns: 1fr;
        }
        .inv-filter-left {
            flex-direction: column;
            align-items: stretch;
        }
        .inv-search-wrap {
            max-width: 100%;
        }
    }
</style>
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

            <div>
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
                        <th>ACCIONES</th>
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

                    <!-- 3. BODEGA (Dropdown con texto de ayuda) -->
                    <div class="ap-field-group">
                        <label class="ap-label-text" for="selectBodega">BODEGA</label>
                        <select id="selectBodega" class="ap-form-control" required>
                            <option value="Bodega central">Bodega central · Centro de Formación</option>
                            <option value="Bodega del apiario">Bodega del apiario · Apiario La Angostura</option>
                        </select>
                        <p class="ap-field-note">
                            El mismo elemento puede estar en las dos bodegas con cantidades distintas.
                        </p>
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
                    <button type="button" class="inv-action-link" style="font-size: 0.88rem;" onclick="abrirMovimientoDesdeHistorial()">
                        + Registrar nuevo movimiento
                    </button>
                    <button type="button" class="ap-btn-cancel" onclick="cerrarModalHistorial()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Datos inyectados desde el backend
    let inventarioData = @json($items);
    let selectedItemId = null;

    document.addEventListener('DOMContentLoaded', () => {
        renderInventarioTable();
        actualizarMetricas();
    });

    // ==================== RENDERIZADO Y FILTROS ====================
    function renderInventarioTable() {
        const search = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
        const bodega = document.getElementById('bodegaFilter')?.value || 'todas';
        const categoria = document.getElementById('categoriaFilter')?.value || 'todas';
        const estado = document.getElementById('estadoFilter')?.value || 'todos';

        const filtered = inventarioData.filter(item => {
            const matchSearch = item.nombre.toLowerCase().includes(search) ||
                                item.codigo.toLowerCase().includes(search);
            const matchBodega = (bodega === 'todas') || (item.bodega === bodega);
            const matchCategoria = (categoria === 'todas') || (item.categoria.toLowerCase() === categoria.toLowerCase());
            const matchEstado = (estado === 'todos') || (item.estado === estado);

            return matchSearch && matchBodega && matchCategoria && matchEstado;
        });

        const tbody = document.getElementById('inventarioTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        document.getElementById('shownCount').textContent = filtered.length;
        document.getElementById('totalCount').textContent = inventarioData.length;

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                        No se encontraron registros de inventario con los filtros aplicados.
                    </td>
                </tr>
            `;
            return;
        }

        filtered.forEach(item => {
            const tr = document.createElement('tr');
            const isStockBajo = parseInt(item.cantidad) <= parseInt(item.stock_minimo);
            const isInactive = (item.estado === 'Inactivo');

            if (isStockBajo) {
                tr.className = 'row-stock-bajo';
            }

            let estadoBadge = isStockBajo 
                ? `<span class="inv-badge-bajo">Stock bajo</span>`
                : `<span class="inv-badge-disponible">Disponible</span>`;

            if (isInactive) {
                estadoBadge += ` <span style="display:inline-block; padding: 2px 8px; border-radius: 50px; font-size: 0.72rem; font-weight: 800; background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; margin-left: 4px;">Inactivo</span>`;
            }

            const unidad = item.unidad_medida || 'unidad';

            tr.innerHTML = `
                <td style="font-weight: 500; color: #475569;">
                    ${item.codigo}
                </td>
                <td style="font-weight: 800; color: #0f172a;">
                    ${item.nombre}
                </td>
                <td style="color: #475569;">
                    ${item.categoria}
                </td>
                <td style="color: #475569;">
                    ${item.bodega}
                </td>
                <td>
                    <strong style="color: #0f172a; font-weight: 800;">${item.cantidad}</strong> 
                    <span style="color: #64748b; font-size: 0.82rem;">${unidad}</span>
                </td>
                <td style="color: #475569; font-weight: 500;">
                    ${item.stock_minimo}
                </td>
                <td>
                    ${estadoBadge}
                </td>
                <td>
                    <div class="inv-action-links">
                        <button type="button" class="inv-action-link" onclick="abrirModalMovimiento(${item.id})">Movimiento</button>
                        <button type="button" class="inv-action-link" onclick="abrirModalHistorial(${item.id})">Historial</button>
                        ${isInactive
                            ? `<button type="button" class="inv-action-link" style="color: #059669; font-weight: 700;" onclick="toggleInventarioStatus(${item.id})">Activar</button>`
                            : `<button type="button" class="inv-action-link" style="color: #ef4444; font-weight: 700;" onclick="desactivarInventarioItem(${item.id}, '${item.codigo}', '${item.nombre}')">Desactivar</button>`
                        }
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filtrarInventario() {
        renderInventarioTable();
    }

    function actualizarMetricas() {
        const total = inventarioData.length;
        const bodegaCentral = inventarioData.filter(i => (i.bodega || '').includes('Bodega central')).length;
        const bodegaApiario = inventarioData.filter(i => (i.bodega || '').includes('apiario') || (i.bodega || '').includes('Apiario')).length;
        const alertaStock = inventarioData.filter(i => parseInt(i.cantidad) <= parseInt(i.stock_minimo)).length;

        document.getElementById('cardTotalElementos').textContent = total;
        document.getElementById('cardBodegaCentral').textContent = bodegaCentral;
        document.getElementById('cardBodegaApiario').textContent = bodegaApiario;
        document.getElementById('cardAlertaStock').textContent = alertaStock;
    }

    // ==================== MODAL: AGREGAR AL INVENTARIO ====================
    function abrirModalAgregar() {
        document.getElementById('formAgregarInventario').reset();
        seleccionarTipo('Herramienta');
        document.getElementById('selectElemento').value = 'Traje apícola';
        toggleRegistrarNuevo('Traje apícola');
        document.getElementById('selectBodega').value = 'Bodega central';
        document.getElementById('inputCantidadInicial').value = 0;
        document.getElementById('inputStockMinimo').value = 5;
        document.getElementById('modalAgregarBackdrop').classList.add('is-open');
    }

    function cerrarModalAgregar() {
        document.getElementById('modalAgregarBackdrop').classList.remove('is-open');
    }

    function seleccionarTipo(tipo) {
        document.getElementById('inputTipo').value = tipo;
        const btnHerramienta = document.getElementById('btnHerramienta');
        const btnInsumo = document.getElementById('btnInsumo');

        if (tipo === 'Herramienta') {
            btnHerramienta.classList.add('active');
            btnInsumo.classList.remove('active');
        } else {
            btnInsumo.classList.add('active');
            btnHerramienta.classList.remove('active');
        }
    }

    function toggleRegistrarNuevo(val) {
        const seccion = document.getElementById('seccionRegistrarNuevo');
        const inputNombreNuevo = document.getElementById('inputNombreNuevo');
        if (val === '__nuevo__') {
            seccion.style.display = 'block';
            inputNombreNuevo.required = true;
            inputNombreNuevo.focus();
        } else {
            seccion.style.display = 'none';
            inputNombreNuevo.required = false;
        }
    }

    async function guardarInventario(e) {
        e.preventDefault();

        const tipo = document.getElementById('inputTipo').value;
        const elementoSelect = document.getElementById('selectElemento').value;
        const isNuevo = (elementoSelect === '__nuevo__');
        const nombreNuevo = document.getElementById('inputNombreNuevo').value.trim();
        const unidadMedida = isNuevo ? document.getElementById('selectUnidadMedida').value : 'unidad';
        const bodega = document.getElementById('selectBodega').value;
        const cantidadInicial = parseInt(document.getElementById('inputCantidadInicial').value) || 0;
        const stockMinimo = parseInt(document.getElementById('inputStockMinimo').value) || 0;

        const payload = {
            tipo: tipo,
            elemento: elementoSelect,
            nombre_nuevo: isNuevo ? nombreNuevo : '',
            unidad_medida: unidadMedida,
            bodega: bodega,
            cantidad_inicial: cantidadInicial,
            stock_minimo: stockMinimo,
        };

        const btnSubmit = document.getElementById('btnSubmitAgregar');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando...';

        try {
            const resp = await fetch(`{{ route('apicola.admin.inventario.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                inventarioData.push(data.data);
                cerrarModalAgregar();
                renderInventarioTable();
                actualizarMetricas();
                showToast(data.message || 'Elemento agregado con éxito');
            } else {
                alert(data.message || 'Error al guardar el elemento.');
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al servidor al guardar elemento.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar';
        }
    }

    // ==================== MODAL: REGISTRAR MOVIMIENTO ====================
    function abrirModalMovimiento(id) {
        const item = inventarioData.find(i => i.id === id);
        if (!item) return;

        selectedItemId = item.id;
        document.getElementById('formMovimiento').reset();
        document.getElementById('movItemId').value = item.id;
        document.getElementById('movItemNombre').textContent = item.nombre;
        document.getElementById('movItemCodigo').textContent = item.codigo;
        document.getElementById('movItemBodega').textContent = item.bodega;
        document.getElementById('movItemDisponible').textContent = `${item.cantidad} ${item.unidad_medida || 'unidad'}`;
        document.getElementById('movFecha').value = new Date().toISOString().split('T')[0];
        setTipoMovimiento('Entrada');

        document.getElementById('modalMovimientoBackdrop').classList.add('is-open');
    }

    function cerrarModalMovimiento() {
        document.getElementById('modalMovimientoBackdrop').classList.remove('is-open');
    }

    function setTipoMovimiento(tipo) {
        document.getElementById('movTipo').value = tipo;
        const btnE = document.getElementById('btnTipoEntrada');
        const btnS = document.getElementById('btnTipoSalida');
        const btnA = document.getElementById('btnTipoAjuste');

        btnE.className = 'tipo-pill-btn' + (tipo === 'Entrada' ? ' active' : '');
        btnS.className = 'tipo-pill-btn' + (tipo === 'Salida' ? ' active' : '');
        btnA.className = 'tipo-pill-btn' + (tipo === 'Ajuste' ? ' active' : '');
    }

    async function guardarMovimiento(e) {
        e.preventDefault();
        const id = document.getElementById('movItemId').value;
        const payload = {
            tipo: document.getElementById('movTipo').value,
            cantidad: parseInt(document.getElementById('movCantidad').value) || 1,
            fecha: document.getElementById('movFecha').value,
            motivo: document.getElementById('movMotivo').value.trim(),
            responsable: document.getElementById('movResponsable').value.trim(),
        };

        const btnSubmit = document.getElementById('btnSubmitMovimiento');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Confirmando...';

        try {
            const resp = await fetch(`{{ url('apicola/inventario') }}/${id}/movimiento`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                const idx = inventarioData.findIndex(i => i.id == id);
                if (idx !== -1) {
                    inventarioData[idx] = data.data.item;
                }

                cerrarModalMovimiento();
                renderInventarioTable();
                actualizarMetricas();
                showToast(data.message || 'Movimiento registrado con éxito');
            } else {
                alert(data.message || 'Error al registrar el movimiento.');
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al registrar el movimiento.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Confirmar movimiento';
        }
    }

    // ==================== MODAL: HISTORIAL ====================
    async function abrirModalHistorial(id) {
        selectedItemId = id;
        const item = inventarioData.find(i => i.id === id);
        if (!item) return;

        document.getElementById('histCategoria').textContent = (item.categoria || 'HERRAMIENTA').toUpperCase();
        document.getElementById('histNombre').textContent = item.nombre;
        document.getElementById('histCodigo').textContent = item.codigo;
        document.getElementById('histBodega').textContent = item.bodega;
        document.getElementById('histDisponible').textContent = `${item.cantidad} ${item.unidad_medida || 'unidad'}`;

        const isStockBajo = parseInt(item.cantidad) <= parseInt(item.stock_minimo);
        const badge = document.getElementById('histEstadoBadge');
        if (isStockBajo) {
            badge.className = 'inv-badge-bajo';
            badge.textContent = 'Stock bajo';
        } else {
            badge.className = 'inv-badge-disponible';
            badge.textContent = 'Disponible';
        }

        const tbody = document.getElementById('historialTableBody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 1.25rem;">Cargando historial...</td></tr>';
        document.getElementById('modalHistorialBackdrop').classList.add('is-open');

        try {
            const resp = await fetch(`{{ url('apicola/inventario') }}/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const res = await resp.json();

            if (res.success && res.data.movimientos) {
                tbody.innerHTML = '';
                if (res.data.movimientos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Sin movimientos registrados aún.</td></tr>';
                    return;
                }

                res.data.movimientos.forEach(m => {
                    const tr = document.createElement('tr');
                    const badgeColor = m.tipo === 'Entrada' ? '#059669' : (m.tipo === 'Salida' ? '#dc2626' : '#0284c7');
                    const sign = m.tipo === 'Entrada' ? '+' : (m.tipo === 'Salida' ? '-' : '=');

                    tr.innerHTML = `
                        <td style="font-weight: 600; color: #64748b;">${m.fecha ? m.fecha.substring(0, 10) : '-'}</td>
                        <td>
                            <strong style="color: ${badgeColor}; font-size: 0.8rem;">
                                ${sign} ${m.tipo}
                            </strong>
                        </td>
                        <td style="font-weight: 800; color: #0f172a;">${m.cantidad}</td>
                        <td style="font-weight: 600; color: #64748b;">${m.stock_nuevo}</td>
                        <td>${m.motivo || '-'}</td>
                        <td style="color: #64748b;">${m.responsable || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        } catch (e) {
            console.error(e);
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #ef4444; padding: 1rem;">No se pudo cargar el historial.</td></tr>';
        }
    }

    function cerrarModalHistorial() {
        document.getElementById('modalHistorialBackdrop').classList.remove('is-open');
    }

    function abrirMovimientoDesdeHistorial() {
        const id = selectedItemId;
        cerrarModalHistorial();
        if (id) {
            abrirModalMovimiento(id);
        }
    }

    async function toggleInventarioStatus(id) {
        try {
            const resp = await fetch(`{{ url('apicola/inventario') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await resp.json();
            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'Error al cambiar estado.');
            }

            const item = inventarioData.find(i => i.id === id);
            if (item) {
                item.estado = data.data.estado;
            }

            showToast(data.message || 'Estado actualizado con éxito.');
            renderInventarioTable();
            actualizarMetricas();
        } catch (err) {
            console.error(err);
            alert(err.message || 'Error al cambiar estado del elemento.');
        }
    }

    async function desactivarInventarioItem(id, codigo, nombre) {
        if (!confirm(`¿Estás seguro de que deseas desactivar "${codigo} - ${nombre}" del inventario?`)) {
            return;
        }

        try {
            const resp = await fetch(`{{ url('apicola/inventario') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await resp.json();
            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'Error al desactivar el elemento.');
            }

            const item = inventarioData.find(i => i.id === id);
            if (item) {
                item.estado = 'Inactivo';
            }
            showToast(data.message || `Elemento ${codigo} desactivado con éxito.`);
            renderInventarioTable();
            actualizarMetricas();
        } catch (err) {
            console.error(err);
            alert(err.message || 'Error al desactivar elemento del inventario.');
        }
    }

    // Cerrar modal al hacer clic en el backdrop
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('ap-modal-backdrop')) {
            e.target.classList.remove('is-open');
        }
    });
</script>
@endsection
