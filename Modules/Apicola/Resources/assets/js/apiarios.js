let apiariosList = window.ApiariosConfig?.apiariosList || [];

    const MAPBOX_ACCESS_TOKEN = window.ApiariosConfig?.mapboxToken || '';
    if (typeof mapboxgl !== 'undefined' && MAPBOX_ACCESS_TOKEN) {
        mapboxgl.accessToken = MAPBOX_ACCESS_TOKEN;
    }

    let modalMap = null;
    let modalMarker = null;
    let modalMarkerBadgeEl = null;
    let modalMapIsSatellite = false;

    let generalMap = null;
    let generalMarkers = [];

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
                            ? `<button type="button" class="ap-action-btn deactivate" style="color: #f59e0b;" onclick="toggleApiarioStatus(${item.id})" title="Desactivar apiario"><i class="fas fa-ban"></i></button>`
                            : `<button type="button" class="ap-action-btn activate" style="color: #059669;" onclick="toggleApiarioStatus(${item.id})" title="Activar apiario"><i class="fas fa-circle-check"></i></button>`
                        }
                        <button type="button" class="ap-action-btn delete" style="color: #ef4444;" onclick="eliminarApiario(${item.id}, '${item.nombre}')" title="Eliminar apiario"><i class="fas fa-trash-can"></i></button>
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
        
        const inputNombre = document.getElementById('modalApiarioNombre');
        if (inputNombre) {
            inputNombre.value = '';
            inputNombre.style.borderColor = '#cbd5e1';
        }
        const errorNombre = document.getElementById('errorNumeroNombre');
        if (errorNombre) errorNombre.style.display = 'none';

        // Reset selector de responsable: solo SERGIO BARRERA y Añadir a otro
        const selectResp = document.getElementById('modalApiarioRespSelect');
        const hiddenResp = document.getElementById('modalApiarioResp');
        const containerOtro = document.getElementById('containerOtroRespApiario');
        const inputOtro = document.getElementById('modalApiarioOtroRespInput');
        const errorResp = document.getElementById('errorNumeroResp');

        if (containerOtro) containerOtro.style.display = 'none';
        if (inputOtro) {
            inputOtro.value = '';
            inputOtro.style.borderColor = '#cbd5e1';
        }
        if (errorResp) errorResp.style.display = 'none';

        if (selectResp) {
            selectResp.innerHTML = `
                <option value="" disabled selected>Selecciona un responsable...</option>
                <option value="SERGIO BARRERA">SERGIO BARRERA</option>
                <option value="__otro__" style="color: #047857; font-weight: 700;">➕ Añadir a otro...</option>
            `;
            selectResp.value = '';
        }
        if (hiddenResp) hiddenResp.value = '';

        document.getElementById('modalApiarioColmenas').value = '0';

        // Coordenadas iniciales por defecto: SENA La Angostura, Campoalegre (verificado en Google Maps)
        const defaultLat = 2.614811;
        const defaultLng = -75.360875;
        const inputLat = document.getElementById('modalApiarioLat');
        const inputLng = document.getElementById('modalApiarioLng');
        if (inputLat) {
            inputLat.value = defaultLat.toFixed(6);
            inputLat.style.borderColor = '#cbd5e1';
        }
        if (inputLng) {
            inputLng.value = defaultLng.toFixed(6);
            inputLng.style.borderColor = '#cbd5e1';
        }
        const errLat = document.getElementById('errorLatCoords');
        const errLng = document.getElementById('errorLngCoords');
        if (errLat) errLat.style.display = 'none';
        if (errLng) errLng.style.display = 'none';

        setApiarioEstado('Activo');

        document.getElementById('modalApiarioBackdrop').classList.add('is-open');

        setTimeout(() => {
            initModalMap(defaultLat, defaultLng);
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
        const inputNombre = document.getElementById('modalApiarioNombre');
        if (inputNombre) {
            inputNombre.value = item.nombre || '';
            inputNombre.style.borderColor = '#cbd5e1';
        }
        const errorNombre = document.getElementById('errorNumeroNombre');
        if (errorNombre) errorNombre.style.display = 'none';

        // Asignar responsable en el selector o registrarlo si no existe
        const selectResp = document.getElementById('modalApiarioRespSelect');
        const hiddenResp = document.getElementById('modalApiarioResp');
        const containerOtro = document.getElementById('containerOtroRespApiario');
        const inputOtro = document.getElementById('modalApiarioOtroRespInput');
        const errorResp = document.getElementById('errorNumeroResp');

        if (containerOtro) containerOtro.style.display = 'none';
        if (inputOtro) {
            inputOtro.value = '';
            inputOtro.style.borderColor = '#cbd5e1';
        }
        if (errorResp) errorResp.style.display = 'none';

        const respVal = item.responsable || '';
        if (hiddenResp) hiddenResp.value = respVal;

        if (selectResp) {
            selectResp.innerHTML = `
                <option value="" disabled>Selecciona un responsable...</option>
                <option value="SERGIO BARRERA">SERGIO BARRERA</option>
                <option value="__otro__" style="color: #047857; font-weight: 700;">➕ Añadir a otro...</option>
            `;
            if (respVal && respVal.toLowerCase() !== 'sergio barrera') {
                const opt = new Option(respVal, respVal, true, true);
                const otroOpt = Array.from(selectResp.options).find(o => o.value === '__otro__');
                if (otroOpt) selectResp.insertBefore(opt, otroOpt);
                else selectResp.appendChild(opt);
            }
            selectResp.value = respVal;
        }

        document.getElementById('modalApiarioColmenas').value = item.colmenas || 0;
        
        const latVal = item.lat || 2.614811;
        const lngVal = item.lng || -75.360875;
        const editLatInput = document.getElementById('modalApiarioLat');
        const editLngInput = document.getElementById('modalApiarioLng');
        if (editLatInput) {
            editLatInput.value = parseFloat(latVal).toFixed(6);
            editLatInput.style.borderColor = '#cbd5e1';
        }
        if (editLngInput) {
            editLngInput.value = parseFloat(lngVal).toFixed(6);
            editLngInput.style.borderColor = '#cbd5e1';
        }
        const errEditLat = document.getElementById('errorLatCoords');
        const errEditLng = document.getElementById('errorLngCoords');
        if (errEditLat) errEditLat.style.display = 'none';
        if (errEditLng) errEditLng.style.display = 'none';

        setApiarioEstado(item.estado || 'Activo');

        document.getElementById('modalApiarioBackdrop').classList.add('is-open');

        setTimeout(() => {
            initModalMap(parseFloat(latVal), parseFloat(lngVal));
        }, 200);
    }

    // El mapa base de Mapbox no conoce nombres de apiarios (no son sitios registrados en su
    // base de datos de lugares), así que cada marcador dibuja su propia etiqueta con el nombre.
    function buildApiarioMarkerElement(color, label) {
        const el = document.createElement('div');
        el.style.display = 'flex';
        el.style.flexDirection = 'column';
        el.style.alignItems = 'center';
        el.style.cursor = 'pointer';

        const badge = document.createElement('div');
        badge.style.cssText = 'background:#b45309; color:#fff; font-size:0.68rem; font-weight:800; padding:3px 9px; border-radius:20px; white-space:nowrap; box-shadow:0 2px 6px rgba(0,0,0,0.25); margin-bottom:3px; border:1.5px solid #fff;';
        badge.innerHTML = label ? ('<i class="fas fa-tag"></i> ' + label) : '';
        badge.style.display = label ? 'inline-block' : 'none';
        el.appendChild(badge);

        const dot = document.createElement('div');
        dot.style.cssText = `width:18px; height:18px; border-radius:50%; background:${color}; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3);`;
        el.appendChild(dot);

        el._badgeEl = badge;
        return el;
    }

    function initModalMap(lat, lng) {
        const container = document.getElementById('modalMapContainer');
        if (!container) return;

        if (typeof mapboxgl === 'undefined') {
            console.warn('Mapbox GL JS no está cargado todavía.');
            return;
        }

        if (!modalMap) {
            modalMap = new mapboxgl.Map({
                container: 'modalMapContainer',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [lng, lat],
                zoom: 14
            });

            modalMap.addControl(new mapboxgl.NavigationControl(), 'top-right');

            modalMap.on('click', (e) => {
                modalMarker.setLngLat(e.lngLat);
                syncMapCoordsToInputs(e.lngLat.lat, e.lngLat.lng);
            });
        } else {
            modalMap.resize();
            modalMap.setCenter([lng, lat]);
            modalMap.setZoom(14);
        }

        if (modalMarker) {
            modalMarker.remove();
        }

        const nombreActual = (document.getElementById('modalApiarioNombre')?.value || '').trim();
        const markerEl = buildApiarioMarkerElement('#059669', nombreActual || null);

        modalMarker = new mapboxgl.Marker({
            element: markerEl,
            draggable: true
        })
            .setLngLat([lng, lat])
            .addTo(modalMap);

        modalMarkerBadgeEl = markerEl._badgeEl;

        modalMarker.on('dragend', () => {
            const pos = modalMarker.getLngLat();
            syncMapCoordsToInputs(pos.lat, pos.lng);
        });
    }

    function updateModalMarkerLabel() {
        if (!modalMarkerBadgeEl) return;
        const nombre = (document.getElementById('modalApiarioNombre')?.value || '').trim();
        if (nombre) {
            modalMarkerBadgeEl.innerHTML = '<i class="fas fa-tag"></i> ' + nombre;
            modalMarkerBadgeEl.style.display = 'inline-block';
        } else {
            modalMarkerBadgeEl.style.display = 'none';
        }
    }

    function toggleModalMapStyle() {
        if (!modalMap) return;
        modalMapIsSatellite = !modalMapIsSatellite;
        modalMap.setStyle(modalMapIsSatellite
            ? 'mapbox://styles/mapbox/satellite-streets-v12'
            : 'mapbox://styles/mapbox/streets-v12');

        const btn = document.getElementById('btnToggleModalMapStyle');
        if (btn) {
            btn.innerHTML = modalMapIsSatellite
                ? '<i class="fas fa-map"></i> Ver calles'
                : '<i class="fas fa-satellite"></i> Ver satélite';
        }
    }

    function syncMapCoordsToInputs(lat, lng) {
        const inputLat = document.getElementById('modalApiarioLat');
        const inputLng = document.getElementById('modalApiarioLng');
        if (inputLat) {
            inputLat.value = lat.toFixed(6);
            validarCoordenada(inputLat, 'errorLatCoords', -90, 90);
        }
        if (inputLng) {
            inputLng.value = lng.toFixed(6);
            validarCoordenada(inputLng, 'errorLngCoords', -180, 180);
        }
    }

    let generalMapIsSatellite = false;

    function openGeneralMapModal() {
        document.getElementById('modalGeneralMapBackdrop').classList.add('is-open');

        setTimeout(() => {
            if (typeof mapboxgl === 'undefined') {
                console.warn('Mapbox GL JS no está cargado todavía.');
                return;
            }

            if (!generalMap) {
                generalMap = new mapboxgl.Map({
                    container: 'generalMapContainer',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [-75.360875, 2.614811],
                    zoom: 13
                });

                generalMap.addControl(new mapboxgl.NavigationControl(), 'top-right');
            } else {
                generalMap.resize();
            }

            renderApiarioMarkersGeneral();
        }, 200);
    }

    function renderApiarioMarkersGeneral() {
        if (!generalMap) return;

        generalMarkers.forEach(marker => marker.remove());
        generalMarkers = [];

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
                    <div style="display:flex; gap:6px; margin-top:8px;">
                        <button type="button" onclick="openEditApiarioModal(${a.id})" style="flex:1; font-size:0.72rem; font-weight:700; padding:5px 6px; border-radius:6px; border:1px solid #cbd5e1; background:#f8fafc; cursor:pointer;">
                            <i class="fas fa-pen-to-square"></i> Editar
                        </button>
                        <button type="button" onclick="eliminarApiario(${a.id}, '${a.nombre}')" style="flex:1; font-size:0.72rem; font-weight:700; padding:5px 6px; border-radius:6px; border:1px solid #fecaca; background:#fef2f2; color:#dc2626; cursor:pointer;">
                            <i class="fas fa-trash-can"></i> Eliminar
                        </button>
                    </div>
                </div>
            `;

            const marker = new mapboxgl.Marker({
                element: buildApiarioMarkerElement(markerColor, a.nombre)
            })
                .setLngLat([a.lng, a.lat])
                .setPopup(new mapboxgl.Popup({ offset: 20 }).setHTML(popupContent))
                .addTo(generalMap);

            generalMarkers.push(marker);
        });
    }

    function toggleGeneralMapStyle() {
        if (!generalMap) return;
        generalMapIsSatellite = !generalMapIsSatellite;
        generalMap.setStyle(generalMapIsSatellite
            ? 'mapbox://styles/mapbox/satellite-streets-v12'
            : 'mapbox://styles/mapbox/streets-v12');

        const btn = document.getElementById('btnToggleMapStyle');
        if (btn) {
            btn.innerHTML = generalMapIsSatellite
                ? '<i class="fas fa-map"></i> Ver calles'
                : '<i class="fas fa-satellite"></i> Ver satélite';
        }
    }

    function closeGeneralMapModal() {
        document.getElementById('modalGeneralMapBackdrop').classList.remove('is-open');
    }

    function validarTextoSinNumeros(input, errorSpanId) {
        const errorSpan = document.getElementById(errorSpanId);
        if (/\d/.test(input.value)) {
            if (errorSpan) errorSpan.style.display = 'block';
            input.style.borderColor = '#dc2626';
        } else {
            if (errorSpan) errorSpan.style.display = 'none';
            input.style.borderColor = '#cbd5e1';
        }
    }

    function validarCoordenada(input, errorSpanId, min, max) {
        const errorSpan = document.getElementById(errorSpanId);
        const val = input.value.trim();

        if (val === '') {
            if (errorSpan) errorSpan.style.display = 'none';
            input.style.borderColor = '#cbd5e1';
            return false;
        }

        // Si contiene letras o caracteres prohibidos (solo números, signo - y .)
        if (/[a-zA-Z]/.test(val) || !/^-?\d*(\.\d*)?$/.test(val)) {
            if (errorSpan) {
                errorSpan.innerHTML = '<i class="fas fa-circle-exclamation"></i> Coordenada inválida: no se permiten letras ni texto, solo números reales.';
                errorSpan.style.display = 'block';
            }
            input.style.borderColor = '#dc2626';
            return false;
        }

        if (val === '-' || val === '.') {
            return false;
        }

        const num = parseFloat(val);
        if (isNaN(num) || num < min || num > max) {
            if (errorSpan) {
                errorSpan.innerHTML = `<i class="fas fa-circle-exclamation"></i> Coordenada fuera de rango (${min} a ${max}).`;
                errorSpan.style.display = 'block';
            }
            input.style.borderColor = '#dc2626';
            return false;
        }

        if (errorSpan) errorSpan.style.display = 'none';
        input.style.borderColor = '#10b981';

        return true;
    }

    function handleApiarioRespSelect(val) {
        const container = document.getElementById('containerOtroRespApiario');
        const inputOtro = document.getElementById('modalApiarioOtroRespInput');
        const hidden = document.getElementById('modalApiarioResp');
        const errorSpan = document.getElementById('errorNumeroResp');

        if (errorSpan) errorSpan.style.display = 'none';
        if (inputOtro) inputOtro.style.borderColor = '#cbd5e1';

        if (val === '__otro__') {
            if (container) container.style.display = 'block';
            if (inputOtro) {
                inputOtro.value = '';
                inputOtro.focus();
            }
            if (hidden) hidden.value = '';
        } else {
            if (container) container.style.display = 'none';
            if (hidden) hidden.value = val;
        }
    }

    function confirmarOtroResponsableApiario() {
        const inputOtro = document.getElementById('modalApiarioOtroRespInput');
        const select = document.getElementById('modalApiarioRespSelect');
        const hidden = document.getElementById('modalApiarioResp');
        const container = document.getElementById('containerOtroRespApiario');
        const errorSpan = document.getElementById('errorNumeroResp');

        const nuevo = (inputOtro?.value || '').trim();
        if (!nuevo) {
            Swal.fire({
                icon: 'warning',
                title: 'Nombre requerido',
                text: 'Por favor escribe el nombre del responsable para poder guardarlo.',
                confirmButtonColor: '#286043'
            });
            inputOtro?.focus();
            return false;
        }

        // Validación estricta: NO NÚMEROS EN RESPONSABLE
        if (/\d/.test(nuevo)) {
            if (errorSpan) errorSpan.style.display = 'block';
            inputOtro.style.borderColor = '#dc2626';
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'No se permiten números en el responsable. Solo se aceptan datos reales de personas.',
                confirmButtonColor: '#286043'
            });
            inputOtro.focus();
            return false;
        }

        const regexSoloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$/;
        if (!regexSoloLetras.test(nuevo) || nuevo.length < 3) {
            if (errorSpan) errorSpan.style.display = 'block';
            inputOtro.style.borderColor = '#dc2626';
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'Por favor ingresa un nombre real de persona válido (solo letras).',
                confirmButtonColor: '#286043'
            });
            inputOtro.focus();
            return false;
        }

        if (errorSpan) errorSpan.style.display = 'none';
        inputOtro.style.borderColor = '#10b981';

        let existingOpt = Array.from(select.options).find(o => o.value.toLowerCase() === nuevo.toLowerCase());
        if (!existingOpt) {
            const opt = new Option(nuevo, nuevo, true, true);
            const otroOpt = Array.from(select.options).find(o => o.value === '__otro__');
            if (otroOpt) select.insertBefore(opt, otroOpt);
            else select.appendChild(opt);
        }

        select.value = nuevo;
        if (hidden) hidden.value = nuevo;
        if (container) container.style.display = 'none';
        return true;
    }

    function closeNewApiarioModal() {
        document.getElementById('modalApiarioBackdrop').classList.remove('is-open');
    }

    async function saveApiarioForm(e) {
        e.preventDefault();

        const id = document.getElementById('modalApiarioId').value;
        const inputNombre = document.getElementById('modalApiarioNombre');
        const nombre = (inputNombre ? inputNombre.value : '').trim();
        const errorNombre = document.getElementById('errorNumeroNombre');

        // Validación estricta de Nombre
        if (!nombre) {
            Swal.fire({
                icon: 'warning',
                title: 'Nombre requerido',
                text: 'Por favor ingresa el nombre del apiario.',
                confirmButtonColor: '#286043'
            });
            if (inputNombre) inputNombre.focus();
            return;
        }

        if (/\d/.test(nombre)) {
            if (errorNombre) errorNombre.style.display = 'block';
            if (inputNombre) {
                inputNombre.style.borderColor = '#dc2626';
                inputNombre.focus();
            }
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'El nombre del apiario no puede contener números, solo letras o texto descriptivo.',
                confirmButtonColor: '#286043'
            });
            return;
        }

        if (nombre.length < 3) {
            Swal.fire({
                icon: 'warning',
                title: 'Nombre muy corto',
                text: 'El nombre del apiario debe tener al menos 3 caracteres.',
                confirmButtonColor: '#286043'
            });
            if (inputNombre) inputNombre.focus();
            return;
        }

        const selectResp = document.getElementById('modalApiarioRespSelect');
        const inputOtro = document.getElementById('modalApiarioOtroRespInput');
        const hiddenResp = document.getElementById('modalApiarioResp');
        const errorResp = document.getElementById('errorNumeroResp');

        if (!selectResp || !selectResp.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Responsable requerido',
                text: 'Debes seleccionar a SERGIO BARRERA o añadir a otro responsable para poder guardar el apiario.',
                confirmButtonColor: '#286043'
            });
            if (selectResp) selectResp.focus();
            return;
        }

        if (selectResp.value === '__otro__') {
            const nuevo = (inputOtro?.value || '').trim();
            if (!nuevo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nombre requerido',
                    text: 'Debes escribir el nombre del nuevo responsable para poder guardar el apiario.',
                    confirmButtonColor: '#286043'
                });
                if (inputOtro) inputOtro.focus();
                return;
            }
            const confirmOk = confirmarOtroResponsableApiario();
            if (!confirmOk) {
                return;
            }
        }

        let responsable = (hiddenResp && hiddenResp.value ? hiddenResp.value : (selectResp && selectResp.value !== '__otro__' ? selectResp.value : '')).trim();

        if (!responsable || responsable === '__otro__') {
            Swal.fire({
                icon: 'warning',
                title: 'Responsable requerido',
                text: 'Debes seleccionar a SERGIO BARRERA o añadir a otro responsable para poder guardar el apiario.',
                confirmButtonColor: '#286043'
            });
            if (selectResp) selectResp.focus();
            return;
        }

        // Validación estricta: NO NÚMEROS EN RESPONSABLE, SOLO NOMBRES REALES DE PERSONAS
        if (/\d/.test(responsable)) {
            if (errorResp) errorResp.style.display = 'block';
            Swal.fire({
                icon: 'error',
                title: 'Responsable inválido',
                text: 'No se permiten números en el responsable. Solo se aceptan datos reales de personas.',
                confirmButtonColor: '#286043'
            });
            if (inputOtro) {
                inputOtro.style.borderColor = '#dc2626';
                inputOtro.focus();
            }
            return;
        }

        const regexSoloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$/;
        if (!regexSoloLetras.test(responsable) || responsable.length < 3) {
            if (errorResp) errorResp.style.display = 'block';
            Swal.fire({
                icon: 'error',
                title: 'Responsable inválido',
                text: 'El responsable debe ser un nombre real de persona válido (solo letras, mínimo 3 caracteres).',
                confirmButtonColor: '#286043'
            });
            if (inputOtro) {
                inputOtro.style.borderColor = '#dc2626';
                inputOtro.focus();
            }
            return;
        }

        const estado = document.getElementById('modalApiarioEstado').value;
        const colmenas = parseInt(document.getElementById('modalApiarioColmenas').value) || 0;

        const inputLat = document.getElementById('modalApiarioLat');
        const inputLng = document.getElementById('modalApiarioLng');
        const latVal = (inputLat ? inputLat.value : '').trim();
        const lngVal = (inputLng ? inputLng.value : '').trim();

        if (!latVal || !lngVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Coordenadas requeridas',
                text: 'Haz clic en el mapa para marcar la ubicación geográfica del apiario.',
                confirmButtonColor: '#286043'
            });
            return;
        }

        // Validación: NO LETRAS EN LATITUD NI LONGITUD
        if (/[a-zA-Z]/.test(latVal)) {
            const errLat = document.getElementById('errorLatCoords');
            if (errLat) errLat.style.display = 'block';
            if (inputLat) {
                inputLat.style.borderColor = '#dc2626';
                inputLat.focus();
            }
            Swal.fire({
                icon: 'error',
                title: 'Latitud inválida',
                text: 'La latitud no puede contener letras ni texto, solo números reales (Ej: 2.685700).',
                confirmButtonColor: '#286043'
            });
            return;
        }

        if (/[a-zA-Z]/.test(lngVal)) {
            const errLng = document.getElementById('errorLngCoords');
            if (errLng) errLng.style.display = 'block';
            if (inputLng) {
                inputLng.style.borderColor = '#dc2626';
                inputLng.focus();
            }
            Swal.fire({
                icon: 'error',
                title: 'Longitud inválida',
                text: 'La longitud no puede contener letras ni texto, solo números reales (Ej: -75.324100).',
                confirmButtonColor: '#286043'
            });
            return;
        }

        const lat = parseFloat(latVal);
        const lng = parseFloat(lngVal);

        if (isNaN(lat) || lat < -90 || lat > 90) {
            const errLat = document.getElementById('errorLatCoords');
            if (errLat) errLat.style.display = 'block';
            if (inputLat) {
                inputLat.style.borderColor = '#dc2626';
                inputLat.focus();
            }
            Swal.fire({
                icon: 'error',
                title: 'Latitud fuera de rango',
                text: 'La latitud debe ser un número real válido entre -90 y 90.',
                confirmButtonColor: '#286043'
            });
            return;
        }

        if (isNaN(lng) || lng < -180 || lng > 180) {
            const errLng = document.getElementById('errorLngCoords');
            if (errLng) errLng.style.display = 'block';
            if (inputLng) {
                inputLng.style.borderColor = '#dc2626';
                inputLng.focus();
            }
            Swal.fire({
                icon: 'error',
                title: 'Longitud fuera de rango',
                text: 'La longitud debe ser un número real válido entre -180 y 180.',
                confirmButtonColor: '#286043'
            });
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
            renderApiarioMarkersGeneral();
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
            renderApiarioMarkersGeneral();
        } catch (error) {
            console.error('Error al cambiar estado:', error);
            alert('Error al cambiar el estado: ' + error.message);
        }
    }

    async function eliminarApiario(id, nombre) {
        const result = await Swal.fire({
            title: '¿Eliminar apiario?',
            text: `¿Estás seguro de que deseas eliminar permanentemente el apiario "${nombre}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

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

            const resData = await response.json();
            if (!response.ok || !resData.success) {
                throw new Error(resData.message || 'Error al eliminar el apiario.');
            }

            // Remover de la lista en memoria
            const idx = apiariosList.findIndex(a => a.id == id);
            if (idx !== -1) {
                apiariosList.splice(idx, 1);
            }

            if (resData.metrics) {
                document.getElementById('metricTotalApiarios').textContent = resData.metrics.total ?? apiariosList.length;
                document.getElementById('metricActivos').textContent = resData.metrics.activos ?? apiariosList.filter(a => a.estado === 'Activo').length;
                document.getElementById('metricInactivos').textContent = resData.metrics.inactivos ?? apiariosList.filter(a => a.estado === 'Inactivo').length;
                document.getElementById('metricColmenas').textContent = resData.metrics.colmenas ?? 0;
                document.getElementById('apTotalCount').textContent = resData.metrics.total ?? apiariosList.length;
            } else {
                updateMetricCards();
            }

            renderApiariosTable();
            renderApiarioMarkersGeneral();

            Swal.fire({
                icon: 'success',
                title: 'Apiario eliminado',
                text: resData.message || `El apiario "${nombre}" fue eliminado exitosamente.`,
                confirmButtonColor: '#286043',
                timer: 2000
            });
        } catch (error) {
            console.error('Error al eliminar apiario:', error);
            Swal.fire({
                icon: 'error',
                title: 'No se pudo eliminar',
                text: error.message,
                confirmButtonColor: '#286043'
            });
        }
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
    window.eliminarApiario = eliminarApiario;
    window.openDetailModal = openDetailModal;
    window.closeDetailModal = closeDetailModal;
    window.openGeneralMapModal = openGeneralMapModal;
    window.closeGeneralMapModal = closeGeneralMapModal;
    window.toggleGeneralMapStyle = toggleGeneralMapStyle;
    window.toggleModalMapStyle = toggleModalMapStyle;
    window.updateModalMarkerLabel = updateModalMarkerLabel;
    window.handleApiarioRespSelect = handleApiarioRespSelect;
    window.confirmarOtroResponsableApiario = confirmarOtroResponsableApiario;
    window.validarTextoSinNumeros = validarTextoSinNumeros;
    window.validarCoordenada = validarCoordenada;

