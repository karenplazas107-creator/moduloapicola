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
                            <th class="col-actions" style="text-align: right; padding-right: 1.5rem;">ACCIONES</th>
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
<style>
    /* ================= ESTILOS ESPECÍFICOS DEL CALENDARIO FLORAL 2026 ================= */
    .ap-etapas-legend-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.9rem 1.25rem;
        margin-bottom: 1.4rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .ap-legend-left {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ap-legend-title {
        font-size: 0.74rem;
        font-weight: 800;
        color: #64748b;
        letter-spacing: 0.6px;
        margin-right: 6px;
    }

    .ap-legend-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.81rem;
        font-weight: 600;
        user-select: none;
    }

    .ap-legend-indicator {
        width: 13px;
        height: 13px;
        border-radius: 3px;
        display: inline-block;
    }

    .pill-inicio {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .pill-inicio .ap-legend-indicator {
        background: #86efac;
    }

    .pill-pico {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .pill-pico .ap-legend-indicator {
        background: #f59e0b;
    }

    .pill-fin {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .pill-fin .ap-legend-indicator {
        background: #10b981;
    }

    .pill-sin {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .pill-sin .ap-legend-indicator {
        background: #e2e8f0;
    }

    .ap-legend-right {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    /* ================= MATRIZ FENOLÓGICA DE LA TABLA ================= */
    .ap-calendar-matrix-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ap-calendar-matrix-table thead th {
        background: #f8fafc;
        padding: 12px 6px;
        font-size: 0.76rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        text-align: center;
    }

    .ap-calendar-matrix-table thead th.col-planta {
        text-align: left;
        padding-left: 1.4rem;
        min-width: 250px;
    }

    .ap-calendar-matrix-table thead th.col-month {
        width: 52px;
        min-width: 48px;
    }

    /* Resaltado del mes actual en la cabecera */
    .ap-calendar-matrix-table thead th.current-month-col {
        background: #059669 !important;
        color: #ffffff !important;
        border-radius: 7px;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25);
    }

    .ap-calendar-matrix-table tbody td {
        padding: 12px 6px;
        border-bottom: 1px solid #f1f5f9;
        text-align: center;
        vertical-align: middle;
    }

    .ap-calendar-matrix-table tbody td.col-planta-cell {
        text-align: left;
        padding-left: 1.4rem;
    }

    /* Badges de etapa en la celda del mes */
    .ap-stage-cell-badge {
        width: 32px;
        height: 30px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.8rem;
        margin: 0 auto;
        transition: transform 0.15s ease;
    }

    .ap-stage-cell-badge:hover {
        transform: scale(1.12);
    }

    .ap-stage-cell-badge.stage-inicio {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .ap-stage-cell-badge.stage-pico {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .ap-stage-cell-badge.stage-fin {
        background: #d1fae5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .ap-stage-cell-empty {
        width: 30px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
        font-size: 1rem;
    }

    /* ================= CUADRÍCULA INTERACTIVA DEL MODAL ================= */
    .ap-months-interactive-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 6px;
    }

    .ap-month-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        background: #ffffff;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: left;
    }

    .ap-month-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
    }

    .ap-month-card-code {
        font-size: 0.96rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .ap-month-card-stage {
        font-size: 0.74rem;
        color: #94a3b8;
        font-weight: 600;
    }

    /* Estados de la tarjeta mensual según la etapa activa */
    .ap-month-card.card-stage-inicio {
        background: #f0fdf4;
        border-color: #86efac;
    }
    .ap-month-card.card-stage-inicio .ap-month-card-code {
        color: #166534;
    }
    .ap-month-card.card-stage-inicio .ap-month-card-stage {
        color: #16a34a;
        font-weight: 700;
    }

    .ap-month-card.card-stage-pico {
        background: #fffbeb;
        border-color: #fcd34d;
    }
    .ap-month-card.card-stage-pico .ap-month-card-code {
        color: #92400e;
    }
    .ap-month-card.card-stage-pico .ap-month-card-stage {
        color: #d97706;
        font-weight: 700;
    }

    .ap-month-card.card-stage-fin {
        background: #ecfdf5;
        border-color: #6ee7b7;
    }
    .ap-month-card.card-stage-fin .ap-month-card-code {
        color: #065f46;
    }
    .ap-month-card.card-stage-fin .ap-month-card-stage {
        color: #059669;
        font-weight: 700;
    }
</style>
@endsection

@section('scripts')
<script>
    // ==================== DATOS Y ESTADO DEL CALENDARIO ====================
    const MONTH_NAMES = [
        { code: "Ene", full: "Enero" },
        { code: "Feb", full: "Febrero" },
        { code: "Mar", full: "Marzo" },
        { code: "Abr", full: "Abril" },
        { code: "May", full: "Mayo" },
        { code: "Jun", full: "Junio" },
        { code: "Jul", full: "Julio" },
        { code: "Ago", full: "Agosto" },
        { code: "Sep", full: "Septiembre" },
        { code: "Oct", full: "Octubre" },
        { code: "Nov", full: "Noviembre" },
        { code: "Dic", full: "Diciembre" }
    ];

    // Secuencia de etapas al hacer clic: sin floración -> inicio -> pico -> fin -> sin floración
    const STAGE_CYCLE = [
        { key: "ninguna", label: "Sin floración", short: "-", cardClass: "" },
        { key: "inicio", label: "Inicio de floración", short: "I", cardClass: "card-stage-inicio" },
        { key: "pico", label: "Pico de floración", short: "P", cardClass: "card-stage-pico" },
        { key: "fin", label: "Fin de floración", short: "F", cardClass: "card-stage-fin" }
    ];

    // Lista de plantas melíferas cargada directamente desde la base de datos MySQL (tabla calendario_floral y especie_malifera)
    let plantasList = @json($plantasFormatted ?? []);

    // Limpiar restos de localStorage si existían de versiones anteriores
    try {
        localStorage.removeItem('apicola_calendario_plantas');
    } catch (e) {}

    // Estado temporal de los 12 meses para el modal
    let currentModalMonths = {};

    document.addEventListener('DOMContentLoaded', () => {
        highlightCurrentMonth();
        renderPlantasTable();
    });

    // Resaltar el mes en curso en la cabecera (Septiembre = mes índice 8)
    function highlightCurrentMonth() {
        const now = new Date();
        const currentMonthIndex = now.getMonth(); // 0 a 11 (8 = Sep)
        
        const thMonths = document.querySelectorAll('.col-month');
        thMonths.forEach(th => {
            const mIndex = parseInt(th.getAttribute('data-month'));
            if (mIndex === currentMonthIndex) {
                th.classList.add('current-month-col');
                th.title = `Mes actual en curso (${MONTH_NAMES[mIndex].full})`;
            } else {
                th.classList.remove('current-month-col');
            }
        });
    }

    // Renderizar la tabla fenológica
    function renderPlantasTable() {
        const filtroAnio = document.getElementById('filtroAnio')?.value || 'all';
        const filtroEtapa = document.getElementById('filtroEtapa')?.value || 'all';
        const filtroEstado = document.getElementById('filtroEstado')?.value || 'all';

        const labelAnioEl = document.getElementById('labelAnioSeleccionado');
        if (labelAnioEl) {
            labelAnioEl.textContent = filtroAnio === 'all' ? '(Todos los años)' : filtroAnio;
        }

        const filtered = plantasList.filter(planta => {
            const matchAnio = (filtroAnio === 'all') || (String(planta.anio || 2026) === String(filtroAnio));
            const matchEstado = (filtroEstado === 'all') || (planta.estado === filtroEstado);

            let matchEtapa = true;
            if (filtroEtapa !== 'all') {
                matchEtapa = Object.values(planta.meses || {}).includes(filtroEtapa);
            }

            return matchAnio && matchEstado && matchEtapa;
        });

        const tbody = document.getElementById('plantasTableBody');
        tbody.innerHTML = '';

        document.getElementById('shownPlantasCount').textContent = filtered.length;
        document.getElementById('totalPlantasCount').textContent = plantasList.length;

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="14" style="text-align:center; padding: 2.8rem 1rem; color: #94a3b8;">
                        <i class="fas fa-seedling" style="font-size: 1.8rem; margin-bottom: 0.6rem; display:block; opacity: 0.6;"></i>
                        No se encontraron plantas melíferas con los filtros seleccionados.
                    </td>
                </tr>
            `;
            return;
        }

        filtered.forEach(planta => {
            const tr = document.createElement('tr');

            let mesesHtml = '';
            for (let i = 0; i < 12; i++) {
                const etapa = (planta.meses && planta.meses[i]) ? planta.meses[i] : 'ninguna';
                let cellContent = '';

                if (etapa === 'inicio') {
                    cellContent = `<span class="ap-stage-cell-badge stage-inicio" title="Inicio de floración en ${MONTH_NAMES[i].full}">I</span>`;
                } else if (etapa === 'pico') {
                    cellContent = `<span class="ap-stage-cell-badge stage-pico" title="Pico de floración en ${MONTH_NAMES[i].full}">P</span>`;
                } else if (etapa === 'fin') {
                    cellContent = `<span class="ap-stage-cell-badge stage-fin" title="Fin de floración en ${MONTH_NAMES[i].full}">F</span>`;
                } else {
                    cellContent = `<span class="ap-stage-cell-empty">·</span>`;
                }

                mesesHtml += `<td>${cellContent}</td>`;
            }

            const isActiva = planta.estado === 'Activa';
            const toggleActionText = isActiva ? 'Desactivar' : 'Activar';
            const toggleActionClass = isActiva ? 'deactivate' : 'activate';

            tr.innerHTML = `
                <td class="col-planta-cell">
                    <div style="font-weight: 800; color: #0f172a; font-size: 0.93rem; line-height: 1.25;">
                        ${escapeHtml(planta.nombreComun || '')}
                    </div>
                    <div style="font-style: italic; color: #64748b; font-size: 0.82rem; margin-top: 2px;">
                        ${escapeHtml(planta.nombreCientifico || '')}
                    </div>
                    <div style="margin-top: 4px; display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                        <span style="font-size: 0.7rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 4px;">
                            Año ${planta.anio || 2026}
                        </span>
                        <span style="font-size: 0.7rem; font-weight: 700; background: #f1f5f9; color: #475569; padding: 2px 7px; border-radius: 4px;">
                            ${escapeHtml(planta.aporte || 'Néctar y polen')}
                        </span>
                        ${!isActiva ? '<span style="font-size: 0.68rem; font-weight: 700; background: #fee2e2; color: #dc2626; padding: 1px 6px; border-radius: 4px;">Inactiva</span>' : ''}
                    </div>
                </td>
                ${mesesHtml}
                <td style="text-align: right; padding-right: 1.4rem;">
                    <div class="ap-actions-group" style="justify-content: flex-end;">
                        <button type="button" class="ap-action-btn edit" onclick="openEditPlantaModal(${planta.id})">Editar</button>
                        ${isActiva 
                            ? `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444; font-weight: 600;" onclick="desactivarPlanta(${planta.id}, '${escapeJs(planta.nombreComun)}')">Desactivar</button>`
                            : `<button type="button" class="ap-action-btn activate" style="color: #059669; font-weight: 600;" onclick="togglePlantaStatus(${planta.id})">Activar</button>`
                        }
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    function filterPlantas() {
        renderPlantasTable();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeJs(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    // ==================== MODAL DE NUEVA / EDITAR PLANTA ====================
    function openNewPlantaModal() {
        document.getElementById('modalPlantaTitle').textContent = 'Nueva planta melífera';
        document.getElementById('modalPlantaId').value = '';
        document.getElementById('modalPlantaNombreComun').value = '';
        document.getElementById('modalPlantaNombreCientifico').value = '';
        document.getElementById('modalPlantaAporte').value = 'Néctar y polen';
        
        const filtroAnioVal = document.getElementById('filtroAnio')?.value;
        const defaultAnio = (filtroAnioVal && filtroAnioVal !== 'all') ? parseInt(filtroAnioVal) : 2026;
        document.getElementById('modalPlantaAnio').value = defaultAnio;

        document.getElementById('modalPlantaEstado').value = 'Activa';

        // Inicializar todos los 12 meses en "ninguna" (Sin floración)
        currentModalMonths = {};
        for (let i = 0; i < 12; i++) {
            currentModalMonths[i] = "ninguna";
        }

        renderModalMonthsGrid();
        document.getElementById('modalPlantaBackdrop').classList.add('is-open');
    }

    function openEditPlantaModal(id) {
        const planta = plantasList.find(p => p.id === id);
        if (!planta) return;

        document.getElementById('modalPlantaTitle').textContent = 'Editar planta melífera';
        document.getElementById('modalPlantaId').value = planta.id;
        document.getElementById('modalPlantaNombreComun').value = planta.nombreComun;
        document.getElementById('modalPlantaNombreCientifico').value = planta.nombreCientifico;
        document.getElementById('modalPlantaAporte').value = planta.aporte;
        document.getElementById('modalPlantaAnio').value = planta.anio || 2026;
        document.getElementById('modalPlantaEstado').value = planta.estado;

        // Copiar el estado de los meses
        currentModalMonths = {};
        for (let i = 0; i < 12; i++) {
            currentModalMonths[i] = (planta.meses && planta.meses[i]) ? planta.meses[i] : 'ninguna';
        }

        renderModalMonthsGrid();
        document.getElementById('modalPlantaBackdrop').classList.add('is-open');
    }

    function closePlantaModal() {
        document.getElementById('modalPlantaBackdrop').classList.remove('is-open');
    }

    // Renderizar la cuadrícula interactiva de 12 meses del modal
    function renderModalMonthsGrid() {
        const grid = document.getElementById('modalMonthsGrid');
        grid.innerHTML = '';

        MONTH_NAMES.forEach((m, index) => {
            const currentStageKey = currentModalMonths[index] || "ninguna";
            const stageConfig = STAGE_CYCLE.find(s => s.key === currentStageKey) || STAGE_CYCLE[0];

            const card = document.createElement('div');
            card.className = `ap-month-card ${stageConfig.cardClass}`;
            card.onclick = () => cycleMonthStage(index);

            card.innerHTML = `
                <div class="ap-month-card-code">${m.code}</div>
                <div class="ap-month-card-stage">${stageConfig.label}</div>
            `;

            grid.appendChild(card);
        });
    }

    // Ciclar la etapa de un mes al hacer clic: sin floración -> inicio -> pico -> fin -> sin floración
    function cycleMonthStage(monthIndex) {
        const currentStageKey = currentModalMonths[monthIndex] || "ninguna";
        const currentIndex = STAGE_CYCLE.findIndex(s => s.key === currentStageKey);
        const nextIndex = (currentIndex + 1) % STAGE_CYCLE.length;

        currentModalMonths[monthIndex] = STAGE_CYCLE[nextIndex].key;
        renderModalMonthsGrid();
    }

    // Guardar el formulario de planta melífera enviando datos reales a MySQL
    async function savePlantaForm(e) {
        e.preventDefault();

        const btnSave = document.getElementById('btnSavePlanta');
        const originalText = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        const id = document.getElementById('modalPlantaId').value;
        const nombreComun = document.getElementById('modalPlantaNombreComun').value.trim();
        const nombreCientifico = document.getElementById('modalPlantaNombreCientifico').value.trim();
        const aporte = document.getElementById('modalPlantaAporte').value;
        const anio = parseInt(document.getElementById('modalPlantaAnio').value) || 2026;
        const estado = document.getElementById('modalPlantaEstado').value;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const payload = {
            nombre_comun: nombreComun,
            nombre_cientifico: nombreCientifico,
            aporte: aporte,
            anio: anio,
            estado: estado,
            meses: currentModalMonths
        };

        const url = id ? `{{ url('apicola/calendario') }}/${id}` : `{{ route('apicola.admin.calendario.store') }}`;
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const result = await res.json();

            if (!res.ok || !result.success) {
                throw new Error(result.message || 'Error al procesar la solicitud.');
            }

            if (id) {
                const index = plantasList.findIndex(p => p.id === parseInt(id));
                if (index !== -1) {
                    plantasList[index] = result.data;
                }
            } else {
                plantasList.unshift(result.data);
            }

            showToast(result.message || 'Planta guardada correctamente en la base de datos.');
            closePlantaModal();
            renderPlantasTable();
        } catch (err) {
            console.error('Error guardando planta:', err);
            alert(err.message || 'No se pudo guardar la planta en la base de datos.');
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        }
    }

    // Alternar estado activo / inactivo de una planta en MySQL
    async function togglePlantaStatus(id) {
        const planta = plantasList.find(p => p.id === id);
        if (!planta) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const res = await fetch(`{{ url('apicola/calendario') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const result = await res.json();
            if (!res.ok || !result.success) {
                throw new Error(result.message || 'Error al cambiar estado.');
            }

            planta.estado = result.nuevoEstado;
            showToast(result.message || `Planta "${planta.nombreComun}" marcada como ${planta.estado.toLowerCase()}.`);
            renderPlantasTable();
        } catch (err) {
            console.error('Error toggling status:', err);
            alert(err.message || 'Error al actualizar estado en la base de datos.');
        }
    }

    // Desactivar una planta del calendario floral (cambio de estado a Inactiva)
    async function desactivarPlanta(id, nombreComun) {
        if (!confirm(`¿Estás seguro de que deseas desactivar la planta "${nombreComun}" del calendario floral?`)) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const res = await fetch(`{{ url('apicola/calendario') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const result = await res.json();
            if (!res.ok || !result.success) {
                throw new Error(result.message || 'Error al desactivar la planta.');
            }

            const planta = plantasList.find(p => p.id === id);
            if (planta) {
                planta.estado = 'Inactiva';
            }
            showToast(result.message || `Planta "${nombreComun}" desactivada con éxito.`);
            renderPlantasTable();
        } catch (err) {
            console.error('Error desactivando planta:', err);
            alert(err.message || 'Error al desactivar en la base de datos.');
        }
    }
</script>
@endsection
