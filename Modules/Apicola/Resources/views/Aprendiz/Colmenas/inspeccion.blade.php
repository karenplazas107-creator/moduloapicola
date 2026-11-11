@extends('apicola::Aprendiz.layout')

@section('title', 'Producción e Inspección • ' . ($selectedColmena ?: 'Colmenas') . ' • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.aprendiz.colmenas.index') }}">Colmenas</a>
    <span>/</span>
    <span style="font-weight: 700; color: #059669;" id="currentColmenaCode">{{ $selectedColmena ?: 'Sin colmena' }}</span>
    <span>/</span>
    <span class="active-page">Producción e inspección</span>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/aprendiz-inspeccion.css') }}">
    <style>
        .swal2-container,
        .swal2-high-priority {
            z-index: 999999 !important;
        }
    </style>
@endsection

@section('content')
    <!-- Encabezado de la vista con perfil aprendiz -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">
                COLMENAS / <span id="colmenaTag">{{ $selectedColmena ?: 'GENERAL' }}</span> / PRODUCCIÓN E INSPECCIÓN
            </span>
            <h1 class="ap-view-main-title">Producción e inspección</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #f59e0b;">
                {{ strtoupper(substr(Auth::user()?->nickname ?? 'AP', 0, 2)) }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Aprendiz Apícola') }}</span>
                <span class="ap-user-chip-role">Rol: Aprendiz Apícola</span>
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
                <label class="ap-field-label" style="margin-bottom:0;" for="selectChangeColmena">CAMBIAR COLMENA:</label>
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
                <input type="date" id="filterFechaInicio" class="ap-date-input" value="" title="Fecha inicial" onclick="try{this.showPicker()}catch(e){}" style="cursor: pointer;">
                <input type="date" id="filterFechaFin" class="ap-date-input" value="" title="Fecha final" onclick="try{this.showPicker()}catch(e){}" style="cursor: pointer;">
                <button type="button" class="ap-btn-outline-green" onclick="filterVisitas()">
                    Filtrar
                </button>
            </div>

            <div class="ap-controls-right" style="display: flex; gap: 10px; align-items: center;">
                <button type="button" class="ap-btn-outline-green" onclick="openEnfermedadesModal()" title="Registrar y gestionar catálogo de enfermedades" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-virus"></i> Enfermedades
                </button>
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

    <!-- Toast Notification -->
    <div class="ap-toast" id="inspeccionToast"></div>
@endsection

@section('modals')
    <!-- ==================== MODAL: REGISTRAR / EDITAR VISITA (EXACTO A LAS CAPTURAS) ==================== -->
    <div class="ap-modal-backdrop" id="modalVisitaBackdrop">
        <div class="ap-modal-window">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <h2 class="ap-modal-title" id="modalVisitaTitle" style="margin-bottom:0;">Registrar visita</h2>
                    <button type="button" class="ap-modal-close-btn" onclick="closeVisitaModal()" title="Cerrar">&times;</button>
                </div>

                <form id="formVisita" onsubmit="saveVisitaForm(event)">
                    <input type="hidden" id="modalVisitaId" value="">

                    <!-- Fila 1: FECHA, COLMENA, RESPONSABLE -->
                    <div class="ap-coords-row cols-3" style="margin-bottom: 1.25rem;">
                        <div>
                            <label class="ap-field-label" for="modalVisitaFecha">FECHA</label>
                            <input type="date" id="modalVisitaFecha" class="ap-field-input" value="" required onclick="try{this.showPicker()}catch(e){}" style="cursor: pointer;">
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalVisitaColmena">COLMENA</label>
                            <input type="text" id="modalVisitaColmena" class="ap-field-input" placeholder="Ej: COL-001" value="{{ $selectedColmena ?? '' }}" required>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalVisitaResponsable">RESPONSABLE</label>
                            <input type="text" id="modalVisitaResponsable" class="ap-field-input" value="{{ $aprendizNombre ?? (Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Aprendiz')) }}" readonly style="background-color: #f8fafc; cursor: not-allowed; font-weight: 700; color: #047857;" title="Asignado automáticamente al aprendiz que realiza el registro" required>
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
                                <input type="text" id="modalVisitaColorMarcaOtro" class="ap-field-input" placeholder="Ej: Naranja, Plateado, Rosado..." maxlength="50" oninput="validarTextoSinNumerosVisita(this, 'errorColorMarcaOtroAprendiz')" onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarColorMarcaCustom();}">
                                <button type="button" class="ap-btn-add-mini" onclick="agregarColorMarcaCustom()" title="Agregar color">
                                    <i class="fas fa-plus"></i> Agregar
                                </button>
                            </div>
                            <span id="errorColorMarcaOtroAprendiz" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;">
                                <i class="fas fa-circle-exclamation"></i> Color inválido: no se permiten números, solo letras.
                            </span>
                        </div>
                    </div>

                    <!-- Sección: Enfermedades Detectadas -->
                    <div class="ap-form-section-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span>ENFERMEDADES DETECTADAS</span>
                        <span style="font-size: 0.72rem; color: #64748b; font-weight: normal; text-transform: none;">Marque las detectadas durante la visita</span>
                    </div>

                    <div class="ap-checkbox-card-group" id="containerEnfermedades" style="margin-bottom: 0.5rem;">
                        @if(isset($enfermedadesDisponibles) && count($enfermedadesDisponibles) > 0)
                            @foreach($enfermedadesDisponibles as $enf)
                                @php
                                    $enfId = 'lblEnf_' . \Illuminate\Support\Str::slug($enf, '_');
                                @endphp
                                <label class="ap-checkbox-card" id="{{ $enfId }}">
                                    <input type="checkbox" name="enfermedades" value="{{ $enf }}" onchange="toggleCheckboxCard(this, '{{ $enfId }}')">
                                    <span>{{ $enf }}</span>
                                </label>
                            @endforeach
                        @else
                            <p id="msgSinEnfermedades" style="font-size: 0.85rem; color: #64748b; font-style: italic; margin: 6px 0;">No hay enfermedades registradas en el sistema. Puede registrarlas desde el botón superior "Enfermedades".</p>
                        @endif
                    </div>

                    <p class="ap-field-hint" style="margin-bottom: 1.25rem;">Marque solo si detectó alguna patología en la colmena. Para agregar más opciones use el botón superior "Enfermedades".</p>

                    <!-- Sección: Labores Realizadas -->
                    <div class="ap-form-section-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span>LABORES REALIZADAS</span>
                        <span style="font-size: 0.72rem; color: #64748b; font-weight: normal; text-transform: none;">Marque las realizadas o registre otra</span>
                    </div>

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

                        <!-- Botón para registrar otra labor (Idéntico a Enfermedades) -->
                        <label class="ap-checkbox-card" id="lblLabOtra" onclick="toggleSeccionNuevaLabor(event)" style="border-style: dashed; border-color: #059669; color: #047857; font-weight: 700; cursor: pointer;">
                            <span>➕ Otra / Registrar...</span>
                        </label>
                    </div>

                    <!-- Contenedor desplegable para registrar una nueva labor (Estilo idéntico a Nueva Enfermedad) -->
                    <div id="containerNuevaLabor" style="display: none; margin-top: 6px; margin-bottom: 12px; background: #f0fdf4; padding: 12px; border-radius: 12px; border: 1.5px solid #bbf7d0;">
                        <label style="display: block; font-size: 0.74rem; font-weight: 800; color: #166534; margin-bottom: 5px; text-transform: uppercase;">
                            <i class="fas fa-tools"></i> Nombre de la nueva labor:
                        </label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="inputNuevaLaborTexto" class="ap-field-input" placeholder="Ej: Cosecha, Ahumado, Mantenimiento..." style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1px solid #10b981; font-size: 0.88rem; color: #0f172a; background: #ffffff;" oninput="validarTextoSinNumerosVisita(this, 'errorNuevaLaborAprendiz')" onkeydown="if(event.key==='Enter'){event.preventDefault(); confirmarNuevaLabor();}">
                            <button type="button" onclick="confirmarNuevaLabor()" style="background: #047857; color: #ffffff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.84rem; font-weight: 800; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                        <span id="errorNuevaLaborAprendiz" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;">
                            <i class="fas fa-circle-exclamation"></i> Labor inválida: no se permiten números, solo letras.
                        </span>
                        <span style="display: block; font-size: 0.72rem; color: #15803d; margin-top: 5px;">
                            Quedará marcada en esta visita y guardada para futuras inspecciones.
                        </span>
                    </div>

                    <p class="ap-field-hint" style="margin-bottom: 1.25rem;">Marque las labores ejecutadas o registre una nueva si fue diferente.</p>

                    <div class="ap-form-field">
                        <label class="ap-field-label" for="modalVisitaAlimentacion">ALIMENTACIÓN SUMINISTRADA</label>
                        <input type="text" id="modalVisitaAlimentacion" class="ap-field-input" value="" placeholder="Jarabe, torta proteica, etc." oninput="validarTextoSinNumerosVisita(this, 'errorAlimentacionAprendiz')">
                        <span id="errorAlimentacionAprendiz" style="display:none; color:#dc2626; font-size:0.75rem; font-weight:700; margin-top:4px;">
                            <i class="fas fa-circle-exclamation"></i> Alimentación inválida: no se permiten números, solo letras.
                        </span>
                    </div>

                    <!-- Sección: Observaciones -->
                    <div class="ap-form-field" style="margin-top: 1.2rem;">
                        <label class="ap-field-label" for="modalVisitaObservaciones">OBSERVACIONES</label>
                        <textarea id="modalVisitaObservaciones" class="ap-field-input" rows="3" placeholder="Detalles de la inspección..."></textarea>
                    </div>

                    <!-- Footer: Cancelar y Guardar visita -->
                    <div class="ap-modal-footer">
                        <button type="button" class="ap-btn-modal-cancel" onclick="closeVisitaModal()">Cancelar</button>
                        <button type="submit" class="ap-btn-modal-save" id="btnSaveVisita">Guardar visita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: DETALLE DE VISITA ==================== -->
    <div class="ap-modal-backdrop" id="modalVisitaDetailBackdrop">
        <div class="ap-modal-window" style="max-width: 580px;">
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

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.2rem; margin-bottom:1.2rem;">
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
                    <p id="detailVisitaNotas" style="font-size:0.85rem; color:#475569; line-height:1.5; background: #f1f5f9; padding: 10px 14px; border-radius: 8px; margin:0;"></p>
                </div>

                <div class="ap-modal-footer" style="justify-content:space-between;">
                    <button type="button" class="ap-btn-modal-cancel" onclick="closeVisitaDetailModal()">Cerrar</button>
                    <button type="button" class="ap-btn-modal-save" id="btnEditFromVisitaDetail">Editar visita</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: GESTIÓN DE ENFERMEDADES ==================== -->
    <div class="ap-modal-backdrop" id="modalEnfermedadesBackdrop">
        <div class="ap-modal-window" style="max-width: 560px;">
            <div class="ap-modal-header" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%); color: #ffffff; padding: 18px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fas fa-virus"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #ffffff;">Registro de Enfermedades</h3>
                        <p style="margin: 2px 0 0; font-size: 0.78rem; color: #d1fae5;">Patologías y alertas sanitarias apícolas</p>
                    </div>
                </div>
                <button type="button" class="ap-modal-close" onclick="closeEnfermedadesModal()" style="background: none; border: none; color: #ffffff; font-size: 1.4rem; cursor: pointer; opacity: 0.85;">&times;</button>
            </div>

            <div class="ap-modal-body" style="padding: 22px 24px;">
                <!-- Formulario exclusivo para registrar una enfermedad -->
                <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <label for="inputNuevaEnfermedadCatalogo" style="display: block; font-size: 0.76rem; font-weight: 800; color: #166534; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-plus-circle" style="color: #059669;"></i> Nombre de la nueva enfermedad:
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="inputNuevaEnfermedadCatalogo" class="ap-field-input" placeholder="Ej: Nosema, Polilla de la cera, Micosis..." maxlength="100" style="flex: 1; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #10b981; font-size: 0.92rem; color: #0f172a; background: #ffffff;" oninput="validarTextoSinNumerosVisita(this, 'errorNuevaEnfermedadCatalogo')" onkeydown="if(event.key==='Enter'){event.preventDefault(); guardarNuevaEnfermedadCatalogo();}">
                        <button type="button" id="btnGuardarEnfermedadCatalogo" onclick="guardarNuevaEnfermedadCatalogo()" class="ap-btn-solid-green" style="padding: 10px 20px; font-weight: 800; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
                            <i class="fas fa-plus"></i> Registrar
                        </button>
                    </div>
                    <span id="errorNuevaEnfermedadCatalogo" style="display:none; color:#dc2626; font-size:0.78rem; font-weight:700; margin-top:6px;">
                        <i class="fas fa-circle-exclamation"></i> Enfermedad inválida: no se permiten números, solo letras.
                    </span>
                    <p style="margin: 8px 0 0; font-size: 0.74rem; color: #15803d; line-height: 1.4;">
                        <i class="fas fa-info-circle"></i> Al registrarla quedará guardada y aparecerá como opción disponible al registrar una visita.
                    </p>
                </div>

                <!-- Listado de Enfermedades Registradas -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 0.8rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-list-check" style="color: #047857;"></i> Enfermedades Registradas
                    </span>
                    <span id="badgeTotalEnfermedades" style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 0.75rem; padding: 3px 12px; border-radius: 20px;">
                        {{ count($enfermedadesDisponibles ?? []) }} registradas
                    </span>
                </div>

                <!-- Contenedor dinámico de pills -->
                <div id="listaEnfermedadesCatalogo" style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 220px; overflow-y: auto; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <!-- Rellenado dinámicamente -->
                </div>
            </div>

            <div class="ap-modal-footer" style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 16px 16px; display: flex; justify-content: flex-end;">
                <button type="button" class="ap-btn-modal-cancel" onclick="closeEnfermedadesModal()">Cerrar</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    window.AprendizInspeccionConfig = {
        activeColmena: "{{ $selectedColmena ?? '' }}",
        inspeccionesList: @json($visitasInit ?? []),
        enfermedadesList: @json($enfermedades ?? []),
        enfermedadesNombres: @json($enfermedadesDisponibles ?? []),
        storeUrl: "{{ route('apicola.aprendiz.inspecciones.store') }}",
        baseUrl: "{{ url('apicola/aprendiz/inspecciones') }}",
        enfermedadesUrl: "{{ route('apicola.aprendiz.enfermedades.index') }}",
        csrfToken: "{{ csrf_token() }}",
        currentUserName: "{{ $aprendizNombre ?? (Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Aprendiz')) }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/aprendiz-inspeccion.js') }}?v={{ time() }}"></script>
@endsection
