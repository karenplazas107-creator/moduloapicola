@extends('apicola::Admin.layout')

@section('title', 'Gestión de Colmenas • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.colmenas.index') }}">Colmenas</a>
    <span>/</span>
    <span class="active-page">Gestión de colmenas</span>
@endsection

@section('content')
    <!-- Encabezado de la vista con perfil apicultor -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">COLMENAS</span>
            <h1 class="ap-view-main-title">Gestión de colmenas</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #047857;">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->nickname ?? 'AP', 0, 2)) : 'SB' }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">{{ auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : (auth()->check() ? auth()->user()->nickname : 'Sergio Barrera') }}</span>
                <span class="ap-user-chip-role">Rol: {{ auth()->check() && auth()->user()->roles->first() ? auth()->user()->roles->first()->name : 'Apicultor' }}</span>
            </div>
        </div>
    </div>

    <!-- 4 Tarjetas de Métricas (Exactas al diseño) -->
    <div class="ap-metrics-grid">
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">COLMENAS REGISTRADAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricTotal">{{ $metrics['total'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Apiario SENA La Angostura</div>
        </div>

        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">ACTIVAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricActivas">{{ $metrics['activas'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">En producción</div>
        </div>

        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">EN REVISIÓN</div>
            <div class="ap-metric-card-num" id="colmenaMetricRevision">{{ $metrics['en_revision'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Requieren seguimiento</div>
        </div>

        <div class="ap-metric-card border-red">
            <div class="ap-metric-card-title">INACTIVAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricInactivas">{{ $metrics['inactivas'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Fuera de operación</div>
        </div>
    </div>

    <!-- Sección: Listado de Colmenas -->
    <div class="ap-list-section">
        <div class="ap-list-top-bar">
            <h2 class="ap-list-title">Listado de colmenas</h2>
            <p class="ap-list-desc">Colmenas del Apiario SENA La Angostura.</p>
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

        <!-- Tabla de Colmenas -->
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
                    Mostrando <strong id="colmenasShownCount">0</strong> de <strong id="colmenasTotalCount">0</strong> colmenas
                </div>
                <div>
                    Gestión técnica de colmenas • Centro La Angostura
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <!-- ==================== MODAL: NUEVA / EDITAR COLMENA (DISEÑO EXACTO A LA CAPTURA) ==================== -->
    <div class="ap-modal-backdrop" id="modalColmenaBackdrop">
        <div class="ap-modal-window" style="max-width: 500px; border-radius: 18px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div class="ap-modal-body" style="padding: 2.2rem 2.2rem 1.75rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.25rem;">
                    <div>
                        <h2 class="ap-modal-title" id="modalColmenaTitle" style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0 0 4px 0; letter-spacing: -0.02em;">Nueva colmena</h2>
                        <p style="font-size: 0.88rem; color: #64748b; margin: 0;">Toda colmena pertenece a un apiario activo.</p>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeColmenaModal()" title="Cerrar">&times;</button>
                </div>

                <form id="formColmena" onsubmit="saveColmenaForm(event)">
                    <input type="hidden" id="modalColmenaId" value="">

                    <!-- Fila 1: CÓDIGO (izq) y FECHA DE INSTALACIÓN (der) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                        <div>
                            <label class="ap-field-label" for="modalColmenaCode" style="color: #64748b; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 6px; display: block;">CÓDIGO</label>
                            <input type="text" id="modalColmenaCode" class="ap-field-input" placeholder="Ej: COL-001" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a;">
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalColmenaFecha" style="color: #64748b; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 6px; display: block;">FECHA DE INSTALACIÓN</label>
                            <input type="date" id="modalColmenaFecha" class="ap-field-input" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a;">
                        </div>
                    </div>

                    <!-- Fila 2: APIARIO (ancho completo) -->
                    <div class="ap-form-field" style="margin-bottom: 1.25rem;">
                        <label class="ap-field-label" for="modalColmenaApiario" style="color: #64748b; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 6px; display: block;">APIARIO</label>
                        <select id="modalColmenaApiario" class="ap-field-input" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a; background: #ffffff;">
                            @foreach($apiarios as $ap)
                                <option value="{{ $ap->nombre_apiario }}">{{ $ap->nombre_apiario }}</option>
                            @endforeach
                        </select>
                        <p class="ap-field-hint" style="color: #94a3b8; font-size: 0.76rem; margin-top: 5px; margin-bottom: 0;">Solo se listan apiarios activos.</p>
                    </div>

                    <!-- Fila 3: TIPO DE ABEJA (izq) y ESTADO (der) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                        <div>
                            <label class="ap-field-label" for="modalColmenaTipoAbeja" style="color: #64748b; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 6px; display: block;">TIPO DE ABEJA</label>
                            <select id="modalColmenaTipoAbeja" class="ap-field-input" required onchange="handleTipoAbejaChange(this.value)" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a; background: #ffffff;">
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
                            <label class="ap-field-label" for="modalColmenaEstado" style="color: #64748b; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 6px; display: block;">ESTADO</label>
                            <select id="modalColmenaEstado" class="ap-field-input" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a; background: #ffffff;">
                                <option value="Activa" selected>Activa</option>
                                <option value="En revisión">En revisión</option>
                                <option value="Inactiva">Inactiva</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 4: RESPONSABLE (ancho completo - INPUT TEXTO CON DATALIST) -->
                    <div class="ap-form-field" style="margin-bottom: 1.5rem;">
                        <label class="ap-field-label" for="modalColmenaResponsable" style="color: #64748b; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 6px; display: block;">RESPONSABLE</label>
                        <input type="text" id="modalColmenaResponsable" list="responsablesSugeridosAdmin" class="ap-field-input" placeholder="Escribe tu nombre o un usuario..." required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a; background: #ffffff;">
                        <datalist id="responsablesSugeridosAdmin">
                            @if(auth()->check())
                                @if(auth()->user()->person)
                                    <option value="{{ trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) }}"></option>
                                @endif
                                @if(auth()->user()->nickname)
                                    <option value="{{ auth()->user()->nickname }}"></option>
                                @endif
                            @endif
                        </datalist>
                        <p class="ap-field-hint" style="color: #94a3b8; font-size: 0.76rem; margin-top: 5px; margin-bottom: 0;">Escribe tu nombre o usuario libremente.</p>
                    </div>

                    <!-- Botones footer (idénticos a la captura) -->
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-top: 1.75rem;">
                        <button type="button" class="ap-btn-modal-cancel" onclick="closeColmenaModal()" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 10px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Cancelar</button>
                        <button type="submit" class="ap-btn-modal-save" id="btnSaveColmena" style="background: #047857; color: #ffffff; border: none; padding: 10px 22px; border-radius: 10px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Guardar colmena</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: DETALLE DE COLMENA ==================== -->
    <div class="ap-modal-backdrop" id="modalColmenaDetailBackdrop">
        <div class="ap-modal-window">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <span class="ap-view-subtitle-tag" id="colmenaDetailBadge">DETALLE DE LA COLMENA</span>
                        <h2 class="ap-modal-title" id="colmenaDetailCode" style="margin-bottom:0;">COL-011</h2>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeColmenaDetailModal()" title="Cerrar">&times;</button>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; margin-bottom:1.25rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">APIARIO</span>
                            <strong id="colmenaDetailApiario" style="font-size:0.95rem; color:#0f172a;">-</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">ESTADO</span>
                            <span id="colmenaDetailStatusBadge" class="ap-status-badge activa">Activa</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">RESPONSABLE</span>
                            <span id="colmenaDetailResp" style="font-size:0.88rem; color:#334155; font-weight:600;">-</span>
                        </div>
                        <div>
                            <span class="ap-field-label">FECHA DE INSTALACIÓN</span>
                            <span id="colmenaDetailInstall" style="font-size:0.88rem; color:#334155;">-</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <span class="ap-field-label">ÚLTIMA VISITA</span>
                            <span id="colmenaDetailLastVisit" style="font-size:0.88rem; color:#059669; font-weight:700;">-</span>
                        </div>
                        <div>
                            <span class="ap-field-label">TIPO DE CAJA</span>
                            <span style="font-size:0.88rem; color:#334155;">Langstroth Estándar</span>
                        </div>
                    </div>
                </div>

                <p id="colmenaDetailNotas" style="font-size:0.85rem; color:#475569; line-height:1.5; margin-bottom:1.25rem;"></p>

                <div class="ap-modal-footer" style="padding:0; border:none; justify-content:space-between;">
                    <a href="#" id="linkIrInspeccionColmena" class="ap-btn-outline-green" style="font-size:0.84rem;">
                        <i class="fas fa-clipboard-check"></i> Ver producción e inspección
                    </a>
                    <div>
                        <button type="button" class="ap-btn-modal-cancel" onclick="closeColmenaDetailModal()">Cerrar</button>
                        <button type="button" class="ap-btn-modal-save" id="btnEditFromColmenaDetail">Editar colmena</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // ==================== ESTADO Y DATOS DE COLMENAS DESDE BD ====================
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

    function filterColmenas() {
        renderColmenasTable();
    }

    function renderColmenasTable() {
        const searchVal = (document.getElementById('colmenaSearchInput')?.value || '').toLowerCase().trim();
        const apiarioVal = document.getElementById('colmenaApiarioFilter')?.value || 'all';
        const estadoVal = document.getElementById('colmenaEstadoFilter')?.value || 'all';

        const filtered = colmenasList.filter(item => {
            const matchSearch = item.code.toLowerCase().includes(searchVal) ||
                                (item.responsable && item.responsable.toLowerCase().includes(searchVal)) ||
                                (item.apiario && item.apiario.toLowerCase().includes(searchVal)) ||
                                (item.tipoAbeja && item.tipoAbeja.toLowerCase().includes(searchVal));
            const matchApiario = (apiarioVal === 'all') || (item.apiario === apiarioVal);
            const matchEstado = (estadoVal === 'all') || (item.estado === estadoVal);

            return matchSearch && matchApiario && matchEstado;
        });

        const tbody = document.getElementById('colmenasTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        document.getElementById('colmenasShownCount').textContent = filtered.length;

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display:block;"></i>
                        No se encontraron colmenas con los filtros aplicados.
                    </td>
                </tr>
            `;
            return;
        }

        filtered.forEach(c => {
            const tr = document.createElement('tr');
            
            let badgeClass = 'activa';
            if (c.estado === 'En revisión') badgeClass = 'en-revision';
            if (c.estado === 'Inactiva') badgeClass = 'inactiva';

            const isInactive = c.estado === 'Inactiva';
            const toggleActionText = isInactive ? 'Activar' : 'Desactivar';
            const toggleActionClass = isInactive ? 'activate' : 'deactivate';

            const inspectionUrl = "{{ url('apicola/colmenas') }}/" + encodeURIComponent(c.code) + "/inspecciones";

            tr.innerHTML = `
                <td style="font-weight: 800; color: #0f172a;">${c.code}</td>
                <td style="color: #334155;">${c.apiario}</td>
                <td style="color: #1e293b; font-weight: 500;">${c.tipoAbeja || 'Apis mellifera'}</td>
                <td style="color: #334155;">${c.responsable}</td>
                <td style="color: #64748b;">${c.instalacion}</td>
                <td style="color: #64748b;">${c.ultimaVisita || '-'}</td>
                <td>
                    <span class="ap-status-badge ${badgeClass}">${c.estado}</span>
                </td>
                    <div class="ap-actions-group">
                        <button type="button" class="ap-action-btn view" onclick="openColmenaDetailModal(${c.id})">Ver detalle</button>
                        <button type="button" class="ap-action-btn edit" onclick="openEditColmenaModal(${c.id})">Editar</button>
                        <a href="${inspectionUrl}" class="ap-action-btn" style="color: #0284c7; font-weight:600;" title="Ir a inspección de ${c.code}">Inspección</a>
                        ${isInactive 
                            ? `<button type="button" class="ap-action-btn activate" style="color: #059669; font-weight: 600;" onclick="toggleColmenaStatus(${c.id})">Activar</button>` 
                            : `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444; font-weight: 600;" onclick="desactivarColmena(${c.id}, '${c.code}')">Desactivar</button>`
                        }
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
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

    // ==================== MODAL DE NUEVA / EDITAR COLMENA ====================
    function openNewColmenaModal() {
        document.getElementById('modalColmenaTitle').textContent = 'Nueva colmena';
        document.getElementById('modalColmenaId').value = '';
        document.getElementById('modalColmenaCode').value = '';
        document.getElementById('modalColmenaFecha').value = '';
        document.getElementById('modalColmenaResponsable').value = '';
        document.getElementById('modalColmenaEstado').value = 'Activa';

        const apiarioSelect = document.getElementById('modalColmenaApiario');
        if (apiarioSelect && apiarioSelect.options.length > 0) {
            apiarioSelect.selectedIndex = 0;
        }

        const tipoSelect = document.getElementById('modalColmenaTipoAbeja');
        if (tipoSelect && tipoSelect.options.length > 0) {
            tipoSelect.selectedIndex = 0;
        }
        document.getElementById('containerOtroTipoAbeja').style.display = 'none';
        document.getElementById('modalColmenaOtroTipoAbeja').value = '';

        document.getElementById('modalColmenaBackdrop').classList.add('is-open');
    }

    function openEditColmenaModal(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;

        document.getElementById('modalColmenaTitle').textContent = 'Editar colmena';
        document.getElementById('modalColmenaId').value = item.id;
        document.getElementById('modalColmenaCode').value = item.code;

        // Seleccionar apiario
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
            const url = id ? `{{ url('apicola/colmenas') }}/${id}` : `{{ route('apicola.admin.colmenas.store') }}`;
            const method = id ? 'PUT' : 'POST';

            const payload = {
                code: code,
                apiario_name: apiario,
                tipo_abeja: tipoAbeja,
                responsable: responsable,
                installation_date: rawFecha,
                last_visit_date: rawFecha,
                status: estado,
                notes: id ? undefined : 'Colmena registrada a través del panel administrativo.'
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
        document.getElementById('colmenaDetailResp').textContent = item.responsable;
        document.getElementById('colmenaDetailInstall').textContent = item.instalacion;
        document.getElementById('colmenaDetailLastVisit').textContent = item.ultimaVisita || 'Sin visita reciente';
        document.getElementById('colmenaDetailNotas').textContent = item.notas || 'Sin observaciones fitosanitarias registradas.';

        const badge = document.getElementById('colmenaDetailStatusBadge');
        badge.textContent = item.estado;
        badge.className = 'ap-status-badge ' + (item.estado === 'Activa' ? 'activa' : (item.estado === 'En revisión' ? 'en-revision' : 'inactiva'));

        document.getElementById('linkIrInspeccionColmena').href = "{{ url('apicola/colmenas') }}/" + encodeURIComponent(item.code) + "/inspecciones";

        document.getElementById('btnEditFromColmenaDetail').onclick = () => {
            closeColmenaDetailModal();
            openEditColmenaModal(item.id);
        };

        document.getElementById('modalColmenaDetailBackdrop').classList.add('is-open');
    }

    function closeColmenaDetailModal() {
        document.getElementById('modalColmenaDetailBackdrop').classList.remove('is-open');
    }

    // ==================== ALTERNAR ESTADO COLMENA ====================
    async function toggleColmenaStatus(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;

        try {
            const response = await fetch(`{{ url('apicola/colmenas') }}/${id}/toggle-status`, {
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
            showToast(result.message || `Estado de la colmena "${item.code}" actualizado.`);
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
            const response = await fetch(`{{ url('apicola/colmenas') }}/${id}`, {
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
