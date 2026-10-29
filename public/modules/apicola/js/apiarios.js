let apiariosList = window.ApiariosConfig?.apiariosList || [];

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
                <td style="text-align: center;">
                    <div class="ap-actions-group" style="justify-content: center;">
                        <button type="button" class="ap-action-btn view" onclick="openDetailModal(${item.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                        <button type="button" class="ap-action-btn edit" onclick="openEditApiarioModal(${item.id})" title="Editar apiario"><i class="fas fa-pen-to-square"></i></button>
                        ${isActivo 
                            ? `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444;" onclick="desactivarApiario(${item.id}, '${item.nombre}')" title="Desactivar apiario"><i class="fas fa-ban"></i></button>`
                            : `<button type="button" class="ap-action-btn activate" style="color: #059669;" onclick="toggleApiarioStatus(${item.id})" title="Activar apiario"><i class="fas fa-circle-check"></i></button>`
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

    function getCsrfToken() {
        return window.ApiariosConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
    }

    function showToast(msg) {
        if (typeof window.showToast === 'function' && window.showToast !== showToast) {
            window.showToast(msg);
            return;
        }
        const toast = document.getElementById('apToast');
        const toastText = document.getElementById('apToastText');
        if (toast && toastText) {
            toastText.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
        } else {
            console.log(msg);
        }
    }

    function openNewApiarioModal() {
        document.getElementById('modalApiarioTitle').textContent = 'Nuevo apiario';
        const btnSave = document.getElementById('btnSaveApiario');
        if (btnSave) btnSave.textContent = 'Guardar apiario';
        document.getElementById('modalApiarioId').value = '';
        document.getElementById('modalApiarioNombre').value = '';
        document.getElementById('modalApiarioResp').value = '';
        document.getElementById('modalApiarioColmenas').value = '0';
        document.getElementById('modalApiarioLat').value = '';
        document.getElementById('modalApiarioLng').value = '';
        setApiarioEstado('Activo');

        document.getElementById('modalApiarioBackdrop').classList.add('is-open');

        setTimeout(() => {
            initModalMap(2.6857, -75.3241);
        }, 200);
    }

    function openEditApiarioModal(id) {
        const item = apiariosList.find(a => a.id == id);
        if (!item) {
            console.warn('Apiario no encontrado con id:', id);
            return;
        }

        document.getElementById('modalApiarioTitle').textContent = 'Editar apiario';
        const btnSave = document.getElementById('btnSaveApiario');
        if (btnSave) btnSave.textContent = 'Actualizar apiario';

        document.getElementById('modalApiarioId').value = item.id;
        document.getElementById('modalApiarioNombre').value = item.nombre || '';
        document.getElementById('modalApiarioResp').value = item.responsable || '';
        document.getElementById('modalApiarioColmenas').value = item.colmenas || 0;
        document.getElementById('modalApiarioLat').value = item.lat || 2.6857;
        document.getElementById('modalApiarioLng').value = item.lng || -75.3241;
        setApiarioEstado(item.estado || 'Activo');

        document.getElementById('modalApiarioBackdrop').classList.add('is-open');

        setTimeout(() => {
            initModalMap(item.lat || 2.6857, item.lng || -75.3241);
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
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (id ? 'Actualizando apiario...' : 'Guardando apiario...');

        const csrfToken = getCsrfToken();

        try {
            const url = id 
                ? `${window.ApiariosConfig?.baseUrl || "/apicola/apiarios"}/${id}` 
                : `${window.ApiariosConfig?.storeUrl || "/apicola/apiarios"}`;

            const payload = {
                nombre_apiario: nombre,
                responsable: responsable,
                latitud: lat,
                longitud: lng,
                estado: estado,
                colmenas: colmenas,
                _token: csrfToken
            };

            if (id) {
                payload._method = 'PUT';
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-HTTP-Method-Override': id ? 'PUT' : 'POST',
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
                const idx = apiariosList.findIndex(a => a.id == id);
                if (idx !== -1) {
                    apiariosList[idx] = result.data;
                } else {
                    apiariosList.unshift(result.data);
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
        const apiario = apiariosList.find(a => a.id == id);
        if (!apiario) return;

        const csrfToken = getCsrfToken();
        try {
            const response = await fetch(`${window.ApiariosConfig?.baseUrl || "/apicola/apiarios"}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: csrfToken })
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

        const csrfToken = getCsrfToken();
        try {
            const response = await fetch(`${window.ApiariosConfig?.baseUrl || "/apicola/apiarios"}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-HTTP-Method-Override': 'DELETE',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: csrfToken, _method: 'DELETE' })
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Error al desactivar el apiario.');
            }

            const item = apiariosList.find(a => a.id == id);
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
        const apiario = apiariosList.find(a => a.id == id);
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

    // Exponer globalmente en window para llamadas inline HTML
    window.openNewApiarioModal = openNewApiarioModal;
    window.openEditApiarioModal = openEditApiarioModal;
    window.closeNewApiarioModal = closeNewApiarioModal;
    window.saveApiarioForm = saveApiarioForm;
    window.setApiarioEstado = setApiarioEstado;
    window.filterApiarios = filterApiarios;
    window.toggleApiarioStatus = toggleApiarioStatus;
    window.desactivarApiario = desactivarApiario;
    window.openGeneralMapModal = openGeneralMapModal;
    window.closeGeneralMapModal = closeGeneralMapModal;
    window.openDetailModal = openDetailModal;
    window.closeDetailModal = closeDetailModal;

