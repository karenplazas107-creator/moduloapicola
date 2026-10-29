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
    let plantasList = window.CalendarioConfig?.plantasList || [];

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
                <td style="text-align: center;">
                    <div class="ap-actions-group" style="justify-content: center;">
                        <button type="button" class="ap-action-btn edit" onclick="openEditPlantaModal(${planta.id})" title="Editar planta"><i class="fas fa-pen-to-square"></i></button>
                        ${isActiva 
                            ? `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444;" onclick="desactivarPlanta(${planta.id}, '${escapeJs(planta.nombreComun)}')" title="Desactivar planta"><i class="fas fa-ban"></i></button>`
                            : `<button type="button" class="ap-action-btn activate" style="color: #059669;" onclick="togglePlantaStatus(${planta.id})" title="Activar planta"><i class="fas fa-circle-check"></i></button>`
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

        const url = id ? `${window.CalendarioConfig?.baseUrl || "/apicola/calendario"}/${id}` : `${window.CalendarioConfig?.storeUrl || "/apicola/calendario"}`;
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
            const res = await fetch(`${window.CalendarioConfig?.baseUrl || "/apicola/calendario"}/${id}/toggle-status`, {
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
            const res = await fetch(`${window.CalendarioConfig?.baseUrl || "/apicola/calendario"}/${id}`, {
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
