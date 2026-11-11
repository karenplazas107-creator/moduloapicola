@extends('apicola::Admin.layout')

@section('title', 'Gestión de Apiarios • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.apiarios.index') }}">Apiarios</a>
    <span>/</span>
    <span class="active-page">Gestión de apiarios</span>
@endsection


@section('content')
    <!-- Encabezado de la vista con perfil apicultor -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">APIARIOS</span>
            <h1 class="ap-view-main-title">Gestión de apiarios</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #047857;">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->nickname ?? 'AP', 0, 2)) : 'AP' }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">{{ auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : (auth()->check() ? auth()->user()->nickname : 'Administrador Apícola') }}</span>
                <span class="ap-user-chip-role">Rol: {{ auth()->check() && auth()->user()->roles->first() ? auth()->user()->roles->first()->name : 'Apicultor' }}</span>
            </div>
        </div>
    </div>

    <!-- 4 Tarjetas de Métricas -->
    <div class="ap-metrics-grid">
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">APIARIOS REGISTRADOS</div>
            <div class="ap-metric-card-num" id="metricTotalApiarios">{{ $metrics['total'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Total en el sistema</div>
        </div>

        <div class="ap-metric-card border-teal">
            <div class="ap-metric-card-title">ACTIVOS</div>
            <div class="ap-metric-card-num" id="metricActivos">{{ $metrics['activos'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">En operación</div>
        </div>

        <div class="ap-metric-card border-red">
            <div class="ap-metric-card-title">INACTIVOS</div>
            <div class="ap-metric-card-num" id="metricInactivos">{{ $metrics['inactivos'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Fuera de operación</div>
        </div>

        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">COLMENAS ASOCIADAS</div>
            <div class="ap-metric-card-num" id="metricColmenas">{{ $metrics['colmenas'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Cálculo automático</div>
        </div>
    </div>

    <!-- Sección: Listado de Apiarios -->
    <div class="ap-list-section">
        <div class="ap-list-top-bar">
            <h2 class="ap-list-title">Listado de apiarios</h2>
            <p class="ap-list-desc">El número de colmenas se calcula a partir de las colmenas registradas.</p>
        </div>

        <!-- Fila de Controles: Búsqueda, Filtro y Botones -->
        <div class="ap-controls-bar">
            <div class="ap-controls-left">
                <div class="ap-search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="apSearchInput" placeholder="Buscar por nombre o responsable..." oninput="filterApiarios()">
                </div>
                <select id="apStateFilter" class="ap-filter-select" onchange="filterApiarios()">
                    <option value="all">Estado: todos</option>
                    <option value="Activo">Activos</option>
                    <option value="Inactivo">Inactivos</option>
                </select>
            </div>

            <div class="ap-controls-right">
                <button type="button" class="ap-btn-solid-green" onclick="openNewApiarioModal()">
                    <i class="fas fa-plus"></i> Nuevo apiario
                </button>
            </div>
        </div>

        <!-- Tabla de Apiarios -->
        <div class="ap-table-card">
            <div class="ap-table-scroll">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>
                            <th>RESPONSABLE</th>
                            <th>UBICACIÓN</th>
                            <th>COLMENAS</th>
                            <th>ESTADO</th>
                            <th style="text-align: center;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="apTableBody">
                        <!-- Filas inyectadas con JS -->
                    </tbody>
                </table>
            </div>

            <div class="ap-table-footer-row">
                <div>
                    Mostrando <strong id="apShownCount">1</strong> de <strong id="apTotalCount">1</strong> apiarios
                </div>
                <div>
                    Coordenadas WGS84 • Centro Agroindustrial La Angostura
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <!-- ==================== MODAL: NUEVO / EDITAR APIARIO ==================== -->
    <div class="ap-modal-backdrop" id="modalApiarioBackdrop">
        <div class="ap-modal-window">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <h2 class="ap-modal-title" id="modalApiarioTitle" style="margin-bottom:0;">Nuevo apiario</h2>
                    <button type="button" class="ap-modal-close-btn" onclick="closeNewApiarioModal()" title="Cerrar">&times;</button>
                </div>

                <form id="formApiario" onsubmit="saveApiarioForm(event)">
                    <input type="hidden" id="modalApiarioId" value="">

                    <!-- 1. Nombre -->
                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalApiarioNombre">NOMBRE DEL APIARIO</label>
                        <input type="text" id="modalApiarioNombre" class="ap-field-input" placeholder="Ej: Apiario La Angostura" oninput="validarTextoSinNumeros(this, 'errorNumeroNombre')" required>
                        <span id="errorNumeroNombre" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;">
                            <i class="fas fa-circle-exclamation"></i> Nombre inválido: no se permiten números, solo texto.
                        </span>
                    </div>

                    <!-- 2. Responsable -->
                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalApiarioRespSelect">RESPONSABLE</label>
                        <select id="modalApiarioRespSelect" class="ap-field-input" onchange="handleApiarioRespSelect(this.value)" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a; background: #ffffff;">
                            <option value="" disabled selected>Selecciona un responsable...</option>
                            <option value="SERGIO BARRERA">SERGIO BARRERA</option>
                            <option value="__otro__" style="color: #047857; font-weight: 700;">➕ Añadir a otro...</option>
                        </select>

                        <!-- Contenedor para añadir a otro responsable -->
                        <div id="containerOtroRespApiario" style="display: none; margin-top: 8px; background: #f0fdf4; padding: 10px; border-radius: 10px; border: 1px solid #bbf7d0;">
                            <label style="display: block; font-size: 0.72rem; font-weight: 700; color: #166534; margin-bottom: 4px; text-transform: uppercase;">Nombre del nuevo responsable:</label>
                            <div style="display: flex; gap: 6px;">
                                <input type="text" id="modalApiarioOtroRespInput" class="ap-field-input" placeholder="Escribe el nombre del nuevo responsable..." style="flex: 1; padding: 7px 10px; border-radius: 8px; border: 1px solid #10b981; font-size: 0.85rem; color: #0f172a; background: #ffffff;" oninput="validarTextoSinNumeros(this, 'errorNumeroResp')" onkeydown="if(event.key==='Enter'){event.preventDefault(); confirmarOtroResponsableApiario();}">
                                <button type="button" onclick="confirmarOtroResponsableApiario()" style="background: #047857; color: #ffffff; border: none; padding: 7px 12px; border-radius: 8px; font-size: 0.80rem; font-weight: 700; cursor: pointer; white-space: nowrap;">
                                    <i class="fas fa-plus"></i> Usar este
                                </button>
                            </div>
                            <span id="errorNumeroResp" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;">
                                <i class="fas fa-circle-exclamation"></i> Responsable inválido: no se permiten números, solo nombres reales de personas.
                            </span>
                            <span style="display: block; font-size: 0.70rem; color: #15803d; margin-top: 4px;">Debes ingresar solo nombres reales de personas (sin números).</span>
                        </div>

                        <!-- Input oculto para enviar al backend -->
                        <input type="hidden" id="modalApiarioResp" value="" required>

                    </div>

                    <!-- 3. Estado -->
                    <div class="ap-form-field">
                        <label class="ap-field-label">ESTADO</label>
                        <div class="ap-status-pill-group" style="grid-template-columns: 1fr 1fr;">
                            <button type="button" class="ap-status-pill-btn selected-activa" id="toggleActivo" onclick="setApiarioEstado('Activo')">Activo</button>
                            <button type="button" class="ap-status-pill-btn" id="toggleInactivo" onclick="setApiarioEstado('Inactivo')">Inactivo</button>
                        </div>
                        <input type="hidden" id="modalApiarioEstado" value="Activo">
                    </div>

                    <!-- 4. Colmenas Asociadas -->
                    <div class="ap-form-field">
                        <label class="ap-field-label" id="modalApiarioColmenasLabel" for="modalApiarioColmenas">COLMENAS ASOCIADAS</label>
                        <input type="number" id="modalApiarioColmenas" class="ap-field-input" min="0" value="0" readonly style="background-color: #f8fafc; cursor: not-allowed;" title="El conteo de colmenas se calcula automáticamente">
                        <p class="ap-field-hint" style="font-size:0.75rem; color:#64748b; margin-top:3px;">
                            <i class="fas fa-info-circle"></i> Las colmenas se asocian y gestionan desde el módulo de <strong>Colmenas</strong>.
                        </p>
                    </div>

                    <!-- 5. Ubicación Geográfica -->
                    <div class="ap-form-field">
                        <label class="ap-field-label">UBICACIÓN GEOGRÁFICA (COORDENADAS)</label>
                        <div class="ap-coords-row">
                            <div>
                                <label class="ap-field-label" for="modalApiarioLat">LATITUD</label>
                                <input type="text" id="modalApiarioLat" class="ap-field-input" value="" placeholder="Ej: 2.6857" oninput="validarCoordenada(this, 'errorLatCoords', -90, 90)" required>
                                <span id="errorLatCoords" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;">
                                    <i class="fas fa-circle-exclamation"></i> Coordenada inválida: no se permiten letras, solo números reales.
                                </span>
                            </div>
                            <div>
                                <label class="ap-field-label" for="modalApiarioLng">LONGITUD</label>
                                <input type="text" id="modalApiarioLng" class="ap-field-input" value="" placeholder="Ej: -75.3241" oninput="validarCoordenada(this, 'errorLngCoords', -180, 180)" required>
                                <span id="errorLngCoords" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;">
                                    <i class="fas fa-circle-exclamation"></i> Coordenada inválida: no se permiten letras, solo números reales.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="ap-modal-footer" style="padding: 1rem 0 0; border: none;">
                        <button type="button" class="ap-btn-modal-cancel" onclick="closeNewApiarioModal()">Cancelar</button>
                        <button type="submit" class="ap-btn-modal-save" id="btnSaveApiario">Guardar apiario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: DETALLE DE APIARIO ==================== -->
    <div class="ap-modal-backdrop" id="modalDetailBackdrop">
        <div class="ap-modal-window">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <span class="ap-view-subtitle-tag" id="detailModalBadge">DETALLE DEL APIARIO</span>
                        <h2 class="ap-modal-title" id="detailModalNombre" style="margin-bottom:0;">Apiario</h2>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeDetailModal()" title="Cerrar">&times;</button>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; margin-bottom:1.25rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">RESPONSABLE</span>
                            <strong id="detailModalResp" style="font-size:0.9rem; color:#0f172a;">-</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">ESTADO</span>
                            <span id="detailModalEstado" class="ap-status-badge activo">Activo</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <span class="ap-field-label">COLMENAS REGISTRADAS</span>
                            <strong id="detailModalColmenas" style="font-size:1.25rem; color:#059669;">-</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">COORDENADAS</span>
                            <span id="detailModalCoords" style="color:#64748b; font-family:monospace; font-size:0.82rem;">-</span>
                        </div>
                    </div>
                </div>

                <p id="detailModalNotas" style="font-size:0.85rem; color:#475569; line-height:1.5; margin-bottom:1rem;"></p>

                <div class="ap-modal-footer" style="padding:0; border:none;">
                    <button type="button" class="ap-btn-modal-cancel" onclick="closeDetailModal()">Cerrar</button>
                    <button type="button" class="ap-btn-modal-save" id="btnEditFromDetail">Editar información</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    window.ApiariosConfig = {
        apiariosList: @json($apiariosFormatted ?? []),
        responsablesSugeridos: @json($responsablesSugeridos ?? []),
        currentUserName: "{{ auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : (auth()->check() ? auth()->user()->nickname : '') }}",
        baseUrl: "{{ url('apicola/apiarios') }}",
        storeUrl: "{{ route('apicola.admin.apiarios.store') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/apiarios.js') }}?v={{ time() }}"></script>
@endsection
