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
                            <th style="text-align: center;">ACCIONES</th>
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
    window.InspeccionConfig = {
        activeColmena: "{{ $selectedColmena ?? '' }}",
        inspeccionesList: @json($visitasInit ?? []),
        storeUrl: "{{ route('apicola.admin.inspecciones.store') }}",
        baseUrl: "{{ url('apicola/inspecciones') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/inspeccion.js') }}"></script>
@endsection
