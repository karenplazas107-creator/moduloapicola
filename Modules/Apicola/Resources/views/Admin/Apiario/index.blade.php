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
                <button type="button" class="ap-btn-outline-green" onclick="openGeneralMapModal()">
                    <i class="fas fa-map-location-dot"></i> Ver mapa general
                </button>
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
                            <th>ACCIONES</th>
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
                        <input type="text" id="modalApiarioNombre" class="ap-field-input" placeholder="Ej: Apiario La Angostura" required>
                    </div>

                    <!-- 2. Responsable -->
                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalApiarioResp">RESPONSABLE</label>
                        <input type="text" id="modalApiarioResp" list="responsablesSugeridosApiario" class="ap-field-input" placeholder="Escribe el nombre del responsable..." value="" required>
                        <datalist id="responsablesSugeridosApiario">
                            @if(auth()->check() && auth()->user()->person)
                                <option value="{{ trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) }}"></option>
                            @endif
                            @if(auth()->check() && auth()->user()->nickname)
                                <option value="{{ auth()->user()->nickname }}"></option>
                            @endif
                        </datalist>
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

                    <!-- 4. Colmenas Iniciales -->
                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalApiarioColmenas">COLMENAS INICIALES</label>
                        <input type="number" id="modalApiarioColmenas" class="ap-field-input" min="0" value="" placeholder="Ej: 0">
                    </div>

                    <!-- 5. Ubicación & Mapa -->
                    <div class="ap-form-field">
                        <label class="ap-field-label">UBICACIÓN GEOGRÁFICA</label>
                        <div id="modalMapContainer" style="width:100%; height:190px; border-radius:12px; border:1px solid #cbd5e1; margin-bottom:6px;"></div>
                        <p class="ap-field-hint">Haga clic en el mapa para marcar la ubicación exacta.</p>

                        <div class="ap-coords-row">
                            <div>
                                <label class="ap-field-label" for="modalApiarioLat">LATITUD</label>
                                <input type="text" id="modalApiarioLat" class="ap-field-input" value="" placeholder="Ej: 2.6857">
                            </div>
                            <div>
                                <label class="ap-field-label" for="modalApiarioLng">LONGITUD</label>
                                <input type="text" id="modalApiarioLng" class="ap-field-input" value="" placeholder="Ej: -75.3241">
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

    <!-- ==================== MODAL: MAPA GENERAL DE APIARIOS ==================== -->
    <div class="ap-modal-backdrop" id="modalGeneralMapBackdrop">
        <div class="ap-modal-window large">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem;">
                    <h2 class="ap-modal-title" style="margin-bottom:0;">
                        <i class="fas fa-map-marked-alt" style="color: #059669;"></i> Mapa general de apiarios
                    </h2>
                    <button type="button" class="ap-modal-close-btn" onclick="closeGeneralMapModal()" title="Cerrar">&times;</button>
                </div>
                <p style="font-size:0.84rem; color:#64748b; margin-bottom:1rem;">
                    Visualización georreferenciada de los apiarios en el Centro La Angostura y zonas aledañas de Campoalegre, Huila.
                </p>
                <div id="generalMapContainer" style="width:100%; height: 420px; border-radius: 14px; border: 1px solid #cbd5e1;"></div>
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
    let apiariosList = @json($apiariosFormatted ?? []);

    let modalMap = null;
    let modalMarker = null;
    let generalMap = null;
    let generalMarkersLayer = null;

    document.addEventListener('DOMContentLoaded', () => {
        renderApiariosTable();
        updateMetricCards();
    });

    function updateMetricCards() {
        const total = apiariosList.length;
        const activos = apiariosList.filter(a => a.estado === 'Activo').length;
        const inactivos = apiariosList.filter(a => a.estado === 'Inactivo').length;
        const colmenas = apiariosList.reduce((sum, a) => sum + (parseInt(a.colmenas) || 0), 0);

        document.getElementById('metricTotalApiarios').textContent = total;
        document.getElementById('metricActivos').textContent = activos;
        document.getElementById('metricInactivos').textContent = inactivos;
        document.getElementById('metricColmenas').textContent = colmenas;

        document.getElementById('apTotalCount').textContent = total;
    }

    function renderApiariosTable() {
        const searchVal = (document.getElementById('apSearchInput')?.value || '').toLowerCase().trim();
        const stateVal = document.getElementById('apStateFilter')?.value || 'all';

        const filtered = apiariosList.filter(item => {
            const matchSearch = item.nombre.toLowerCase().includes(searchVal) ||
                                item.responsable.toLowerCase().includes(searchVal);
            const matchState = (stateVal === 'all') || (item.estado === stateVal);
            return matchSearch && matchState;
        });

        const tbody = document.getElementById('apTableBody');
        tbody.innerHTML = '';

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display:block;"></i>
                        No se encontraron apiarios con los criterios seleccionados.
                    </td>
                </tr>
            `;
            document.getElementById('apShownCount').textContent = '0';
            return;
        }

        document.getElementById('apShownCount').textContent = filtered.length;

        filtered.forEach(item => {
            const isActivo = item.estado === 'Activo';
            const statusBadgeClass = isActivo ? 'activo' : 'inactivo';
            const toggleActionText = isActivo ? 'Desactivar' : 'Activar';
            const toggleActionClass = isActivo ? 'deactivate' : 'activate';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div style="font-weight: 700; color: #0f172a; display:flex; align-items:center; gap:8px;">
                        <span>${item.nombre}</span>
                        ${item.isSena ? '<span style="font-size:0.65rem; font-weight:800; background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; padding:2px 7px; border-radius:20px;">SENA LA ANGOSTURA</span>' : ''}
                    </div>
                </td>
                <td style="color: #334155;">${item.responsable}</td>
                <td>
                    <span style="font-family:monospace; color:#64748b; font-size:0.82rem;">${item.lat.toFixed(4)}, ${item.lng.toFixed(4)}</span>
                </td>
                <td>
                    <span style="font-weight: 700; color:#0f172a;">${item.colmenas}</span>
                </td>
                <td>
                    <span class="ap-status-badge ${statusBadgeClass}">
                        ${item.estado}
                    </span>
                </td>
                <td>
                    <div class="ap-actions-group">
                        <button type="button" class="ap-action-btn view" onclick="openDetailModal(${item.id})">Ver detalle</button>
                        <button type="button" class="ap-action-btn edit" onclick="openEditApiarioModal(${item.id})">Editar</button>
                        ${isActivo 
                            ? `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444; font-weight: 600;" onclick="desactivarApiario(${item.id}, '${item.nombre}')">Desactivar</button>`
                            : `<button type="button" class="ap-action-btn activate" style="color: #059669; font-weight: 600;" onclick="toggleApiarioStatus(${item.id})">Activar</button>`
                        }
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterApiarios() {
        renderApiariosTable();
    }

    function setApiarioEstado(estado) {
        document.getElementById('modalApiarioEstado').value = estado;
        const btnActivo = document.getElementById('toggleActivo');
        const btnInactivo = document.getElementById('toggleInactivo');

        if (estado === 'Activo') {
            btnActivo.className = 'ap-status-pill-btn selected-activa';
            btnInactivo.className = 'ap-status-pill-btn';
        } else {
            btnActivo.className = 'ap-status-pill-btn';
            btnInactivo.className = 'ap-status-pill-btn selected-inactiva';
        }
    }

    function openNewApiarioModal() {
        document.getElementById('modalApiarioTitle').textContent = 'Nuevo apiario';
        document.getElementById('modalApiarioId').value = '';
        document.getElementById('modalApiarioNombre').value = '';
        document.getElementById('modalApiarioResp').value = '';
        document.getElementById('modalApiarioColmenas').value = '';
        document.getElementById('modalApiarioLat').value = '';
        document.getElementById('modalApiarioLng').value = '';
        setApiarioEstado('Activo');

        document.getElementById('modalApiarioBackdrop').classList.add('is-open');

        setTimeout(() => {
            initModalMap(2.6857, -75.3241);
        }, 200);
    }

    function openEditApiarioModal(id) {
        const item = apiariosList.find(a => a.id === id);
        if (!item) return;

        document.getElementById('modalApiarioTitle').textContent = 'Editar apiario';
        document.getElementById('modalApiarioId').value = item.id;
        document.getElementById('modalApiarioNombre').value = item.nombre;
        document.getElementById('modalApiarioResp').value = item.responsable;
        document.getElementById('modalApiarioColmenas').value = item.colmenas;
        document.getElementById('modalApiarioLat').value = item.lat;
        document.getElementById('modalApiarioLng').value = item.lng;
        setApiarioEstado(item.estado);

        document.getElementById('modalApiarioBackdrop').classList.add('is-open');

        setTimeout(() => {
            initModalMap(item.lat, item.lng);
        }, 200);
    }

    function closeNewApiarioModal() {
        document.getElementById('modalApiarioBackdrop').classList.remove('is-open');
    }

    function initModalMap(lat, lng) {
        const container = document.getElementById('modalMapContainer');
        if (!container) return;

        if (!modalMap) {
            modalMap = L.map('modalMapContainer', {
                attributionControl: false
            }).setView([lat, lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(modalMap);

            modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);

            modalMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                document.getElementById('modalApiarioLat').value = pos.lat.toFixed(6);
                document.getElementById('modalApiarioLng').value = pos.lng.toFixed(6);
            });

            modalMap.on('click', function (e) {
                modalMarker.setLatLng(e.latlng);
                document.getElementById('modalApiarioLat').value = e.latlng.lat.toFixed(6);
                document.getElementById('modalApiarioLng').value = e.latlng.lng.toFixed(6);
            });
        } else {
            modalMap.invalidateSize();
            modalMap.setView([lat, lng], 14);
            modalMarker.setLatLng([lat, lng]);
        }
    }

    async function saveApiarioForm(e) {
        e.preventDefault();

        const id = document.getElementById('modalApiarioId').value;
        const nombre = document.getElementById('modalApiarioNombre').value.trim();
        const responsable = document.getElementById('modalApiarioResp').value.trim();
        const estado = document.getElementById('modalApiarioEstado').value;
        const colmenas = parseInt(document.getElementById('modalApiarioColmenas').value) || 0;
        const latVal = document.getElementById('modalApiarioLat').value.trim();
        const lngVal = document.getElementById('modalApiarioLng').value.trim();
        const lat = latVal !== '' ? parseFloat(latVal) : 2.6857;
        const lng = lngVal !== '' ? parseFloat(lngVal) : -75.3241;

        if (!nombre) {
            alert('Por favor ingrese el nombre del apiario.');
            return;
        }

        if (!responsable) {
            alert('Por favor ingrese el nombre del responsable.');
            document.getElementById('modalApiarioResp').focus();
            return;
        }

        const btnSave = document.getElementById('btnSaveApiario');
        const originalText = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando en base de datos...';

        try {
            const url = id ? `{{ url('apicola/apiarios') }}/${id}` : `{{ route('apicola.admin.apiarios.store') }}`;
            const method = id ? 'PUT' : 'POST';

            const payload = {
                nombre_apiario: nombre,
                responsable: responsable,
                latitud: lat,
                longitud: lng,
                estado: estado,
                colmenas: colmenas,
                _token: '{{ csrf_token() }}'
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
                const errorMsg = result.message || (result.errors ? Object.values(result.errors).flat().join('\n') : 'Error al guardar en la base de datos');
                throw new Error(errorMsg);
            }

            if (id) {
                const idx = apiariosList.findIndex(a => a.id === parseInt(id));
                if (idx !== -1) {
                    apiariosList[idx] = result.data;
                }
                showToast(result.message || `Apiario "${nombre}" actualizado en la base de datos.`);
            } else {
                apiariosList.unshift(result.data);
                showToast(result.message || `Nuevo apiario "${nombre}" guardado con éxito en la base de datos.`);
            }

            closeNewApiarioModal();
            renderApiariosTable();
            updateMetricCards();
        } catch (error) {
            console.error('Error al guardar apiario:', error);
            alert('Error al guardar en la base de datos: ' + error.message);
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        }
    }

    async function toggleApiarioStatus(id) {
        const apiario = apiariosList.find(a => a.id === id);
        if (!apiario) return;

        try {
            const response = await fetch(`{{ url('apicola/apiarios') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: '{{ csrf_token() }}' })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Error al cambiar estado.');
            }

            apiario.estado = result.nuevo_estado;
            showToast(result.message);
            renderApiariosTable();
            updateMetricCards();
        } catch (error) {
            console.error('Error al cambiar estado:', error);
            alert('Error al cambiar el estado: ' + error.message);
        }
    }

    async function desactivarApiario(id, nombre) {
        if (!confirm(`¿Estás seguro de que deseas desactivar el apiario "${nombre}"?`)) {
            return;
        }

        try {
            const response = await fetch(`{{ url('apicola/apiarios') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Error al desactivar el apiario.');
            }

            const item = apiariosList.find(a => a.id === id);
            if (item) {
                item.estado = 'Inactivo';
            }
            showToast(result.message || `Apiario "${nombre}" desactivado con éxito.`);
            renderApiariosTable();
            updateMetricCards();
        } catch (error) {
            console.error('Error al desactivar apiario:', error);
            alert('Error al desactivar el apiario: ' + error.message);
        }
    }

    function openGeneralMapModal() {
        document.getElementById('modalGeneralMapBackdrop').classList.add('is-open');

        setTimeout(() => {
            if (!generalMap) {
                generalMap = L.map('generalMapContainer', {
                    attributionControl: false
                }).setView([2.6857, -75.3241], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(generalMap);

                generalMarkersLayer = L.layerGroup().addTo(generalMap);
            } else {
                generalMap.invalidateSize();
            }

            generalMarkersLayer.clearLayers();

            apiariosList.forEach(a => {
                const isActivo = a.estado === 'Activo';
                const markerColor = isActivo ? '#059669' : '#ef4444';

                const popupContent = `
                    <div style="font-family:Inter,sans-serif; min-width:180px;">
                        <strong style="font-size:0.95rem; color:#0f172a; display:block; margin-bottom:4px;">${a.nombre}</strong>
                        <div style="font-size:0.8rem; color:#475569; margin-bottom:4px;"><i class="fas fa-user"></i> ${a.responsable}</div>
                        <div style="font-size:0.8rem; color:#475569; margin-bottom:6px;"><i class="fas fa-boxes-stacked"></i> <strong>${a.colmenas}</strong> colmenas</div>
                        <span style="display:inline-block; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px; background:${isActivo ? '#ecfdf5' : '#fee2e2'}; color:${isActivo ? '#059669' : '#dc2626'};">
                            ${a.estado}
                        </span>
                    </div>
                `;

                L.circleMarker([a.lat, a.lng], {
                    radius: 9,
                    fillColor: markerColor,
                    color: '#ffffff',
                    weight: 2.5,
                    opacity: 1,
                    fillOpacity: 0.95
                }).bindPopup(popupContent).addTo(generalMarkersLayer);
            });
        }, 200);
    }

    function closeGeneralMapModal() {
        document.getElementById('modalGeneralMapBackdrop').classList.remove('is-open');
    }

    function openDetailModal(id) {
        const apiario = apiariosList.find(a => a.id === id);
        if (!apiario) return;

        document.getElementById('detailModalNombre').textContent = apiario.nombre;
        document.getElementById('detailModalResp').textContent = apiario.responsable;
        document.getElementById('detailModalColmenas').textContent = apiario.colmenas + ' colmenas';
        document.getElementById('detailModalCoords').textContent = `${apiario.lat.toFixed(4)}, ${apiario.lng.toFixed(4)}`;
        document.getElementById('detailModalNotas').textContent = apiario.notas || 'Sin observaciones registradas.';

        const estadoBadge = document.getElementById('detailModalEstado');
        estadoBadge.textContent = apiario.estado;
        estadoBadge.className = `ap-status-badge ${apiario.estado === 'Activo' ? 'activo' : 'inactivo'}`;

        document.getElementById('btnEditFromDetail').onclick = () => {
            closeDetailModal();
            openEditApiarioModal(apiario.id);
        };

        document.getElementById('modalDetailBackdrop').classList.add('is-open');
    }

    function closeDetailModal() {
        document.getElementById('modalDetailBackdrop').classList.remove('is-open');
    }
</script>
@endsection
