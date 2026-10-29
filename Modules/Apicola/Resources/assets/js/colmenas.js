let colmenasList = window.ColmenasConfig?.colmenasList || [];

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

            const inspectionUrl = `${window.ColmenasConfig?.baseUrl || "/apicola/colmenas"}/${encodeURIComponent(c.code)}/inspecciones`;

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
                <td style="text-align: center;">
                    <div class="ap-actions-group" style="justify-content: center;">
                        <button type="button" class="ap-action-btn view" onclick="openColmenaDetailModal(${c.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                        <button type="button" class="ap-action-btn edit" onclick="openEditColmenaModal(${c.id})" title="Editar colmena"><i class="fas fa-pen-to-square"></i></button>
                        <a href="${inspectionUrl}" class="ap-action-btn" style="color: #0284c7;" title="Ir a inspección de ${c.code}"><i class="fas fa-notes-medical"></i></a>
                        ${isInactive 
                            ? `<button type="button" class="ap-action-btn activate" style="color: #059669;" onclick="toggleColmenaStatus(${c.id})" title="Activar colmena"><i class="fas fa-circle-check"></i></button>` 
                            : `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444;" onclick="desactivarColmena(${c.id}, '${c.code}')" title="Desactivar colmena"><i class="fas fa-ban"></i></button>`
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
            const url = id ? `${window.ColmenasConfig?.baseUrl || "/apicola/colmenas"}/${id}` : `${window.ColmenasConfig?.storeUrl || "/apicola/colmenas"}`;
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
                    'X-CSRF-TOKEN': (window.ColmenasConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
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

        document.getElementById('linkIrInspeccionColmena').href = `${window.ColmenasConfig?.baseUrl || "/apicola/colmenas"}/${encodeURIComponent(item.code)}/inspecciones`;

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
            const response = await fetch(`${window.ColmenasConfig?.baseUrl || "/apicola/colmenas"}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.ColmenasConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
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
            const response = await fetch(`${window.ColmenasConfig?.baseUrl || "/apicola/colmenas"}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.ColmenasConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
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
