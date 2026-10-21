@extends('apicola::Admin.layout')

@section('title', 'Producción e Inspección • ' . ($selectedColmena ?: 'Colmenas') . ' • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.colmenas.index') }}">Colmenas</a>
    <span>/</span>
    <span style="font-weight: 700; color: #059669;" id="currentColmenaCode">{{ $selectedColmena ?: 'Sin colmena' }}</span>
    <span>/</span>
    <span class="active-page">Producción e inspección</span>
@endsection

@section('content')
    <!-- Encabezado de la vista con perfil apicultor -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">
                COLMENAS / <span id="colmenaTag">{{ $selectedColmena ?: 'GENERAL' }}</span> / PRODUCCIÓN E INSPECCIÓN
            </span>
            <h1 class="ap-view-main-title">Producción e inspección</h1>
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

    <!-- 3 Tarjetas de Métricas -->
    <div class="ap-metrics-grid cols-3">
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">VISITAS REGISTRADAS</div>
            <div class="ap-metric-card-num" id="metricTotalVisitas">0</div>
            <div class="ap-metric-card-sub" id="metricColmenaSub">{{ $selectedColmena ? 'Colmena ' . $selectedColmena : 'Colmenas registradas' }}</div>
        </div>

        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">MIEL ACUMULADA</div>
            <div class="ap-metric-card-num" id="metricMielTotal">0,0</div>
            <div class="ap-metric-card-sub">Kilogramos en el período</div>
        </div>

        <div class="ap-metric-card border-red">
            <div class="ap-metric-card-title">ALERTAS SANITARIAS</div>
            <div class="ap-metric-card-num" id="metricAlertasTotal">0</div>
            <div class="ap-metric-card-sub">Enfermedades detectadas</div>
        </div>
    </div>

    <!-- Sección: Historial de la Colmena -->
    <div class="ap-list-section">
        <div class="ap-list-top-bar" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:1rem;">
            <div>
                <h2 class="ap-list-title" id="historyColmenaTitle">{{ $selectedColmena ? 'Historial de la colmena ' . $selectedColmena : 'Historial de inspecciones' }}</h2>
                <p class="ap-list-desc">Una fila por cada visita realizada.</p>
            </div>

            <!-- Selector de colmena rápido -->
            <div style="display:flex; align-items:center; gap:8px;">
                <label class="ap-field-label" style="margin-bottom:0;" for="selectChangeColmena">Cambiar colmena:</label>
                <select id="selectChangeColmena" class="ap-filter-select" onchange="changeActiveColmena(this.value)">
                    @if(isset($colmenas) && count($colmenas) > 0)
                        @foreach($colmenas as $colm)
                            @php $cCode = $colm->code ?? $colm->codigo ?? $colm->nombre_colmena; @endphp
                            <option value="{{ $cCode }}" {{ $selectedColmena == $cCode ? 'selected' : '' }}>
                                {{ $cCode }} ({{ $colm->apiario_name ?? 'Apiario SENA' }})
                            </option>
                        @endforeach
                    @else
                        <option value="">Sin colmenas registradas</option>
                    @endif
                </select>
            </div>
        </div>

        <!-- Fila de Controles: Filtro de fechas y Botón + Registrar visita -->
        <div class="ap-controls-bar" style="margin-top: 1rem;">
            <div class="ap-controls-left">
                <input type="text" id="filterFechaInicio" class="ap-date-input" value="" placeholder="DD/MM/AAAA">
                <input type="text" id="filterFechaFin" class="ap-date-input" value="" placeholder="DD/MM/AAAA">
                <button type="button" class="ap-btn-outline-green" onclick="filterVisitas()">
                    Filtrar
                </button>
            </div>

            <div class="ap-controls-right">
                <button type="button" class="ap-btn-solid-green" onclick="openNewVisitaModal()">
                    <i class="fas fa-plus"></i> Registrar visita
                </button>
            </div>
        </div>

        <!-- Tabla de Visitas e Inspecciones -->
        <div class="ap-table-card">
            <div class="ap-table-scroll">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>N.° REGISTRO</th>
                            <th>ESTADO</th>
                            <th>PRESENCIA DE MIEL</th>
                            <th>MIEL (KG)</th>
                            <th>ENFERMEDADES</th>
                            <th>RESPONSABLE</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="visitasTableBody">
                        <!-- Renderizado dinámicamente con JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="ap-table-footer-row">
                <div id="visitasSummaryText">
                    0 visitas registradas
                </div>
                <div id="mielHarvestSummaryText" style="font-weight: 700; color: #0f172a;">
                    Total cosechado en el período: 0,0 kg
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <!-- ==================== MODAL: REGISTRAR / EDITAR VISITA ==================== -->
    <div class="ap-modal-backdrop" id="modalVisitaBackdrop">
        <div class="ap-modal-window" style="max-width: 580px;">
            <div class="ap-modal-body" style="padding: 2rem 2.2rem 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem;">
                    <h2 class="ap-modal-title" id="modalVisitaTitle" style="font-size: 1.65rem; font-weight: 900; color: #0f172a; margin-bottom: 0;">Registrar visita</h2>
                    <button type="button" class="ap-modal-close-btn" onclick="closeVisitaModal()" title="Cerrar">&times;</button>
                </div>

                <form id="formVisita" onsubmit="saveVisitaForm(event)">
                    <input type="hidden" id="modalVisitaId" value="">

                    <!-- Fila 1: Fecha, Colmena, Responsable -->
                    <div class="ap-coords-row cols-3" style="margin-bottom: 1.25rem; gap: 10px;">
                        <div>
                            <label class="ap-field-label" for="modalVisitaFecha">FECHA</label>
                            <input type="text" id="modalVisitaFecha" class="ap-field-input" placeholder="DD/MM/AAAA" value="" required>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalVisitaColmena">COLMENA</label>
                            <input type="text" id="modalVisitaColmena" class="ap-field-input" placeholder="Ej: COL-001" value="{{ $selectedColmena ?? '' }}" required>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalVisitaResponsable">RESPONSABLE</label>
                            <input type="text" id="modalVisitaResponsable" class="ap-field-input" placeholder="Escribe el nombre del responsable..." value="" required>
                        </div>
                    </div>

                    <!-- Sección: Estado de la Colmena -->
                    <div class="ap-form-section-title">ESTADO DE LA COLMENA</div>
                    
                    <div class="ap-form-field">
                        <label class="ap-field-label">ESTADO GENERAL</label>
                        <div class="ap-status-pill-group">
                            <button type="button" class="ap-status-pill-btn selected-bueno" id="btnEstGenBueno" onclick="setEstadoGeneral('Bueno')">Bueno</button>
                            <button type="button" class="ap-status-pill-btn" id="btnEstGenRegular" onclick="setEstadoGeneral('Regular')">Regular</button>
                            <button type="button" class="ap-status-pill-btn" id="btnEstGenMalo" onclick="setEstadoGeneral('Malo')">Malo</button>
                        </div>
                        <input type="hidden" id="modalVisitaEstadoGeneral" value="Bueno">
                    </div>

                    <div class="ap-form-field">
                        <label class="ap-field-label">PRESENCIA DE MIEL</label>
                        <div class="ap-status-pill-group">
                            <button type="button" class="ap-status-pill-btn selected-bueno" id="btnPresMielBueno" onclick="setPresenciaMiel('Bueno')">Bueno</button>
                            <button type="button" class="ap-status-pill-btn" id="btnPresMielRegular" onclick="setPresenciaMiel('Regular')">Regular</button>
                            <button type="button" class="ap-status-pill-btn" id="btnPresMielMalo" onclick="setPresenciaMiel('Malo')">Malo</button>
                        </div>
                        <input type="hidden" id="modalVisitaPresenciaMiel" value="Bueno">
                    </div>

                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalVisitaMielKg">MIEL COSECHADA (KG)</label>
                        <input type="number" step="0.1" min="0" id="modalVisitaMielKg" class="ap-field-input" placeholder="0.0" value="" required>
                        <p class="ap-field-hint">Deje 0 si en esta visita no se cosechó miel.</p>
                    </div>

                    <!-- Sección: Reina -->
                    <div class="ap-form-section-title">REINA</div>

                    <div class="ap-checkbox-card-group" style="margin-bottom: 1rem;">
                        <label class="ap-checkbox-card" id="lblSeVioReina">
                            <input type="checkbox" id="chkSeVioReina" onchange="toggleCheckboxCard(this, 'lblSeVioReina')">
                            <span>Se vio la reina</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblEstaMarcada">
                            <input type="checkbox" id="chkEstaMarcada" onchange="toggleCheckboxCard(this, 'lblEstaMarcada')">
                            <span>Está marcada</span>
                        </label>
                    </div>

                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalVisitaColorMarca">COLOR DE LA MARCA</label>
                        <select id="modalVisitaColorMarca" class="ap-field-input" onchange="handleColorMarcaChange(this.value)">
                            <option value="Sin marcar" selected>Sin marcar</option>
                            <option value="Blanco">Blanco (Años terminados en 1 o 6)</option>
                            <option value="Amarillo">Amarillo (Años terminados en 2 o 7)</option>
                            <option value="Rojo">Rojo (Años terminados en 3 o 8)</option>
                            <option value="Verde">Verde (Años terminados en 4 o 9)</option>
                            <option value="Azul">Azul (Años terminados en 5 o 0)</option>
                            <option value="Otro">Otro (personalizado)...</option>
                        </select>
                        <div id="wrapperColorMarcaOtro" style="display: none; margin-top: 0.6rem;">
                            <label class="ap-field-label" style="font-size: 0.78rem; color: #047857; margin-bottom: 4px;">ESPECIFICAR OTRO COLOR DE LA REINA</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="modalVisitaColorMarcaOtro" class="ap-field-input" placeholder="Ej: Naranja, Plateado, Rosado..." maxlength="50" onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarColorMarcaCustom();}">
                                <button type="button" class="ap-btn-add-mini" onclick="agregarColorMarcaCustom()" title="Agregar color">
                                    <i class="fas fa-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Enfermedades Detectadas -->
                    <div class="ap-form-section-title">ENFERMEDADES DETECTADAS</div>
                    <div class="ap-checkbox-card-group" id="containerEnfermedades" style="margin-bottom: 0.5rem;">
                        <label class="ap-checkbox-card" id="lblEnfVarroa">
                            <input type="checkbox" name="enfermedades" value="Varroa" onchange="toggleCheckboxCard(this, 'lblEnfVarroa')">
                            <span>Varroa</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblEnfLoqueAm">
                            <input type="checkbox" name="enfermedades" value="Loque americana" onchange="toggleCheckboxCard(this, 'lblEnfLoqueAm')">
                            <span>Loque americana</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblEnfLoqueEu">
                            <input type="checkbox" name="enfermedades" value="Loque europea" onchange="toggleCheckboxCard(this, 'lblEnfLoqueEu')">
                            <span>Loque europea</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblEnfAbejasNegras">
                            <input type="checkbox" name="enfermedades" value="Abejas negras" onchange="toggleCheckboxCard(this, 'lblEnfAbejasNegras')">
                            <span>Abejas negras</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblEnfEnvenenadas">
                            <input type="checkbox" name="enfermedades" value="Envenenadas" onchange="toggleCheckboxCard(this, 'lblEnfEnvenenadas')">
                            <span>Envenenadas</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblEnfHormiga">
                            <input type="checkbox" name="enfermedades" value="Hormiga" onchange="toggleCheckboxCard(this, 'lblEnfHormiga')">
                            <span>Hormiga</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblEnfOtra">
                            <input type="checkbox" id="chkEnfOtra" onchange="toggleEnfermedadOtra(this)">
                            <span>Otra</span>
                        </label>
                    </div>

                    <div id="wrapperEnfOtraInput" style="display: none; margin-top: 0.5rem; margin-bottom: 0.6rem;">
                        <label class="ap-field-label" style="font-size: 0.78rem; color: #047857; margin-bottom: 4px;">ESPECIFICAR OTRA ENFERMEDAD</label>
                        <input type="text" id="modalVisitaEnfOtraTexto" class="ap-field-input" placeholder="Nombre de la enfermedad detectada..." maxlength="100">
                    </div>

                    <div class="ap-add-item-row" style="display: flex; gap: 8px; margin-top: 0.5rem; margin-bottom: 0.4rem;">
                        <input type="text" id="txtNuevaEnfermedad" class="ap-field-input" placeholder="Agregar otra enfermedad..." style="padding: 7px 12px; font-size: 0.85rem;" onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarNuevaEnfermedad();}">
                        <button type="button" class="ap-btn-add-mini" onclick="agregarNuevaEnfermedad()">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                    <p class="ap-field-hint" style="margin-bottom: 1.25rem;">Marque solo si detectó algo. Puede dejarlo vacío o agregar otra enfermedad.</p>

                    <!-- Sección: Labores Realizadas -->
                    <div class="ap-form-section-title">LABORES REALIZADAS</div>
                    <div class="ap-checkbox-card-group" id="containerLabores" style="margin-bottom: 0.5rem;">
                        <label class="ap-checkbox-card" id="lblLabCera">
                            <input type="checkbox" name="labores" value="Aplicación de cera" onchange="toggleCheckboxCard(this, 'lblLabCera')">
                            <span>Aplicación de cera</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblLabAlimentacion">
                            <input type="checkbox" name="labores" value="Alimentación" onchange="toggleCheckboxCard(this, 'lblLabAlimentacion')">
                            <span>Alimentación</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblLabCuadros">
                            <input type="checkbox" name="labores" value="Cambio de cuadros" onchange="toggleCheckboxCard(this, 'lblLabCuadros')">
                            <span>Cambio de cuadros</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblLabTratamiento">
                            <input type="checkbox" name="labores" value="Tratamiento sanitario" onchange="toggleCheckboxCard(this, 'lblLabTratamiento')">
                            <span>Tratamiento sanitario</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblLabLimpieza">
                            <input type="checkbox" name="labores" value="Limpieza" onchange="toggleCheckboxCard(this, 'lblLabLimpieza')">
                            <span>Limpieza</span>
                        </label>
                        <label class="ap-checkbox-card" id="lblLabOtra">
                            <input type="checkbox" id="chkLabOtra" onchange="toggleLaborOtra(this)">
                            <span>Otra</span>
                        </label>
                    </div>

                    <div id="wrapperLabOtraInput" style="display: none; margin-top: 0.5rem; margin-bottom: 0.6rem;">
                        <label class="ap-field-label" style="font-size: 0.78rem; color: #047857; margin-bottom: 4px;">ESPECIFICAR OTRA LABOR</label>
                        <input type="text" id="modalVisitaLabOtraTexto" class="ap-field-input" placeholder="Nombre de la labor realizada..." maxlength="100">
                    </div>

                    <div class="ap-add-item-row" style="display: flex; gap: 8px; margin-top: 0.5rem; margin-bottom: 1rem;">
                        <input type="text" id="txtNuevaLabor" class="ap-field-input" placeholder="Agregar otra labor..." style="padding: 7px 12px; font-size: 0.85rem;" onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarNuevaLabor();}">
                        <button type="button" class="ap-btn-add-mini" onclick="agregarNuevaLabor()">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>

                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalVisitaAlimentacion">ALIMENTACIÓN SUMINISTRADA</label>
                        <input type="text" id="modalVisitaAlimentacion" class="ap-field-input" value="" placeholder="Jarabe, torta proteica, etc.">
                    </div>

                    <!-- Sección: Observaciones -->
                    <div class="ap-form-field" style="margin-top: 1.2rem;">
                        <label class="ap-field-label" for="modalVisitaObservaciones">OBSERVACIONES</label>
                        <textarea id="modalVisitaObservaciones" class="ap-field-input" rows="3" placeholder="Detalles de la inspección..."></textarea>
                    </div>

                    <!-- Footer -->
                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 1.5rem;">
                        <button type="button" class="ap-btn-modal-cancel" onclick="closeVisitaModal()">Cancelar</button>
                        <button type="submit" class="ap-btn-modal-save" id="btnSaveVisita">Guardar visita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: DETALLE DE VISITA ==================== -->
    <div class="ap-modal-backdrop" id="modalVisitaDetailBackdrop">
        <div class="ap-modal-window" style="max-width: 600px;">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="ap-view-subtitle-tag" id="detailVisitaRegNum">REG-0142</span>
                            <span id="detailVisitaEstadoBadge" class="ap-status-badge bueno" style="font-size:0.75rem; padding: 2px 9px;">Activa</span>
                        </div>
                        <h2 class="ap-modal-title" id="detailVisitaColmena" style="margin-bottom:0; margin-top:4px;">Inspección Colmena</h2>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeVisitaDetailModal()" title="Cerrar">&times;</button>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; margin-bottom:1.25rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">FECHA</span>
                            <strong id="detailVisitaFecha" style="font-size:0.9rem; color:#0f172a;">-</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">ESTADO GENERAL</span>
                            <span id="detailVisitaEstadoGen" class="ap-status-badge bueno">Bueno</span>
                        </div>
                        <div>
                            <span class="ap-field-label">PRESENCIA MIEL</span>
                            <span id="detailVisitaPresMiel" class="ap-status-badge bueno">Bueno</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">MIEL COSECHADA</span>
                            <strong id="detailVisitaMielKg" style="font-size:1.1rem; color:#059669;">0 kg</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">RESPONSABLE</span>
                            <span id="detailVisitaResp" style="font-size:0.85rem; color:#334155; font-weight:600;">-</span>
                        </div>
                        <div>
                            <span class="ap-field-label">ENFERMEDADES</span>
                            <span id="detailVisitaEnfermedades" class="ap-status-badge bueno">Ninguna</span>
                        </div>
                    </div>
                    <div style="border-top: 1px solid #e2e8f0; padding-top: 0.75rem; margin-top: 0.75rem;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                            <div>
                                <span class="ap-field-label">REINA</span>
                                <span id="detailVisitaReinaInfo" style="font-size:0.85rem; color:#334155;">-</span>
                            </div>
                            <div>
                                <span class="ap-field-label">LABORES REALIZADAS</span>
                                <span id="detailVisitaLabores" style="font-size:0.85rem; color:#334155;">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <span class="ap-field-label">OBSERVACIONES</span>
                    <p id="detailVisitaNotas" style="font-size:0.85rem; color:#475569; line-height:1.5; background: #f1f5f9; padding: 10px 14px; border-radius: 8px;"></p>
                </div>

                <div class="ap-modal-footer" style="padding:0; border:none;">
                    <button type="button" class="ap-btn-modal-cancel" onclick="closeVisitaDetailModal()">Cerrar</button>
                    <button type="button" class="ap-btn-modal-save" id="btnEditFromVisitaDetail">Editar visita</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // ==================== ESTADO Y DATOS DE INSPECCIÓN ====================
    let activeColmena = "{{ $selectedColmena ?? '' }}";

    // Lista de inspecciones sincronizada con la base de datos registro_inspeccion
    let inspeccionesList = @json($visitasInit ?? []);
    try {
        const storedVisitas = localStorage.getItem('apicola_inspecciones_list');
        if (storedVisitas) {
            const parsed = JSON.parse(storedVisitas);
            if (Array.isArray(parsed) && parsed.length > 0) {
                const existingRegs = new Set(inspeccionesList.map(v => v.regNumber));
                parsed.forEach(p => {
                    if (!existingRegs.has(p.regNumber)) {
                        inspeccionesList.push(p);
                    }
                });
            }
        }
    } catch(e) {
        console.error('Error al cargar visitas de localStorage:', e);
    }
    inspeccionesList.forEach(v => {
        if (!v.estado) v.estado = 'Activa';
    });

    document.addEventListener('DOMContentLoaded', () => {
        renderVisitasTable();
        updateInspeccionMetrics();
    });

    function changeActiveColmena(newCode) {
        activeColmena = newCode;
        const curEl = document.getElementById('currentColmenaCode');
        if (curEl) curEl.textContent = newCode || 'Sin colmena';
        const colmTag = document.getElementById('colmenaTag');
        if (colmTag) colmTag.textContent = newCode || 'GENERAL';
        const subEl = document.getElementById('metricColmenaSub');
        if (subEl) subEl.textContent = newCode ? `Colmena ${newCode}` : 'Colmenas registradas';
        const titleEl = document.getElementById('historyColmenaTitle');
        if (titleEl) titleEl.textContent = newCode ? `Historial de la colmena ${newCode}` : 'Historial de inspecciones';
        const modalColm = document.getElementById('modalVisitaColmena');
        if (modalColm) modalColm.value = newCode;
        
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    // Actualizar 3 tarjetas superiores
    function updateInspeccionMetrics() {
        const filtered = getFilteredVisitas();
        const activeVisitas = filtered.filter(v => v.estado !== 'Inactiva');
        const totalVisitas = filtered.length;
        const mielAcumulada = activeVisitas.reduce((sum, v) => sum + (parseFloat(v.mielKg) || 0), 0);
        const alertasSanitarias = activeVisitas.filter(v => v.enfermedades && v.enfermedades.length > 0).length;

        document.getElementById('metricTotalVisitas').textContent = totalVisitas;
        document.getElementById('metricMielTotal').textContent = mielAcumulada.toFixed(1).replace('.', ',');
        document.getElementById('metricAlertasTotal').textContent = alertasSanitarias;

        const fIni = document.getElementById('filterFechaInicio')?.value;
        const fFin = document.getElementById('filterFechaFin')?.value;
        if (fIni && fFin) {
            document.getElementById('visitasSummaryText').textContent = `${totalVisitas} visitas entre el ${fIni} y el ${fFin}`;
        } else {
            document.getElementById('visitasSummaryText').textContent = `${totalVisitas} visitas registradas`;
        }
        document.getElementById('mielHarvestSummaryText').textContent = `Total cosechado: ${mielAcumulada.toFixed(1).replace('.', ',')} kg`;
    }

    function parseDateDMY(dmyStr) {
        if (!dmyStr) return null;
        const parts = dmyStr.split('/');
        if (parts.length === 3) {
            return new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
        }
        return null;
    }

    function getFilteredVisitas() {
        const fInicio = parseDateDMY(document.getElementById('filterFechaInicio')?.value);
        const fFin = parseDateDMY(document.getElementById('filterFechaFin')?.value);

        return inspeccionesList.filter(item => {
            const vDate = parseDateDMY(item.fecha);
            if (!vDate) return true;
            if (fInicio && vDate < fInicio) return false;
            if (fFin && vDate > fFin) return false;
            return true;
        });
    }

    function filterVisitas() {
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    function renderVisitasTable() {
        const tbody = document.getElementById('visitasTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        const items = getFilteredVisitas();

        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display:block;"></i>
                        No se encontraron visitas registradas.
                    </td>
                </tr>
            `;
            return;
        }

        items.forEach(v => {
            const tr = document.createElement('tr');
            const isInactive = v.estado === 'Inactiva';
            if (isInactive) {
                tr.style.opacity = '0.65';
                tr.style.backgroundColor = '#f8fafc';
            }

            // Enfermedad badge
            let enfermedadesHtml = '<span style="color: #64748b;">Ninguna</span>';
            if (v.enfermedades && v.enfermedades.length > 0) {
                enfermedadesHtml = v.enfermedades.map(enf => `<span class="ap-status-badge alerta" style="font-size:0.75rem; padding: 2px 10px;">${enf}</span>`).join(' ');
            }

            tr.innerHTML = `
                <td style="font-weight: 700; color: #0f172a;">${v.fecha}</td>
                <td style="color: #475569; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.85rem;">
                    ${v.regNumber}
                    ${isInactive ? '<span class="ap-status-badge inactiva" style="margin-left: 6px; font-size: 0.68rem; padding: 2px 7px;">Inactiva</span>' : ''}
                </td>
                <td style="color: #334155;">${v.estadoGeneral}</td>
                <td style="color: #334155;">${v.presenciaMiel}</td>
                <td style="color: #0f172a; font-weight: 700;">${parseFloat(v.mielKg).toFixed(1)}</td>
                <td>${enfermedadesHtml}</td>
                <td style="color: #334155;">${v.responsable}</td>
                <td>
                    <div class="ap-actions-group">
                        <button type="button" class="ap-action-btn view" onclick="openVisitaDetailModal(${v.id})">Ver</button>
                        <button type="button" class="ap-action-btn edit" onclick="openEditVisitaModal(${v.id})">Editar</button>
                        ${isInactive
                            ? `<button type="button" class="ap-action-btn activate" style="color: #059669; font-weight: 600;" onclick="toggleVisitaStatus(${v.id}, '${v.regNumber}')">Activar</button>`
                            : `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444; font-weight: 600;" onclick="desactivarVisita(${v.id}, '${v.regNumber}')">Desactivar</button>`
                        }
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ==================== MODAL DE NUEVA / EDITAR VISITA ====================
    function setEstadoGeneral(val) {
        document.getElementById('modalVisitaEstadoGeneral').value = val;
        document.getElementById('btnEstGenBueno').className = 'ap-status-pill-btn' + (val === 'Bueno' ? ' selected-bueno' : '');
        document.getElementById('btnEstGenRegular').className = 'ap-status-pill-btn' + (val === 'Regular' ? ' selected-regular' : '');
        document.getElementById('btnEstGenMalo').className = 'ap-status-pill-btn' + (val === 'Malo' ? ' selected-malo' : '');
    }

    function setPresenciaMiel(val) {
        document.getElementById('modalVisitaPresenciaMiel').value = val;
        document.getElementById('btnPresMielBueno').className = 'ap-status-pill-btn' + (val === 'Bueno' ? ' selected-bueno' : '');
        document.getElementById('btnPresMielRegular').className = 'ap-status-pill-btn' + (val === 'Regular' ? ' selected-regular' : '');
        document.getElementById('btnPresMielMalo').className = 'ap-status-pill-btn' + (val === 'Malo' ? ' selected-malo' : '');
    }

    function toggleCheckboxCard(checkbox, labelId) {
        const lbl = document.getElementById(labelId);
        if (!lbl) return;
        if (checkbox.checked) {
            lbl.classList.add('is-checked');
        } else {
            lbl.classList.remove('is-checked');
        }
    }

    // ==================== MANEJO DE COLOR DE LA REINA ====================
    function handleColorMarcaChange(val) {
        const wrap = document.getElementById('wrapperColorMarcaOtro');
        const input = document.getElementById('modalVisitaColorMarcaOtro');
        if (!wrap) return;
        if (val === 'Otro') {
            wrap.style.display = 'block';
            if (input) input.focus();
        } else {
            wrap.style.display = 'none';
        }
    }

    function agregarColorMarcaCustom() {
        const input = document.getElementById('modalVisitaColorMarcaOtro');
        const select = document.getElementById('modalVisitaColorMarca');
        if (!input || !select) return;
        const val = input.value.trim();
        if (!val) {
            input.focus();
            return;
        }

        let found = false;
        for (let opt of select.options) {
            if (opt.value.toLowerCase() === val.toLowerCase()) {
                select.value = opt.value;
                found = true;
                break;
            }
        }

        if (!found) {
            const newOpt = document.createElement('option');
            newOpt.value = val;
            newOpt.textContent = val + ' (Personalizado)';
            const otroOpt = Array.from(select.options).find(o => o.value === 'Otro');
            if (otroOpt) {
                select.insertBefore(newOpt, otroOpt);
            } else {
                select.appendChild(newOpt);
            }
            select.value = val;
        }
        document.getElementById('wrapperColorMarcaOtro').style.display = 'none';
    }

    // ==================== MANEJO DE OTRAS ENFERMEDADES ====================
    function toggleEnfermedadOtra(cb) {
        const lbl = document.getElementById('lblEnfOtra');
        const wrap = document.getElementById('wrapperEnfOtraInput');
        const input = document.getElementById('modalVisitaEnfOtraTexto');
        if (cb.checked) {
            if (lbl) lbl.classList.add('is-checked');
            if (wrap) wrap.style.display = 'block';
            if (input) input.focus();
        } else {
            if (lbl) lbl.classList.remove('is-checked');
            if (wrap) wrap.style.display = 'none';
        }
    }

    function agregarNuevaEnfermedad(nombreForzado = null, isChecked = true) {
        const input = document.getElementById('txtNuevaEnfermedad');
        const valor = (nombreForzado !== null ? nombreForzado : (input ? input.value : '')).trim();
        if (!valor) {
            if (input) input.focus();
            return;
        }

        const container = document.getElementById('containerEnfermedades');
        if (!container) return;

        // Si ya existe la tarjeta con ese valor, la marcamos
        const existing = container.querySelector(`input[name="enfermedades"][value="${CSS.escape(valor)}"]`);
        if (existing) {
            existing.checked = true;
            const parentLbl = existing.closest('.ap-checkbox-card');
            if (parentLbl) parentLbl.classList.add('is-checked');
            if (input && nombreForzado === null) input.value = '';
            return;
        }

        const customId = 'lblEnfCust_' + Math.random().toString(36).substring(2, 9);
        const label = document.createElement('label');
        label.className = 'ap-checkbox-card is-custom-card' + (isChecked ? ' is-checked' : '');
        label.id = customId;

        label.innerHTML = `
            <input type="checkbox" name="enfermedades" value="${valor}" ${isChecked ? 'checked' : ''} onchange="toggleCheckboxCard(this, '${customId}')">
            <span style="flex: 1; word-break: break-word;">${valor}</span>
            <button type="button" class="ap-btn-remove-card" onclick="removerCardItem(event, '${customId}')" title="Eliminar">&times;</button>
        `;

        const otraLabel = document.getElementById('lblEnfOtra');
        if (otraLabel && otraLabel.parentNode === container) {
            container.insertBefore(label, otraLabel);
        } else {
            container.appendChild(label);
        }

        if (input && nombreForzado === null) {
            input.value = '';
        }
    }

    // ==================== MANEJO DE OTRAS LABORES ====================
    function toggleLaborOtra(cb) {
        const lbl = document.getElementById('lblLabOtra');
        const wrap = document.getElementById('wrapperLabOtraInput');
        const input = document.getElementById('modalVisitaLabOtraTexto');
        if (cb.checked) {
            if (lbl) lbl.classList.add('is-checked');
            if (wrap) wrap.style.display = 'block';
            if (input) input.focus();
        } else {
            if (lbl) lbl.classList.remove('is-checked');
            if (wrap) wrap.style.display = 'none';
        }
    }

    function agregarNuevaLabor(nombreForzado = null, isChecked = true) {
        const input = document.getElementById('txtNuevaLabor');
        const valor = (nombreForzado !== null ? nombreForzado : (input ? input.value : '')).trim();
        if (!valor) {
            if (input) input.focus();
            return;
        }

        const container = document.getElementById('containerLabores');
        if (!container) return;

        const existing = container.querySelector(`input[name="labores"][value="${CSS.escape(valor)}"]`);
        if (existing) {
            existing.checked = true;
            const parentLbl = existing.closest('.ap-checkbox-card');
            if (parentLbl) parentLbl.classList.add('is-checked');
            if (input && nombreForzado === null) input.value = '';
            return;
        }

        const customId = 'lblLabCust_' + Math.random().toString(36).substring(2, 9);
        const label = document.createElement('label');
        label.className = 'ap-checkbox-card is-custom-card' + (isChecked ? ' is-checked' : '');
        label.id = customId;

        label.innerHTML = `
            <input type="checkbox" name="labores" value="${valor}" ${isChecked ? 'checked' : ''} onchange="toggleCheckboxCard(this, '${customId}')">
            <span style="flex: 1; word-break: break-word;">${valor}</span>
            <button type="button" class="ap-btn-remove-card" onclick="removerCardItem(event, '${customId}')" title="Eliminar">&times;</button>
        `;

        const otraLabel = document.getElementById('lblLabOtra');
        if (otraLabel && otraLabel.parentNode === container) {
            container.insertBefore(label, otraLabel);
        } else {
            container.appendChild(label);
        }

        if (input && nombreForzado === null) {
            input.value = '';
        }
    }

    function removerCardItem(e, id) {
        e.stopPropagation();
        e.preventDefault();
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function openNewVisitaModal() {
        document.getElementById('modalVisitaTitle').textContent = 'Registrar visita';
        document.getElementById('modalVisitaId').value = '';
        
        // Todos los campos completamente en blanco
        document.getElementById('modalVisitaFecha').value = '';
        document.getElementById('modalVisitaColmena').value = activeColmena || '';
        document.getElementById('modalVisitaResponsable').value = '';
        document.getElementById('modalVisitaMielKg').value = '';
        document.getElementById('modalVisitaAlimentacion').value = '';
        document.getElementById('modalVisitaObservaciones').value = '';

        setEstadoGeneral('Bueno');
        setPresenciaMiel('Bueno');

        // Reina (desmarcada por defecto)
        const chkReina = document.getElementById('chkSeVioReina');
        chkReina.checked = false;
        toggleCheckboxCard(chkReina, 'lblSeVioReina');

        const chkMarcada = document.getElementById('chkEstaMarcada');
        chkMarcada.checked = false;
        toggleCheckboxCard(chkMarcada, 'lblEstaMarcada');

        // Color de la marca
        document.getElementById('modalVisitaColorMarca').value = 'Sin marcar';
        const wrapColor = document.getElementById('wrapperColorMarcaOtro');
        if (wrapColor) wrapColor.style.display = 'none';
        const inputColor = document.getElementById('modalVisitaColorMarcaOtro');
        if (inputColor) inputColor.value = '';

        // Limpiar cards personalizadas previas
        document.querySelectorAll('.is-custom-card').forEach(el => el.remove());

        // Enfermedades (todas desmarcadas)
        document.querySelectorAll('input[name="enfermedades"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });
        const chkEnfOtra = document.getElementById('chkEnfOtra');
        if (chkEnfOtra) {
            chkEnfOtra.checked = false;
            toggleEnfermedadOtra(chkEnfOtra);
        }
        const txtEnfOtra = document.getElementById('modalVisitaEnfOtraTexto');
        if (txtEnfOtra) txtEnfOtra.value = '';
        const txtNewEnf = document.getElementById('txtNuevaEnfermedad');
        if (txtNewEnf) txtNewEnf.value = '';

        // Labores (todas desmarcadas)
        document.querySelectorAll('input[name="labores"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });
        const chkLabOtra = document.getElementById('chkLabOtra');
        if (chkLabOtra) {
            chkLabOtra.checked = false;
            toggleLaborOtra(chkLabOtra);
        }
        const txtLabOtra = document.getElementById('modalVisitaLabOtraTexto');
        if (txtLabOtra) txtLabOtra.value = '';
        const txtNewLab = document.getElementById('txtNuevaLabor');
        if (txtNewLab) txtNewLab.value = '';

        document.getElementById('modalVisitaBackdrop').classList.add('is-open');
    }

    function openEditVisitaModal(id) {
        const item = inspeccionesList.find(v => v.id === id);
        if (!item) return;

        document.getElementById('modalVisitaTitle').textContent = 'Editar visita';
        document.getElementById('modalVisitaId').value = item.id;
        document.getElementById('modalVisitaFecha').value = item.fecha;
        document.getElementById('modalVisitaColmena').value = item.colmena;
        document.getElementById('modalVisitaResponsable').value = item.responsable;
        document.getElementById('modalVisitaMielKg').value = item.mielKg;
        document.getElementById('modalVisitaAlimentacion').value = item.alimenSuministrada || '';
        document.getElementById('modalVisitaObservaciones').value = item.notas || '';

        setEstadoGeneral(item.estadoGeneral);
        setPresenciaMiel(item.presenciaMiel);

        // Reina
        const chkReina = document.getElementById('chkSeVioReina');
        chkReina.checked = !!item.reinaVista;
        toggleCheckboxCard(chkReina, 'lblSeVioReina');

        const chkMarcada = document.getElementById('chkEstaMarcada');
        chkMarcada.checked = !!item.reinaMarcada;
        toggleCheckboxCard(chkMarcada, 'lblEstaMarcada');

        // Color de la marca
        const color = item.colorMarca || 'Sin marcar';
        const selColor = document.getElementById('modalVisitaColorMarca');
        const wrapColor = document.getElementById('wrapperColorMarcaOtro');
        const inputColor = document.getElementById('modalVisitaColorMarcaOtro');

        let colorFound = false;
        for (let opt of selColor.options) {
            if (opt.value.toLowerCase() === color.toLowerCase()) {
                selColor.value = opt.value;
                colorFound = true;
                break;
            }
        }
        if (!colorFound && color !== 'Sin marcar') {
            const newOpt = document.createElement('option');
            newOpt.value = color;
            newOpt.textContent = color + ' (Personalizado)';
            const otroOpt = Array.from(selColor.options).find(o => o.value === 'Otro');
            if (otroOpt) selColor.insertBefore(newOpt, otroOpt);
            else selColor.appendChild(newOpt);
            selColor.value = color;
        }
        if (wrapColor) {
            if (selColor.value === 'Otro') {
                wrapColor.style.display = 'block';
                if (inputColor) inputColor.value = color !== 'Otro' ? color : '';
            } else {
                wrapColor.style.display = 'none';
                if (inputColor) inputColor.value = '';
            }
        }

        // Limpiar cards personalizadas previas
        document.querySelectorAll('.is-custom-card').forEach(el => el.remove());

        // Enfermedades
        const chkEnfOtra = document.getElementById('chkEnfOtra');
        const txtEnfOtra = document.getElementById('modalVisitaEnfOtraTexto');
        if (chkEnfOtra) chkEnfOtra.checked = false;
        if (txtEnfOtra) txtEnfOtra.value = '';

        document.querySelectorAll('input[name="enfermedades"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });

        (item.enfermedades || []).forEach(enf => {
            const existing = document.querySelector(`input[name="enfermedades"][value="${CSS.escape(enf)}"]`);
            if (existing) {
                existing.checked = true;
                const lbl = existing.closest('.ap-checkbox-card');
                if (lbl) lbl.classList.add('is-checked');
            } else if (enf === 'Otra') {
                if (chkEnfOtra) chkEnfOtra.checked = true;
            } else {
                agregarNuevaEnfermedad(enf, true);
            }
        });
        if (chkEnfOtra) toggleEnfermedadOtra(chkEnfOtra);

        // Labores
        const chkLabOtra = document.getElementById('chkLabOtra');
        const txtLabOtra = document.getElementById('modalVisitaLabOtraTexto');
        if (chkLabOtra) chkLabOtra.checked = false;
        if (txtLabOtra) txtLabOtra.value = '';

        document.querySelectorAll('input[name="labores"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });

        (item.labores || []).forEach(lab => {
            const existing = document.querySelector(`input[name="labores"][value="${CSS.escape(lab)}"]`);
            if (existing) {
                existing.checked = true;
                const lbl = existing.closest('.ap-checkbox-card');
                if (lbl) lbl.classList.add('is-checked');
            } else if (lab === 'Otra') {
                if (chkLabOtra) chkLabOtra.checked = true;
            } else {
                agregarNuevaLabor(lab, true);
            }
        });
        if (chkLabOtra) toggleLaborOtra(chkLabOtra);

        document.getElementById('modalVisitaBackdrop').classList.add('is-open');
    }

    function closeVisitaModal() {
        document.getElementById('modalVisitaBackdrop').classList.remove('is-open');
    }

    function saveVisitaForm(e) {
        e.preventDefault();

        const id = document.getElementById('modalVisitaId').value;
        const fecha = document.getElementById('modalVisitaFecha').value.trim();
        const colmena = document.getElementById('modalVisitaColmena').value.trim();
        const responsable = document.getElementById('modalVisitaResponsable').value.trim();
        const estadoGeneral = document.getElementById('modalVisitaEstadoGeneral').value;
        const presenciaMiel = document.getElementById('modalVisitaPresenciaMiel').value;
        const mielKg = parseFloat(document.getElementById('modalVisitaMielKg').value) || 0;
        
        const reinaVista = document.getElementById('chkSeVioReina').checked;
        const reinaMarcada = document.getElementById('chkEstaMarcada').checked;
        
        // Color reina
        let colorMarca = document.getElementById('modalVisitaColorMarca').value;
        if (colorMarca === 'Otro') {
            const custom = (document.getElementById('modalVisitaColorMarcaOtro')?.value || '').trim();
            colorMarca = custom || 'Otro';
        }

        // Enfermedades
        const enfermedades = [];
        document.querySelectorAll('input[name="enfermedades"]:checked').forEach(cb => {
            enfermedades.push(cb.value);
        });
        const chkEnfOtra = document.getElementById('chkEnfOtra');
        if (chkEnfOtra && chkEnfOtra.checked) {
            const customEnf = (document.getElementById('modalVisitaEnfOtraTexto')?.value || '').trim();
            if (customEnf && !enfermedades.includes(customEnf)) {
                enfermedades.push(customEnf);
            } else if (!customEnf && !enfermedades.includes('Otra')) {
                enfermedades.push('Otra');
            }
        }

        // Labores
        const labores = [];
        document.querySelectorAll('input[name="labores"]:checked').forEach(cb => {
            labores.push(cb.value);
        });
        const chkLabOtra = document.getElementById('chkLabOtra');
        if (chkLabOtra && chkLabOtra.checked) {
            const customLab = (document.getElementById('modalVisitaLabOtraTexto')?.value || '').trim();
            if (customLab && !labores.includes(customLab)) {
                labores.push(customLab);
            } else if (!customLab && !labores.includes('Otra')) {
                labores.push('Otra');
            }
        }

        const alimenSuministrada = document.getElementById('modalVisitaAlimentacion').value.trim();
        const notas = document.getElementById('modalVisitaObservaciones').value.trim();

        if (id) {
            // Editar existente
            const item = inspeccionesList.find(v => v.id === parseInt(id));
            if (item) {
                item.fecha = fecha;
                item.colmena = colmena;
                item.responsable = responsable;
                item.estadoGeneral = estadoGeneral;
                item.presenciaMiel = presenciaMiel;
                item.mielKg = mielKg;
                item.reinaVista = reinaVista;
                item.reinaMarcada = reinaMarcada;
                item.colorMarca = colorMarca;
                item.enfermedades = enfermedades;
                item.labores = labores;
                item.alimenSuministrada = alimenSuministrada;
                item.notas = notas;
                showToast(`Visita ${item.regNumber} actualizada con éxito.`);
            }
        } else {
            // Crear nueva
            const maxReg = inspeccionesList.reduce((max, v) => {
                const match = v.regNumber.match(/REG-(\d+)/);
                if (match) {
                    const num = parseInt(match[1]);
                    return num > max ? num : max;
                }
                return max;
            }, 0);
            const nextReg = `REG-${String(maxReg + 1).padStart(4, '0')}`;
            const newId = inspeccionesList.length ? Math.max(...inspeccionesList.map(v => v.id)) + 1 : 1;

            inspeccionesList.unshift({
                id: newId,
                regNumber: nextReg,
                colmena: colmena,
                fecha: fecha,
                estadoGeneral: estadoGeneral,
                presenciaMiel: presenciaMiel,
                mielKg: mielKg,
                enfermedades: enfermedades,
                responsable: responsable,
                reinaVista: reinaVista,
                reinaMarcada: reinaMarcada,
                colorMarca: colorMarca,
                labores: labores,
                alimenSuministrada: alimenSuministrada,
                notas: notas,
                estado: 'Activa'
            });

            showToast(`Visita ${nextReg} registrada exitosamente.`);

            // Envío asíncrono para persistencia en backend
            const dateParts = fecha.split('/');
            const formattedIso = dateParts.length === 3 ? `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}` : new Date().toISOString().split('T')[0];

            fetch("{{ route('apicola.admin.inspecciones.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    registration_number: nextReg,
                    colmena_code: colmena,
                    date: formattedIso,
                    responsable: responsable,
                    general_state: estadoGeneral,
                    honey_presence: presenciaMiel,
                    harvested_honey_kg: mielKg,
                    queen_seen: reinaVista,
                    queen_marked: reinaMarcada,
                    queen_color: colorMarca,
                    diseases: enfermedades,
                    tasks: labores,
                    feed_supplied: alimenSuministrada,
                    notes: notas
                })
            }).catch(err => console.log('Registro local exitoso'));
        }

        try {
            localStorage.setItem('apicola_inspecciones_list', JSON.stringify(inspeccionesList));
        } catch(e) {
            console.error(e);
        }

        closeVisitaModal();
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    // Desactivar visita (sin eliminar de la base de datos)
    async function desactivarVisita(id, regNumber) {
        if (!confirm(`¿Estás seguro de que deseas desactivar la visita "${regNumber}"?`)) {
            return;
        }

        try {
            const response = await fetch(`{{ url('apicola/inspecciones') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No se pudo desactivar la visita.');
            }
        } catch (err) {
            console.warn('Persistencia en backend:', err.message);
        }

        const item = inspeccionesList.find(v => v.id === id);
        if (item) {
            item.estado = 'Inactiva';
        }
        try {
            localStorage.setItem('apicola_inspecciones_list', JSON.stringify(inspeccionesList));
        } catch(e) {
            console.error(e);
        }
        showToast(`Visita "${regNumber}" desactivada con éxito.`);
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    // Activar o Alternar estado de la visita
    async function toggleVisitaStatus(id, regNumber) {
        const item = inspeccionesList.find(v => v.id === id);
        if (!item) return;

        const nuevoEstado = (item.estado === 'Inactiva') ? 'Activa' : 'Inactiva';
        const accionStr = (nuevoEstado === 'Activa') ? 'activar' : 'desactivar';

        if (!confirm(`¿Estás seguro de que deseas ${accionStr} la visita "${regNumber}"?`)) {
            return;
        }

        try {
            const response = await fetch(`{{ url('apicola/inspecciones') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No se pudo cambiar el estado de la visita.');
            }
            if (result.nuevo_estado) {
                item.estado = result.nuevo_estado;
            } else {
                item.estado = nuevoEstado;
            }
        } catch (err) {
            console.warn('Persistencia en backend:', err.message);
            item.estado = nuevoEstado;
        }

        try {
            localStorage.setItem('apicola_inspecciones_list', JSON.stringify(inspeccionesList));
        } catch(e) {
            console.error(e);
        }
        showToast(`Visita "${regNumber}" marcada como ${item.estado}.`);
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    // ==================== MODAL DE DETALLE DE VISITA ====================
    function openVisitaDetailModal(id) {
        const item = inspeccionesList.find(v => v.id === id);
        if (!item) return;

        document.getElementById('detailVisitaRegNum').textContent = item.regNumber;
        const badgeEstado = document.getElementById('detailVisitaEstadoBadge');
        if (badgeEstado) {
            const isInactive = item.estado === 'Inactiva';
            badgeEstado.textContent = isInactive ? 'Inactiva' : 'Activa';
            badgeEstado.className = 'ap-status-badge ' + (isInactive ? 'inactiva' : 'bueno');
        }
        document.getElementById('detailVisitaColmena').textContent = `Inspección Colmena ${item.colmena}`;
        document.getElementById('detailVisitaFecha').textContent = item.fecha;
        document.getElementById('detailVisitaResp').textContent = item.responsable;
        document.getElementById('detailVisitaMielKg').textContent = `${parseFloat(item.mielKg).toFixed(1)} kg`;

        const badgeEstGen = document.getElementById('detailVisitaEstadoGen');
        badgeEstGen.textContent = item.estadoGeneral;
        badgeEstGen.className = 'ap-status-badge ' + (item.estadoGeneral === 'Bueno' ? 'bueno' : (item.estadoGeneral === 'Regular' ? 'regular' : 'malo'));

        const badgePresMiel = document.getElementById('detailVisitaPresMiel');
        badgePresMiel.textContent = item.presenciaMiel;
        badgePresMiel.className = 'ap-status-badge ' + (item.presenciaMiel === 'Bueno' ? 'bueno' : (item.presenciaMiel === 'Regular' ? 'regular' : 'malo'));

        const enfContainer = document.getElementById('detailVisitaEnfermedades');
        if (item.enfermedades && item.enfermedades.length > 0) {
            enfContainer.textContent = item.enfermedades.join(', ');
            enfContainer.className = 'ap-status-badge alerta';
        } else {
            enfContainer.textContent = 'Ninguna';
            enfContainer.className = 'ap-status-badge bueno';
        }

        // Reina
        let reinaStr = item.reinaVista ? 'Se vio la reina' : 'No se vio la reina';
        if (item.reinaMarcada) reinaStr += ` • Marcada (${item.colorMarca})`;
        document.getElementById('detailVisitaReinaInfo').textContent = reinaStr;

        // Labores
        let laboresStr = (item.labores && item.labores.length > 0) ? item.labores.join(', ') : 'Ninguna registrada';
        if (item.alimenSuministrada) laboresStr += ` [Alimentación: ${item.alimenSuministrada}]`;
        document.getElementById('detailVisitaLabores').textContent = laboresStr;

        document.getElementById('detailVisitaNotas').textContent = item.notas || 'Sin observaciones adicionales.';

        document.getElementById('btnEditFromVisitaDetail').onclick = () => {
            closeVisitaDetailModal();
            openEditVisitaModal(item.id);
        };

        document.getElementById('modalVisitaDetailBackdrop').classList.add('is-open');
    }

    function closeVisitaDetailModal() {
        document.getElementById('modalVisitaDetailBackdrop').classList.remove('is-open');
    }
</script>
@endsection
