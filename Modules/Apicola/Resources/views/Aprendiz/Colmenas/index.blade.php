@extends('apicola::Aprendiz.layout')

@section('title', 'Gestión de Colmenas • SENA APÍCOLA')

@section('breadcrumb')
    <span class="active-page">Gestión de colmenas</span>
@endsection

@section('styles')
<style>
    /* Estilos Premium del Módulo de Colmenas del Aprendiz */
    .ap-view-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .ap-view-subtitle-tag {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #059669;
        margin-bottom: 0.2rem;
    }

    .ap-view-main-title {
        font-size: 1.85rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        color: #0f172a;
        margin: 0;
    }

    .ap-user-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 6px 14px;
        border-radius: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .ap-user-chip-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        color: #ffffff;
        font-weight: 800;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ap-user-chip-name {
        font-size: 0.86rem;
        font-weight: 700;
        color: #0f172a;
        display: block;
    }

    .ap-user-chip-role {
        font-size: 0.72rem;
        color: #64748b;
        display: block;
    }

    /* Grid de Métricas (Borde lateral exacto a la captura) */
    .ap-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .ap-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .ap-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -3px rgba(15, 23, 42, 0.07);
    }

    .ap-metric-card.border-green { border-left: 4.5px solid #10b981; }
    .ap-metric-card.border-amber { border-left: 4.5px solid #f59e0b; }
    .ap-metric-card.border-red   { border-left: 4.5px solid #ef4444; }

    .ap-metric-card-title {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.45rem;
    }

    .ap-metric-card-num {
        font-size: 2.25rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 0.4rem;
    }

    .ap-metric-card-sub {
        font-size: 0.78rem;
        color: #94a3b8;
    }

    /* Sección de Listado */
    .ap-list-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    .ap-list-top-bar {
        margin-bottom: 1.25rem;
    }

    .ap-list-title {
        font-size: 1.25rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }

    .ap-list-desc {
        font-size: 0.84rem;
        color: #64748b;
        margin: 0;
    }

    /* Barra de Controles */
    .ap-controls-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 1.25rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }

    .ap-controls-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-grow: 1;
        flex-wrap: wrap;
    }

    .ap-search-wrapper {
        position: relative;
        min-width: 260px;
        flex-grow: 1;
    }

    .ap-search-wrapper i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
    }

    .ap-search-wrapper input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        font-size: 0.85rem;
        font-family: inherit;
        background: #ffffff;
        outline: none;
        transition: all 0.2s;
    }

    .ap-search-wrapper input:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
    }

    .ap-filter-select {
        padding: 9px 14px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        font-size: 0.85rem;
        font-family: inherit;
        background: #ffffff;
        color: #334155;
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .ap-filter-select:focus {
        border-color: #059669;
    }

    .ap-btn-solid-green {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #047857;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 6px rgba(4, 120, 87, 0.25);
    }

    .ap-btn-solid-green:hover {
        background: #065f46;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(4, 120, 87, 0.35);
    }

    /* Tabla */
    .ap-table-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .ap-table-scroll {
        overflow-x: auto;
    }

    .ap-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.86rem;
    }

    .ap-table thead tr {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .ap-table th {
        padding: 12px 16px;
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .ap-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s;
    }

    .ap-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .ap-table td {
        padding: 14px 16px;
        color: #334155;
        vertical-align: middle;
    }

    .ap-code-badge {
        font-weight: 800;
        color: #0f172a;
        font-family: inherit;
    }

    /* Badges de Estado */
    .ap-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .ap-status-badge.activa {
        background: #ecfdf5;
        color: #059669;
    }

    .ap-status-badge.en-revision {
        background: #fffbeb;
        color: #d97706;
    }

    .ap-status-badge.inactiva {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Botones de Acción en Tabla */
    .ap-actions-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ap-action-btn {
        background: none;
        border: none;
        padding: 0;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: color 0.15s;
    }

    .ap-action-btn.view { color: #059669; }
    .ap-action-btn.view:hover { color: #047857; text-decoration: underline; }

    .ap-action-btn.edit { color: #475569; }
    .ap-action-btn.edit:hover { color: #0f172a; text-decoration: underline; }

    .ap-action-btn.inspeccion { color: #0284c7; }
    .ap-action-btn.inspeccion:hover { color: #0369a1; text-decoration: underline; }

    .ap-action-btn.deactivate { color: #dc2626; }
    .ap-action-btn.deactivate:hover { color: #b91c1c; text-decoration: underline; }

    .ap-action-btn.activate { color: #059669; }
    .ap-action-btn.activate:hover { color: #047857; text-decoration: underline; }

    .ap-table-footer-row {
        padding: 12px 16px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.78rem;
        color: #64748b;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    /* Modal Backdrop & Window (Exacto a la captura) */
    .ap-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .ap-modal-backdrop.is-open {
        display: flex;
    }

    .ap-modal-window {
        background: #ffffff;
        border-radius: 18px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        animation: modalFadeIn 0.2s ease-out;
        overflow: hidden;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to   { opacity: 1; transform: scale(1); }
    }

    .ap-modal-body {
        padding: 2.2rem 2.2rem 1.75rem;
    }

    .ap-modal-title {
        font-size: 1.6rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 4px 0;
        letter-spacing: -0.02em;
    }

    .ap-modal-sub {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0 0 1.5rem 0;
    }

    .ap-form-field {
        margin-bottom: 1.25rem;
    }

    .ap-field-label {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .ap-field-input {
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        font-size: 0.90rem;
        font-family: inherit;
        background: #ffffff;
        color: #0f172a;
        outline: none;
        transition: all 0.2s;
    }

    .ap-field-input:focus {
        border-color: #047857;
        box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.12);
    }

    .ap-field-hint {
        color: #94a3b8;
        font-size: 0.76rem;
        margin-top: 5px;
        margin-bottom: 0;
    }

    .ap-grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 1.25rem;
    }

    .ap-modal-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin-top: 1.75rem;
    }

    .ap-btn-modal-cancel {
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
    }

    .ap-btn-modal-cancel:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .ap-btn-modal-save {
        background: #047857;
        color: #ffffff;
        border: none;
        padding: 10px 22px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
        box-shadow: 0 2px 6px rgba(4, 120, 87, 0.25);
    }

    .ap-btn-modal-save:hover {
        background: #065f46;
    }

    /* Toast Notification */
    .ap-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #071e18;
        color: #ffffff;
        border-left: 4px solid #10b981;
        padding: 14px 20px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
        z-index: 9999;
        display: none;
        animation: toastSlideIn 0.25s ease-out;
    }

    @keyframes toastSlideIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 992px) {
        .ap-metrics-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')
    <!-- Encabezado de la vista con perfil aprendiz -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">COLMENAS</span>
            <h1 class="ap-view-main-title">Gestión de colmenas</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #f59e0b;">
                {{ strtoupper(substr(Auth::user()?->nickname ?? 'AP', 0, 2)) }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Jhon Ramírez Ortiz') }}</span>
                <span class="ap-user-chip-role">Rol: Aprendiz Apícola</span>
            </div>
        </div>
    </div>

    <!-- 4 Tarjetas de Métricas -->
    <div class="ap-metrics-grid">
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">COLMENAS REGISTRADAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricTotal">{{ $metrics['total'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">En apiarios activos</div>
        </div>

        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">ACTIVAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricActivas">{{ $metrics['activas'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">En producción</div>
        </div>

        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">EN REVISIÓN</div>
            <div class="ap-metric-card-num" id="colmenaMetricRevision">{{ $metrics['en_revision'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Cría abierta / seguimiento</div>
        </div>

        <div class="ap-metric-card border-red">
            <div class="ap-metric-card-title">INACTIVAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricInactivas">{{ $metrics['inactivas'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">No admiten visitas</div>
        </div>
    </div>

    <!-- Sección: Listado de Colmenas -->
    <div class="ap-list-section">
        <div class="ap-list-top-bar">
            <h2 class="ap-list-title">Listado de colmenas</h2>
            <p class="ap-list-desc">Toda colmena pertenece a un apiario. Solo se registran visitas a colmenas activas o en revisión.</p>
        </div>

        <!-- Fila de Controles: Búsqueda, Filtros y Botón Nuevo -->
        <div class="ap-controls-bar">
            <div class="ap-controls-left" style="max-width: 680px;">
                <div class="ap-search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="colmenaSearchInput" placeholder="Buscar por código..." oninput="filterColmenas()">
                </div>
                <select id="colmenaApiarioFilter" class="ap-filter-select" onchange="filterColmenas()">
                    <option value="all">Apiario: todos</option>
                    @foreach($apiarios as $ap)
                        <option value="{{ $ap->nombre_apiario }}">{{ $ap->nombre_apiario }}</option>
                    @endforeach
                </select>
                <select id="colmenaEstadoFilter" class="ap-filter-select" onchange="filterColmenas()">
                    <option value="all">Estado: todos</option>
                    <option value="Activa">Activa</option>
                    <option value="En revisión">En revisión</option>
                    <option value="Inactiva">Inactiva</option>
                </select>
            </div>

            <div class="ap-controls-right">
                <button type="button" class="ap-btn-solid-green" onclick="openNewColmenaModal()">
                    <i class="fas fa-plus"></i> Nueva colmena
                </button>
            </div>
        </div>

        <!-- Tabla de Colmenas con columna TIPO DE ABEJA -->
        <div class="ap-table-card">
            <div class="ap-table-scroll">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>APIARIO</th>
                            <th>TIPO DE ABEJA</th>
                            <th>RESPONSABLE</th>
                            <th>INSTALACIÓN</th>
                            <th>ÚLTIMA VISITA</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="colmenasTableBody">
                        <!-- Generado dinámicamente con JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="ap-table-footer-row">
                <div>
                    Mostrando <strong id="colmenasShownCount">8</strong> de <strong id="colmenasTotalCount">8</strong> colmenas
                </div>
                <div>
                    Las colmenas no se eliminan: se desactivan para preservar la trazabilidad.
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="ap-toast" id="colmenaToast"></div>
@endsection

@section('modals')
    <!-- ==================== MODAL: NUEVA / EDITAR COLMENA (DISEÑO EXACTO A LA CAPTURA) ==================== -->
    <div class="ap-modal-backdrop" id="modalColmenaBackdrop">
        <div class="ap-modal-window">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.25rem;">
                    <div>
                        <h2 class="ap-modal-title" id="modalColmenaTitle" style="margin-bottom:0;">Nueva colmena</h2>
                        <p class="ap-modal-sub" style="margin-bottom:0;">Toda colmena pertenece a un apiario activo.</p>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeColmenaModal()" title="Cerrar">&times;</button>
                </div>

                <form id="formColmena" onsubmit="saveColmenaForm(event)">
                    <input type="hidden" id="modalColmenaId" value="">

                    <!-- Fila 1: CÓDIGO (izq) y FECHA DE INSTALACIÓN (der) -->
                    <div class="ap-grid-2col">
                        <div>
                            <label class="ap-field-label" for="modalColmenaCode">CÓDIGO</label>
                            <input type="text" id="modalColmenaCode" class="ap-field-input" value="" placeholder="Ej: COL-001" required>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalColmenaFecha">FECHA DE INSTALACIÓN</label>
                            <input type="date" id="modalColmenaFecha" class="ap-field-input" value="" required>
                        </div>
                    </div>

                    <!-- Fila 2: APIARIO (ancho completo) -->
                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalColmenaApiario">APIARIO</label>
                        <select id="modalColmenaApiario" class="ap-field-input" required>
                            @foreach($apiarios as $ap)
                                <option value="{{ $ap->nombre_apiario }}">{{ $ap->nombre_apiario }}</option>
                            @endforeach
                        </select>
                        <p class="ap-field-hint">Solo se listan apiarios activos.</p>
                    </div>

                    <!-- Fila 3: TIPO DE ABEJA (izq) y ESTADO (der) -->
                    <div class="ap-grid-2col">
                        <div>
                            <label class="ap-field-label" for="modalColmenaTipoAbeja">TIPO DE ABEJA</label>
                            <select id="modalColmenaTipoAbeja" class="ap-field-input" required onchange="handleTipoAbejaChange(this.value)">
                                @foreach($tiposAbeja as $tipo)
                                    <option value="{{ $tipo }}" {{ $loop->first ? 'selected' : '' }}>{{ $tipo }}</option>
                                @endforeach
                                <option value="__otra__" style="color: #047857; font-weight: 700;">➕ Otra / Registrar nueva...</option>
                            </select>

                            <!-- Contenedor para registrar una nueva especie de abeja -->
                            <div id="containerOtroTipoAbeja" style="display: none; margin-top: 8px; background: #f0fdf4; padding: 10px; border-radius: 10px; border: 1px solid #bbf7d0;">
                                <label style="display: block; font-size: 0.72rem; font-weight: 700; color: #166534; margin-bottom: 4px; text-transform: uppercase;">Nueva especie:</label>
                                <div style="display: flex; gap: 6px;">
                                    <input type="text" id="modalColmenaOtroTipoAbeja" class="ap-field-input" placeholder="Nombre de la nueva abeja..." style="flex: 1; padding: 7px 10px; border-radius: 8px; border: 1px solid #10b981; font-size: 0.85rem; color: #0f172a; background: #ffffff;">
                                    <button type="button" onclick="confirmarNuevoTipoAbeja()" style="background: #047857; color: #ffffff; border: none; padding: 7px 12px; border-radius: 8px; font-size: 0.80rem; font-weight: 700; cursor: pointer; white-space: nowrap;">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <span style="display: block; font-size: 0.70rem; color: #15803d; margin-top: 4px;">Quedará guardada para esta y futuras colmenas.</span>
                            </div>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalColmenaEstado">ESTADO</label>
                            <select id="modalColmenaEstado" class="ap-field-input" required>
                                <option value="Activa" selected>Activa</option>
                                <option value="En revisión">En revisión</option>
                                <option value="Inactiva">Inactiva</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 4: RESPONSABLE (ancho completo - INPUT TEXTO CON DATALIST) -->
                    <div class="ap-form-field" style="margin-bottom: 1.5rem;">
                        <label class="ap-field-label" for="modalColmenaResponsable">RESPONSABLE</label>
                        <input type="text" id="modalColmenaResponsable" list="responsablesSugeridosAprendiz" class="ap-field-input" placeholder="Escribe tu nombre o un usuario..." required>
                        <datalist id="responsablesSugeridosAprendiz">
                            @if(auth()->check())
                                @if(auth()->user()->person)
                                    <option value="{{ trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) }}"></option>
                                @endif
                                @if(auth()->user()->nickname)
                                    <option value="{{ auth()->user()->nickname }}"></option>
                                @endif
                            @endif
                        </datalist>
                        <p class="ap-field-hint">Escribe tu nombre o el usuario apicultor a cargo.</p>
                    </div>

                    <!-- Footer: Cancelar y Guardar colmena -->
                    <div class="ap-modal-footer">
                        <button type="button" class="ap-btn-modal-cancel" onclick="closeColmenaModal()">Cancelar</button>
                        <button type="submit" class="ap-btn-modal-save" id="btnSaveColmena">Guardar colmena</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: DETALLE DE COLMENA ==================== -->
    <div class="ap-modal-backdrop" id="modalColmenaDetailBackdrop">
        <div class="ap-modal-window" style="max-width: 480px;">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <span class="ap-view-subtitle-tag" style="font-size:0.68rem;">DETALLE DE COLMENA</span>
                        <h2 class="ap-modal-title" id="colmenaDetailCode" style="margin-bottom:0;">COL-001</h2>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeColmenaDetailModal()" title="Cerrar">&times;</button>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.2rem; margin-bottom:1.2rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">APIARIO</span>
                            <strong id="colmenaDetailApiario" style="font-size:0.88rem; color:#0f172a;">Apiario La Angostura</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">ESTADO SANITARIO</span>
                            <span id="colmenaDetailStatusBadge" class="ap-status-badge activa">Activa</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">TIPO DE ABEJA</span>
                            <span id="colmenaDetailTipoAbeja" style="font-size:0.86rem; color:#334155; font-weight: 600;">Apis mellifera</span>
                        </div>
                        <div>
                            <span class="ap-field-label">RESPONSABLE</span>
                            <span id="colmenaDetailResp" style="font-size:0.86rem; color:#334155;">Jhon Ramírez Ortiz</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <span class="ap-field-label">INSTALACIÓN</span>
                            <span id="colmenaDetailInstalacion" style="font-size:0.86rem; color:#64748b;">12/01/2026</span>
                        </div>
                        <div>
                            <span class="ap-field-label">ÚLTIMA VISITA</span>
                            <span id="colmenaDetailLastVisit" style="font-size:0.86rem; color:#059669; font-weight:700;">15/08/2026</span>
                        </div>
                    </div>
                </div>

                <p id="colmenaDetailNotas" style="font-size:0.84rem; color:#475569; line-height:1.5; margin-bottom:1.5rem;"></p>

                <div class="ap-modal-footer" style="justify-content:space-between;">
                    <button type="button" class="ap-btn-modal-cancel" onclick="closeColmenaDetailModal()">Cerrar</button>
                    <button type="button" class="ap-btn-modal-save" id="btnEditFromColmenaDetail">Editar datos</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // ==================== DATOS DE COLMENAS DESDE BD ====================
    @php
        $colmenasFormatted = $colmenas->map(function($c) {
            return [
                'id' => $c->id,
                'code' => $c->code,
                'apiario' => $c->apiario_name,
                'tipoAbeja' => $c->tipo_abeja ?? 'Apis mellifera',
                'responsable' => $c->responsable,
                'instalacion' => $c->installation_date ? \Carbon\Carbon::parse($c->installation_date)->format('d/m/Y') : '',
                'instalacionRaw' => $c->installation_date ? \Carbon\Carbon::parse($c->installation_date)->format('Y-m-d') : '',
                'ultimaVisita' => $c->last_visit_date ? \Carbon\Carbon::parse($c->last_visit_date)->format('d/m/Y') : ($c->installation_date ? \Carbon\Carbon::parse($c->installation_date)->format('d/m/Y') : '-'),
                'ultimaVisitaRaw' => $c->last_visit_date ? \Carbon\Carbon::parse($c->last_visit_date)->format('Y-m-d') : '',
                'estado' => $c->status,
                'notas' => $c->notes ?? 'Sin observaciones.',
            ];
        });
    @endphp

    let colmenasList = @json($colmenasFormatted);

    document.addEventListener('DOMContentLoaded', () => {
        renderColmenasTable();
        updateColmenasMetrics();
    });

    function showToast(message) {
        const toast = document.getElementById('apToast');
        const toastText = document.getElementById('apToastText');
        if (toast && toastText) {
            toastText.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
            return;
        }
        let fb = document.getElementById('apFallbackToast');
        if (!fb) {
            fb = document.createElement('div');
            fb.id = 'apFallbackToast';
            fb.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#064e3b;color:#ffffff;padding:12px 20px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:99999;font-weight:600;font-size:0.9rem;display:flex;align-items:center;gap:10px;transition:all 0.3s ease;';
            document.body.appendChild(fb);
        }
        fb.innerHTML = '<i class="fas fa-check-circle" style="color:#34d399;"></i> ' + message;
        fb.style.opacity = '1';
        fb.style.transform = 'translateY(0)';
        setTimeout(() => {
            fb.style.opacity = '0';
            fb.style.transform = 'translateY(10px)';
        }, 3500);
    }

    function updateColmenasMetrics() {
        const total = colmenasList.length;
        const activas = colmenasList.filter(c => c.estado === 'Activa').length;
        const revision = colmenasList.filter(c => c.estado === 'En revisión').length;
        const inactivas = colmenasList.filter(c => c.estado === 'Inactiva').length;

        document.getElementById('colmenaMetricTotal').textContent = total;
        document.getElementById('colmenaMetricActivas').textContent = activas;
        document.getElementById('colmenaMetricRevision').textContent = revision;
        document.getElementById('colmenaMetricInactivas').textContent = inactivas;

        document.getElementById('colmenasTotalCount').textContent = total;
    }

    function renderColmenasTable() {
        const searchVal = (document.getElementById('colmenaSearchInput')?.value || '').toLowerCase().trim();
        const apiarioVal = document.getElementById('colmenaApiarioFilter')?.value || 'all';
        const estadoVal = document.getElementById('colmenaEstadoFilter')?.value || 'all';

        const filtered = colmenasList.filter(item => {
            const matchSearch = item.code.toLowerCase().includes(searchVal) ||
                                (item.tipoAbeja && item.tipoAbeja.toLowerCase().includes(searchVal)) ||
                                item.responsable.toLowerCase().includes(searchVal);
            const matchApiario = (apiarioVal === 'all') || (item.apiario.includes(apiarioVal) || apiarioVal.includes(item.apiario));
            const matchEstado = (estadoVal === 'all') || (item.estado === estadoVal);
            return matchSearch && matchApiario && matchEstado;
        });

        const tbody = document.getElementById('colmenasTableBody');
        tbody.innerHTML = '';

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display:block;"></i>
                        No se encontraron colmenas con los criterios seleccionados.
                    </td>
                </tr>
            `;
            document.getElementById('colmenasShownCount').textContent = '0';
            return;
        }

        document.getElementById('colmenasShownCount').textContent = filtered.length;

        filtered.forEach(item => {
            let statusClass = 'activa';
            if (item.estado === 'En revisión') statusClass = 'en-revision';
            else if (item.estado === 'Inactiva') statusClass = 'inactiva';

            const isActiva = item.estado !== 'Inactiva';
            const toggleText = isActiva ? 'Desactivar' : 'Activar';
            const toggleClass = isActiva ? 'deactivate' : 'activate';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <span class="ap-code-badge">${item.code}</span>
                </td>
                <td style="color: #334155;">${item.apiario}</td>
                <td style="color: #1e293b; font-weight: 500;">${item.tipoAbeja || 'Apis mellifera'}</td>
                <td style="color: #475569;">${item.responsable}</td>
                <td style="color: #64748b; font-size: 0.82rem;">${item.instalacion}</td>
                <td style="color: #64748b; font-size: 0.82rem;">${item.ultimaVisita}</td>
                <td>
                    <span class="ap-status-badge ${statusClass}">
                        ${item.estado}
                    </span>
                </td>
                <td>
                    <div class="ap-actions-group">
                        <button type="button" class="ap-action-btn view" onclick="openColmenaDetailModal(${item.id})">Ver detalle</button>
                        <button type="button" class="ap-action-btn edit" onclick="openEditColmenaModal(${item.id})">Editar</button>
                        <a href="{{ url('apicola/aprendiz/colmenas') }}/${item.code}/inspecciones" class="ap-action-btn inspeccion" style="text-decoration: none;">Inspección</a>
                        ${item.estado === 'Inactiva'
                            ? `<button type="button" class="ap-action-btn activate" style="color: #059669; font-weight: 600;" onclick="toggleColmenaStatus(${item.id})">Activar</button>`
                            : `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444; font-weight: 600;" onclick="desactivarColmena(${item.id}, '${item.code}')">Desactivar</button>`
                        }
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterColmenas() {
        renderColmenasTable();
    }

    // ==================== GESTIÓN DE TIPO DE ABEJA ====================
    function handleTipoAbejaChange(val) {
        const container = document.getElementById('containerOtroTipoAbeja');
        const input = document.getElementById('modalColmenaOtroTipoAbeja');
        if (val === '__otra__') {
            container.style.display = 'block';
            input.focus();
        } else {
            container.style.display = 'none';
        }
    }

    function confirmarNuevoTipoAbeja() {
        const input = document.getElementById('modalColmenaOtroTipoAbeja');
        const select = document.getElementById('modalColmenaTipoAbeja');
        const container = document.getElementById('containerOtroTipoAbeja');
        const val = input.value.trim();
        if (!val) {
            showToast('Por favor escribe el nombre de la nueva especie.');
            input.focus();
            return;
        }

        let exists = false;
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value.toLowerCase() === val.toLowerCase()) {
                select.selectedIndex = i;
                exists = true;
                break;
            }
        }

        if (!exists) {
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            const otraOption = select.querySelector('option[value="__otra__"]');
            if (otraOption) {
                select.insertBefore(opt, otraOption);
            } else {
                select.appendChild(opt);
            }
            select.value = val;
        }

        container.style.display = 'none';
        input.value = '';
        showToast(`Especie "${val}" agregada.`);
    }

    function openNewColmenaModal() {
        document.getElementById('modalColmenaTitle').textContent = 'Nueva colmena';
        document.getElementById('modalColmenaId').value = '';
        
        const maxNum = colmenasList.reduce((max, c) => {
            const match = c.code.match(/COL-(\d+)/);
            if (match) {
                const num = parseInt(match[1], 10);
                return num > max ? num : max;
            }
            return max;
        }, 0);
        document.getElementById('modalColmenaCode').value = '';
        document.getElementById('modalColmenaFecha').value = '';

        const apiarioSelect = document.getElementById('modalColmenaApiario');
        if (apiarioSelect.options.length > 0) {
            apiarioSelect.selectedIndex = 0;
        }

        const tipoSelect = document.getElementById('modalColmenaTipoAbeja');
        tipoSelect.value = tipoSelect.options[0]?.value || 'Apis mellifera';
        document.getElementById('containerOtroTipoAbeja').style.display = 'none';
        document.getElementById('modalColmenaOtroTipoAbeja').value = '';

        document.getElementById('modalColmenaEstado').value = 'Activa';
        document.getElementById('modalColmenaResponsable').value = '';

        document.getElementById('modalColmenaBackdrop').classList.add('is-open');
    }

    function openEditColmenaModal(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;

        document.getElementById('modalColmenaTitle').textContent = 'Editar colmena';
        document.getElementById('modalColmenaId').value = item.id;
        document.getElementById('modalColmenaCode').value = item.code;

        // Apiario
        const apiarioSelect = document.getElementById('modalColmenaApiario');
        let apiarioFound = false;
        for (let i = 0; i < apiarioSelect.options.length; i++) {
            if (apiarioSelect.options[i].value === item.apiario) {
                apiarioSelect.selectedIndex = i;
                apiarioFound = true;
                break;
            }
        }
        if (!apiarioFound && item.apiario) {
            const opt = document.createElement('option');
            opt.value = item.apiario;
            opt.textContent = item.apiario;
            apiarioSelect.appendChild(opt);
            apiarioSelect.value = item.apiario;
        }

        // Tipo de abeja
        const tipoSelect = document.getElementById('modalColmenaTipoAbeja');
        const abejaVal = item.tipoAbeja || 'Apis mellifera';
        let tipoFound = false;
        for (let i = 0; i < tipoSelect.options.length; i++) {
            if (tipoSelect.options[i].value.toLowerCase() === abejaVal.toLowerCase()) {
                tipoSelect.selectedIndex = i;
                tipoFound = true;
                break;
            }
        }
        if (!tipoFound) {
            const opt = document.createElement('option');
            opt.value = abejaVal;
            opt.textContent = abejaVal;
            const otraOption = tipoSelect.querySelector('option[value="__otra__"]');
            if (otraOption) tipoSelect.insertBefore(opt, otraOption);
            else tipoSelect.appendChild(opt);
            tipoSelect.value = abejaVal;
        }
        document.getElementById('containerOtroTipoAbeja').style.display = 'none';
        document.getElementById('modalColmenaOtroTipoAbeja').value = '';

        document.getElementById('modalColmenaEstado').value = item.estado;
        document.getElementById('modalColmenaResponsable').value = item.responsable;

        let dateVal = item.instalacionRaw;
        if (!dateVal && item.instalacion && item.instalacion.includes('/')) {
            const parts = item.instalacion.split('/');
            dateVal = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }
        document.getElementById('modalColmenaFecha').value = dateVal || '';

        document.getElementById('modalColmenaBackdrop').classList.add('is-open');
    }

    function closeColmenaModal() {
        document.getElementById('modalColmenaBackdrop').classList.remove('is-open');
    }

    async function saveColmenaForm(e) {
        e.preventDefault();

        const id = document.getElementById('modalColmenaId').value;
        const code = document.getElementById('modalColmenaCode').value.trim();
        const apiario = document.getElementById('modalColmenaApiario').value;
        let tipoAbeja = document.getElementById('modalColmenaTipoAbeja').value;
        const rawFecha = document.getElementById('modalColmenaFecha').value;
        const responsable = document.getElementById('modalColmenaResponsable').value.trim();
        const estado = document.getElementById('modalColmenaEstado').value;

        if (tipoAbeja === '__otra__') {
            const otroInput = document.getElementById('modalColmenaOtroTipoAbeja');
            const customVal = otroInput.value.trim();
            if (!customVal) {
                showToast('Por favor escribe el nombre de la nueva especie de abeja.');
                otroInput.focus();
                return;
            }
            tipoAbeja = customVal;
        }

        if (!responsable) {
            showToast('Por favor escribe el nombre o usuario del responsable.');
            document.getElementById('modalColmenaResponsable').focus();
            return;
        }

        const btnSave = document.getElementById('btnSaveColmena');
        const prevBtnText = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        try {
            const url = id ? `{{ url('apicola/aprendiz/colmenas') }}/${id}` : `{{ route('apicola.aprendiz.colmenas.store') }}`;
            const method = id ? 'PUT' : 'POST';

            const payload = {
                code: code,
                apiario_name: apiario,
                tipo_abeja: tipoAbeja,
                responsable: responsable,
                installation_date: rawFecha,
                last_visit_date: rawFecha,
                status: estado,
                notes: id ? undefined : 'Colmena registrada por el aprendiz apícola.'
            };

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                let errorMsg = result.message || 'Error al guardar la colmena.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).flat().join(' | ');
                }
                throw new Error(errorMsg);
            }

            const savedItem = result.data;
            let instalacionFormatted = rawFecha;
            if (rawFecha && rawFecha.includes('-')) {
                const parts = rawFecha.split('-');
                instalacionFormatted = `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            const formattedRow = {
                id: savedItem.id,
                code: savedItem.code,
                apiario: savedItem.apiario_name,
                tipoAbeja: savedItem.tipo_abeja || tipoAbeja,
                responsable: savedItem.responsable,
                instalacion: instalacionFormatted,
                instalacionRaw: rawFecha,
                ultimaVisita: instalacionFormatted,
                ultimaVisitaRaw: rawFecha,
                estado: savedItem.status,
                notas: savedItem.notes || ''
            };

            if (id) {
                const idx = colmenasList.findIndex(c => c.id === parseInt(id));
                if (idx !== -1) {
                    colmenasList[idx] = { ...colmenasList[idx], ...formattedRow };
                }
                showToast(result.message || `Colmena "${code}" actualizada correctamente.`);
            } else {
                colmenasList.unshift(formattedRow);
                showToast(result.message || `Colmena "${code}" registrada con éxito.`);
            }

            // Asegurar que la especie esté en el select
            const select = document.getElementById('modalColmenaTipoAbeja');
            let exists = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value.toLowerCase() === tipoAbeja.toLowerCase()) {
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                const opt = document.createElement('option');
                opt.value = tipoAbeja;
                opt.textContent = tipoAbeja;
                const otraOption = select.querySelector('option[value="__otra__"]');
                if (otraOption) select.insertBefore(opt, otraOption);
                else select.appendChild(opt);
            }

            closeColmenaModal();
            renderColmenasTable();
            updateColmenasMetrics();
        } catch (err) {
            showToast(err.message || 'No se pudo guardar la colmena en la base de datos.');
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = prevBtnText;
        }
    }

    // ==================== DETALLE DE COLMENA ====================
    function openColmenaDetailModal(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;

        document.getElementById('colmenaDetailCode').textContent = item.code;
        document.getElementById('colmenaDetailApiario').textContent = item.apiario;
        document.getElementById('colmenaDetailTipoAbeja').textContent = item.tipoAbeja || 'Apis mellifera';
        document.getElementById('colmenaDetailResp').textContent = item.responsable;
        document.getElementById('colmenaDetailInstalacion').textContent = item.instalacion;
        document.getElementById('colmenaDetailLastVisit').textContent = item.ultimaVisita || 'Reciente';
        document.getElementById('colmenaDetailNotas').textContent = item.notas || 'Sin novedades clínicas registradas.';

        const badge = document.getElementById('colmenaDetailStatusBadge');
        badge.textContent = item.estado;
        badge.className = 'ap-status-badge ' + (item.estado === 'Activa' ? 'activa' : (item.estado === 'En revisión' ? 'en-revision' : 'inactiva'));

        document.getElementById('btnEditFromColmenaDetail').onclick = () => {
            closeColmenaDetailModal();
            openEditColmenaModal(item.id);
        };

        document.getElementById('modalColmenaDetailBackdrop').classList.add('is-open');
    }

    function closeColmenaDetailModal() {
        document.getElementById('modalColmenaDetailBackdrop').classList.remove('is-open');
    }

    function openInspeccionModal(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;
        showToast(`Ficha de inspección formativa para "${item.code}" abierta.`);
        openColmenaDetailModal(id);
    }

    async function toggleColmenaStatus(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;

        try {
            const response = await fetch(`{{ url('apicola/aprendiz/colmenas') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No se pudo cambiar el estado.');
            }

            item.estado = result.data.status;
            showToast(result.message || `Colmena "${item.code}" actualizada.`);
            renderColmenasTable();
            updateColmenasMetrics();
        } catch (err) {
            showToast(err.message || 'Error al cambiar estado en el servidor.');
        }
    }

    // ==================== DESACTIVAR COLMENA ====================
    async function desactivarColmena(id, code) {
        if (!confirm(`¿Estás seguro de que deseas desactivar la colmena "${code}"?`)) {
            return;
        }

        try {
            const response = await fetch(`{{ url('apicola/aprendiz/colmenas') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No se pudo desactivar la colmena.');
            }

            const colmena = colmenasList.find(c => c.id === id);
            if (colmena) {
                colmena.estado = 'Inactiva';
            }
            showToast(result.message || `Colmena "${code}" desactivada con éxito.`);
            renderColmenasTable();
            updateColmenasMetrics();
        } catch (err) {
            showToast(err.message || 'Error al desactivar la colmena en el servidor.');
        }
    }
</script>
@endsection
