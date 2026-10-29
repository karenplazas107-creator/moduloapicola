@extends('apicola::Admin.layout')

@section('title', 'Calendario Floral 2026 • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.calendario.index') }}">Calendario floral</a>
    <span>/</span>
    <span class="active-page">Fenología 2026</span>
@endsection

@section('content')
    <!-- Encabezado de la vista con perfil apicultor -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">CALENDARIO FLORAL</span>
            <h1 class="ap-view-main-title">Calendario floral</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #047857;">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->nickname ?? 'AP', 0, 2)) : 'AP' }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name" style="display:flex; align-items:center; gap:5px;">
                    {{ auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : (auth()->check() ? auth()->user()->nickname : 'Administrador Apícola') }}
                </span>
                <span class="ap-user-chip-role" style="display:flex; align-items:center; gap:4px;">
                    Rol: {{ auth()->check() && auth()->user()->roles->first() ? auth()->user()->roles->first()->name : 'Administrador' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal del Calendario Floral -->
    <div class="ap-list-section" style="margin-top: 0.5rem;">
        
        <!-- Fila Superior: Título y Controles -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin-bottom: 0.35rem; letter-spacing: -0.3px;">
                    Calendario floral <span id="labelAnioSeleccionado">2026</span>
                </h2>
                <p style="font-size: 0.88rem; color: #64748b; margin: 0;">
                    Meses de floración de las plantas melíferas de la zona, con su etapa en cada mes.
                </p>
            </div>

            <!-- Controles a la derecha: Filtros y Botón + Nueva planta -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <select id="filtroAnio" class="ap-filter-select" onchange="filterPlantas()" style="min-width: 125px;">
                    <option value="all">Año: todos</option>
                    <option value="2024">Año: 2024</option>
                    <option value="2025">Año: 2025</option>
                    <option value="2026" selected>Año: 2026</option>
                    <option value="2027">Año: 2027</option>
                    <option value="2028">Año: 2028</option>
                </select>

                <select id="filtroEtapa" class="ap-filter-select" onchange="filterPlantas()" style="min-width: 140px;">
                    <option value="all">Etapa: todas</option>
                    <option value="inicio">Inicio de floración</option>
                    <option value="pico">Pico de floración</option>
                    <option value="fin">Fin de floración</option>
                    <option value="ninguna">Sin floración</option>
                </select>

                <select id="filtroEstado" class="ap-filter-select" onchange="filterPlantas()" style="min-width: 130px;">
                    <option value="all">Estado: todos</option>
                    <option value="Activa">Activas</option>
                    <option value="Inactiva">Inactivas</option>
                </select>

                <button type="button" class="ap-btn-solid-green" onclick="openNewPlantaModal()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 9px 18px; border-radius: 9px;">
                    <i class="fas fa-plus"></i> Nueva planta
                </button>
            </div>
        </div>

        <!-- Barra de Leyenda de Etapas (Exacta al diseño) -->
        <div class="ap-etapas-legend-card">
            <div class="ap-legend-left">
                <span class="ap-legend-title">ETAPAS</span>
                
                <div class="ap-legend-pill pill-inicio">
                    <span class="ap-legend-indicator"></span>
                    <span>Inicio de floración</span>
                </div>

                <div class="ap-legend-pill pill-pico">
                    <span class="ap-legend-indicator"></span>
                    <span>Pico de floración</span>
                </div>

                <div class="ap-legend-pill pill-fin">
                    <span class="ap-legend-indicator"></span>
                    <span>Fin de floración</span>
                </div>

                <div class="ap-legend-pill pill-sin">
                    <span class="ap-legend-indicator"></span>
                    <span>Sin floración</span>
                </div>
            </div>

            <div class="ap-legend-right">
                <span>El mes en curso aparece resaltado en la cabecera.</span>
            </div>
        </div>

        <!-- Tabla / Matriz Fenológica de 12 Meses -->
        <div class="ap-table-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;">
            <div class="ap-table-scroll">
                <table class="ap-table ap-calendar-matrix-table">
                    <thead>
                        <tr>
                            <th class="col-planta">PLANTA MELÍFERA</th>
                            <th class="col-month" data-month="0">Ene</th>
                            <th class="col-month" data-month="1">Feb</th>
                            <th class="col-month" data-month="2">Mar</th>
                            <th class="col-month" data-month="3">Abr</th>
                            <th class="col-month" data-month="4">May</th>
                            <th class="col-month" data-month="5">Jun</th>
                            <th class="col-month" data-month="6">Jul</th>
                            <th class="col-month" data-month="7">Ago</th>
                            <th class="col-month" data-month="8">Sep</th>
                            <th class="col-month" data-month="9">Oct</th>
                            <th class="col-month" data-month="10">Nov</th>
                            <th class="col-month" data-month="11">Dic</th>
                            <th class="col-actions" style="text-align: center;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="plantasTableBody">
                        <!-- Generado dinámicamente con JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pie de la Tabla -->
            <div class="ap-table-footer-row" style="background: #ffffff; border-top: 1px solid #f1f5f9; padding: 0.9rem 1.4rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.83rem; color: #64748b;">
                <div>
                    Mostrando <strong id="shownPlantasCount" style="color: #0f172a;">7</strong> de <strong id="totalPlantasCount" style="color: #0f172a;">7</strong> plantas
                </div>
                <div style="font-weight: 600; color: #64748b; letter-spacing: 0.3px;">
                    <span style="color: #166534;">I</span> = inicio &nbsp;·&nbsp; 
                    <span style="color: #b45309;">P</span> = pico &nbsp;·&nbsp; 
                    <span style="color: #047857;">F</span> = fin
                </div>
            </div>
        </div>

    </div>
@endsection

@section('modals')
    <!-- ==================== MODAL: NUEVA / EDITAR PLANTA MELÍFERA ==================== -->
    <div class="ap-modal-backdrop" id="modalPlantaBackdrop">
        <div class="ap-modal-window" style="max-width: 580px; border-radius: 18px;">
            <div class="ap-modal-body" style="padding: 2.2rem 2.4rem 1.6rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.25rem;">
                    <div>
                        <h2 class="ap-modal-title" id="modalPlantaTitle" style="font-size: 1.65rem; font-weight: 900; color: #0f172a; margin-bottom: 0.25rem;">
                            Nueva planta melífera
                        </h2>
                        <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 0;">
                            Marca los meses de floración y la etapa de cada uno.
                        </p>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closePlantaModal()" title="Cerrar">&times;</button>
                </div>

                <form id="formPlanta" onsubmit="savePlantaForm(event)">
                    <input type="hidden" id="modalPlantaId" value="">

                    <!-- 1. Nombre Común -->
                    <div class="ap-form-field" style="margin-bottom: 1.2rem;">
                        <label class="ap-field-label" for="modalPlantaNombreComun">NOMBRE COMÚN</label>
                        <input type="text" id="modalPlantaNombreComun" class="ap-field-input" placeholder="Ej: Eucalipto" value="" required>
                    </div>

                    <!-- 2. Nombre Científico -->
                    <div class="ap-form-field" style="margin-bottom: 1.2rem;">
                        <label class="ap-field-label" for="modalPlantaNombreCientifico">NOMBRE CIENTÍFICO</label>
                        <input type="text" id="modalPlantaNombreCientifico" class="ap-field-input" placeholder="Ej: Eucalyptus globulus" value="" style="font-style: italic;" required>
                    </div>

                    <!-- 3. Aporte Principal, Año y Estado -->
                    <div style="display: grid; grid-template-columns: 1.2fr 0.9fr 0.9fr; gap: 12px; margin-bottom: 1.4rem;">
                        <div>
                            <label class="ap-field-label" for="modalPlantaAporte">APORTE PRINCIPAL</label>
                            <select id="modalPlantaAporte" class="ap-field-input" required>
                                <option value="Néctar y polen" selected>Néctar y polen</option>
                                <option value="Néctar">Néctar</option>
                                <option value="Polen">Polen</option>
                                <option value="Propóleo">Propóleo</option>
                            </select>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalPlantaAnio">AÑO</label>
                            <input type="number" id="modalPlantaAnio" class="ap-field-input" min="2020" max="2050" value="2026" placeholder="Ej: 2026" required>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalPlantaEstado">ESTADO</label>
                            <select id="modalPlantaEstado" class="ap-field-input" required>
                                <option value="Activa" selected>Activa</option>
                                <option value="Inactiva">Inactiva</option>
                            </select>
                        </div>
                    </div>

                    <!-- 4. Cuadrícula Interactiva de 12 Meses -->
                    <div class="ap-form-field" style="margin-bottom: 0.5rem;">
                        <label class="ap-field-label">MESES DE FLORACIÓN Y ETAPA</label>
                        
                        <div class="ap-months-interactive-grid" id="modalMonthsGrid">
                            <!-- 12 tarjetas generadas por JavaScript -->
                        </div>

                        <p class="ap-field-hint" style="color: #64748b; font-size: 0.82rem; margin-top: 9px; margin-bottom: 1.6rem;">
                            Toca un mes para recorrer las etapas: sin floración → inicio → pico → fin.
                        </p>
                    </div>

                    <!-- Footer de botones -->
                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 1.25rem;">
                        <button type="button" class="ap-btn-modal-cancel" onclick="closePlantaModal()" style="padding: 10px 22px; font-weight: 700;">
                            Cancelar
                        </button>
                        <button type="submit" class="ap-btn-modal-save" id="btnSavePlanta" style="padding: 10px 26px; font-weight: 800; background: #16a34a;">
                            Guardar planta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/calendario.css') }}">
@endsection

@section('scripts')
<script>
    window.CalendarioConfig = {
        plantasList: @json($plantasFormatted ?? []),
        baseUrl: "{{ url('apicola/calendario') }}",
        storeUrl: "{{ route('apicola.admin.calendario.store') }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/calendario.js') }}"></script>
@endsection
