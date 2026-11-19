let colmenasList = window.AprendizColmenasConfig?.colmenasList || [];

    document.addEventListener('DOMContentLoaded', () => {
        renderColmenasTable();
        updateColmenasMetrics();
    });

    function showToast(message) {
        const toast = document.getElementById('apToast');
        const toastText = document.getElementById('apToastText');
        if (toast && toastText) {
            toastText.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
            return;
        }
        let fb = document.getElementById('apFallbackToast');
        if (!fb) {
            fb = document.createElement('div');
            fb.id = 'apFallbackToast';
            fb.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#064e3b;color:#ffffff;padding:12px 20px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:99999;font-weight:600;font-size:0.9rem;display:flex;align-items:center;gap:10px;transition:all 0.3s ease;';
            document.body.appendChild(fb);
        }
        fb.innerHTML = '<i class="fas fa-check-circle" style="color:#34d399;"></i> ' + message;
        fb.style.opacity = '1';
        fb.style.transform = 'translateY(0)';
        setTimeout(() => {
            fb.style.opacity = '0';
            fb.style.transform = 'translateY(10px)';
        }, 3500);
    }

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

    function renderColmenasTable() {
        const searchVal = (document.getElementById('colmenaSearchInput')?.value || '').toLowerCase().trim();
        const apiarioVal = document.getElementById('colmenaApiarioFilter')?.value || 'all';
        const estadoVal = document.getElementById('colmenaEstadoFilter')?.value || 'all';

        const filtered = colmenasList.filter(item => {
            const matchSearch = item.code.toLowerCase().includes(searchVal) ||
                                (item.tipoAbeja && item.tipoAbeja.toLowerCase().includes(searchVal)) ||
                                item.responsable.toLowerCase().includes(searchVal);
            const matchApiario = (apiarioVal === 'all') || (item.apiario.includes(apiarioVal) || apiarioVal.includes(item.apiario));
            const matchEstado = (estadoVal === 'all') || (item.estado === estadoVal);
            return matchSearch && matchApiario && matchEstado;
        });

        const tbody = document.getElementById('colmenasTableBody');
        tbody.innerHTML = '';

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display:block;"></i>
                        No se encontraron colmenas con los criterios seleccionados.
                    </td>
                </tr>
            `;
            document.getElementById('colmenasShownCount').textContent = '0';
            return;
        }

        document.getElementById('colmenasShownCount').textContent = filtered.length;

        filtered.forEach(item => {
            let statusClass = 'activa';
            if (item.estado === 'En revisión') statusClass = 'en-revision';
            else if (item.estado === 'Inactiva') statusClass = 'inactiva';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <span class="ap-code-badge">${item.code}</span>
                </td>
                <td style="color: #334155;">${item.apiario}</td>
                <td style="color: #1e293b; font-weight: 500;">${item.tipoAbeja || 'Apis mellifera'}</td>
                <td style="color: #475569;">${item.responsable}</td>
                <td style="color: #64748b; font-size: 0.82rem;">${item.instalacion}</td>
                <td style="color: #64748b; font-size: 0.82rem;">${item.ultimaVisita}</td>
                <td>
                    <span class="ap-status-badge ${statusClass}">
                        ${item.estado}
                    </span>
                </td>
                <td style="text-align: center;">
                    <div class="ap-actions-group" style="justify-content: center;">
                        <button type="button" class="ap-action-btn view" onclick="openColmenaDetailModal(${item.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterColmenas() {
        renderColmenasTable();
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

    function openNewColmenaModal() {
        document.getElementById('modalColmenaTitle').textContent = 'Nueva colmena';
        document.getElementById('modalColmenaId').value = '';
        
        const maxNum = colmenasList.reduce((max, c) => {
            const match = c.code.match(/COL-(\d+)/);
            if (match) {
                const num = parseInt(match[1], 10);
                return num > max ? num : max;
            }
            return max;
        }, 0);
        document.getElementById('modalColmenaCode').value = '';
        document.getElementById('modalColmenaFecha').value = '';

        const apiarioSelect = document.getElementById('modalColmenaApiario');
        if (apiarioSelect.options.length > 0) {
            apiarioSelect.selectedIndex = 0;
        }

        const tipoSelect = document.getElementById('modalColmenaTipoAbeja');
        tipoSelect.value = tipoSelect.options[0]?.value || 'Apis mellifera';
        document.getElementById('containerOtroTipoAbeja').style.display = 'none';
        document.getElementById('modalColmenaOtroTipoAbeja').value = '';

        document.getElementById('modalColmenaEstado').value = 'Activa';
        document.getElementById('modalColmenaResponsable').value = '';

        document.getElementById('modalColmenaBackdrop').classList.add('is-open');
    }

    function openEditColmenaModal(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;

        document.getElementById('modalColmenaTitle').textContent = 'Editar colmena';
        document.getElementById('modalColmenaId').value = item.id;
        document.getElementById('modalColmenaCode').value = item.code;

        // Apiario
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

        const estadoSelect = document.getElementById('modalColmenaEstado');
        if (item.estado === 'Inactiva') {
            if (!estadoSelect.querySelector('option[value="Inactiva"]')) {
                const opt = document.createElement('option');
                opt.value = 'Inactiva';
                opt.textContent = 'Inactiva (Solo lectura)';
                opt.disabled = true;
                estadoSelect.appendChild(opt);
            }
        } else {
            const inactivaOpt = estadoSelect.querySelector('option[value="Inactiva"]');
            if (inactivaOpt) inactivaOpt.remove();
        }
        estadoSelect.value = item.estado;
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
            const url = id ? `${window.AprendizColmenasConfig?.baseUrl || "/apicola/aprendiz/colmenas"}/${id}` : `${window.AprendizColmenasConfig?.storeUrl || "/apicola/aprendiz/colmenas"}`;
            const method = id ? 'PUT' : 'POST';

            const payload = {
                code: code,
                apiario_name: apiario,
                tipo_abeja: tipoAbeja,
                responsable: responsable,
                installation_date: rawFecha,
                last_visit_date: rawFecha,
                status: estado,
                notes: id ? undefined : 'Colmena registrada por el aprendiz apícola.'
            };

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.AprendizColmenasConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
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
        document.getElementById('colmenaDetailTipoAbeja').textContent = item.tipoAbeja || 'Apis mellifera';
        document.getElementById('colmenaDetailResp').textContent = item.responsable;
        document.getElementById('colmenaDetailInstalacion').textContent = item.instalacion;
        document.getElementById('colmenaDetailLastVisit').textContent = item.ultimaVisita || 'Reciente';
        document.getElementById('colmenaDetailNotas').textContent = item.notas || 'Sin novedades clínicas registradas.';

        const badge = document.getElementById('colmenaDetailStatusBadge');
        badge.textContent = item.estado;
        badge.className = 'ap-status-badge ' + (item.estado === 'Activa' ? 'activa' : (item.estado === 'En revisión' ? 'en-revision' : 'inactiva'));

        document.getElementById('modalColmenaDetailBackdrop').classList.add('is-open');
    }

    function closeColmenaDetailModal() {
        document.getElementById('modalColmenaDetailBackdrop').classList.remove('is-open');
    }

    function openInspeccionModal(id) {
        const item = colmenasList.find(c => c.id === id);
        if (!item) return;
        showToast(`Ficha de inspección formativa para "${item.code}" abierta.`);
        openColmenaDetailModal(id);
    }

    async function toggleColmenaStatus(id) {
        showToast('El rol de aprendiz no tiene permisos para desactivar o modificar el estado de las colmenas.');
    }

    // ==================== DESACTIVAR COLMENA (NO PERMITIDO A APRENDIZ) ====================
    async function desactivarColmena(id, code) {
        showToast('El rol de aprendiz no tiene permisos para desactivar colmenas.');
    }
