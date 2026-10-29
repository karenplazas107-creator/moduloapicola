// Datos inyectados desde el backend
    let inventarioData = window.InventarioConfig?.inventarioData || [];
    let selectedItemId = null;

    document.addEventListener('DOMContentLoaded', () => {
        renderInventarioTable();
        actualizarMetricas();
    });

    // ==================== RENDERIZADO Y FILTROS ====================
    function renderInventarioTable() {
        const search = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
        const bodega = document.getElementById('bodegaFilter')?.value || 'todas';
        const categoria = document.getElementById('categoriaFilter')?.value || 'todas';
        const estado = document.getElementById('estadoFilter')?.value || 'todos';

        const filtered = inventarioData.filter(item => {
            const matchSearch = item.nombre.toLowerCase().includes(search) ||
                                item.codigo.toLowerCase().includes(search);
            const matchBodega = (bodega === 'todas') || (item.bodega === bodega);
            const matchCategoria = (categoria === 'todas') || (item.categoria.toLowerCase() === categoria.toLowerCase());
            const matchEstado = (estado === 'todos') || (item.estado === estado);

            return matchSearch && matchBodega && matchCategoria && matchEstado;
        });

        const tbody = document.getElementById('inventarioTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        document.getElementById('shownCount').textContent = filtered.length;
        document.getElementById('totalCount').textContent = inventarioData.length;

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                        No se encontraron registros de inventario con los filtros aplicados.
                    </td>
                </tr>
            `;
            return;
        }

        filtered.forEach(item => {
            const tr = document.createElement('tr');
            const isStockBajo = parseInt(item.cantidad) <= parseInt(item.stock_minimo);
            const isInactive = (item.estado === 'Inactivo');

            if (isStockBajo) {
                tr.className = 'row-stock-bajo';
            }

            let estadoBadge = isStockBajo 
                ? `<span class="inv-badge-bajo">Stock bajo</span>`
                : `<span class="inv-badge-disponible">Disponible</span>`;

            if (isInactive) {
                estadoBadge += ` <span style="display:inline-block; padding: 2px 8px; border-radius: 50px; font-size: 0.72rem; font-weight: 800; background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; margin-left: 4px;">Inactivo</span>`;
            }

            const unidad = item.unidad_medida || 'unidad';

            tr.innerHTML = `
                <td style="font-weight: 500; color: #475569;">
                    ${item.codigo}
                </td>
                <td style="font-weight: 800; color: #0f172a;">
                    ${item.nombre}
                </td>
                <td style="color: #475569;">
                    ${item.categoria}
                </td>
                <td style="color: #475569;">
                    ${item.bodega}
                </td>
                <td>
                    <strong style="color: #0f172a; font-weight: 800;">${item.cantidad}</strong> 
                    <span style="color: #64748b; font-size: 0.82rem;">${unidad}</span>
                </td>
                <td style="color: #475569; font-weight: 500;">
                    ${item.stock_minimo}
                </td>
                <td>
                    ${estadoBadge}
                </td>
                <td style="text-align: center;">
                    <div class="inv-action-links" style="display: flex; gap: 8px; align-items: center; justify-content: center;">
                        <button type="button" class="inv-action-link" onclick="abrirModalMovimiento(${item.id})" title="Registrar movimiento"><i class="fas fa-boxes-packing"></i></button>
                        <button type="button" class="inv-action-link" onclick="abrirModalHistorial(${item.id})" title="Ver historial de movimientos"><i class="fas fa-clock-rotate-left"></i></button>
                        ${isInactive
                            ? `<button type="button" class="inv-action-link activate" style="color: #059669;" onclick="toggleInventarioStatus(${item.id})" title="Activar elemento"><i class="fas fa-circle-check"></i></button>`
                            : `<button type="button" class="inv-action-link danger" style="color: #ef4444;" onclick="desactivarInventarioItem(${item.id}, '${item.codigo}', '${item.nombre}')" title="Desactivar elemento"><i class="fas fa-ban"></i></button>`
                        }
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filtrarInventario() {
        renderInventarioTable();
    }

    function actualizarMetricas() {
        const total = inventarioData.length;
        const bodegaCentral = inventarioData.filter(i => (i.bodega || '').includes('Bodega central')).length;
        const bodegaApiario = inventarioData.filter(i => (i.bodega || '').includes('apiario') || (i.bodega || '').includes('Apiario')).length;
        const alertaStock = inventarioData.filter(i => parseInt(i.cantidad) <= parseInt(i.stock_minimo)).length;

        document.getElementById('cardTotalElementos').textContent = total;
        document.getElementById('cardBodegaCentral').textContent = bodegaCentral;
        document.getElementById('cardBodegaApiario').textContent = bodegaApiario;
        document.getElementById('cardAlertaStock').textContent = alertaStock;
    }

    // ==================== MODAL: AGREGAR AL INVENTARIO ====================
    function abrirModalAgregar() {
        document.getElementById('formAgregarInventario').reset();
        seleccionarTipo('Herramienta');
        document.getElementById('selectElemento').value = 'Traje apícola';
        toggleRegistrarNuevo('Traje apícola');
        document.getElementById('selectBodega').value = 'Bodega central';
        document.getElementById('inputCantidadInicial').value = 0;
        document.getElementById('inputStockMinimo').value = 5;
        document.getElementById('modalAgregarBackdrop').classList.add('is-open');
    }

    function cerrarModalAgregar() {
        document.getElementById('modalAgregarBackdrop').classList.remove('is-open');
    }

    function seleccionarTipo(tipo) {
        document.getElementById('inputTipo').value = tipo;
        const btnHerramienta = document.getElementById('btnHerramienta');
        const btnInsumo = document.getElementById('btnInsumo');

        if (tipo === 'Herramienta') {
            btnHerramienta.classList.add('active');
            btnInsumo.classList.remove('active');
        } else {
            btnInsumo.classList.add('active');
            btnHerramienta.classList.remove('active');
        }
    }

    function toggleRegistrarNuevo(val) {
        const seccion = document.getElementById('seccionRegistrarNuevo');
        const inputNombreNuevo = document.getElementById('inputNombreNuevo');
        if (val === '__nuevo__') {
            seccion.style.display = 'block';
            inputNombreNuevo.required = true;
            inputNombreNuevo.focus();
        } else {
            seccion.style.display = 'none';
            inputNombreNuevo.required = false;
        }
    }

    async function guardarInventario(e) {
        e.preventDefault();

        const tipo = document.getElementById('inputTipo').value;
        const elementoSelect = document.getElementById('selectElemento').value;
        const isNuevo = (elementoSelect === '__nuevo__');
        const nombreNuevo = document.getElementById('inputNombreNuevo').value.trim();
        const unidadMedida = isNuevo ? document.getElementById('selectUnidadMedida').value : 'unidad';
        const bodega = document.getElementById('selectBodega').value;
        const cantidadInicial = parseInt(document.getElementById('inputCantidadInicial').value) || 0;
        const stockMinimo = parseInt(document.getElementById('inputStockMinimo').value) || 0;

        const payload = {
            tipo: tipo,
            elemento: elementoSelect,
            nombre_nuevo: isNuevo ? nombreNuevo : '',
            unidad_medida: unidadMedida,
            bodega: bodega,
            cantidad_inicial: cantidadInicial,
            stock_minimo: stockMinimo,
        };

        const btnSubmit = document.getElementById('btnSubmitAgregar');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando...';

        try {
            const resp = await fetch(`${window.InventarioConfig?.storeUrl || "/apicola/inventario"}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.InventarioConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                inventarioData.push(data.data);
                cerrarModalAgregar();
                renderInventarioTable();
                actualizarMetricas();
                showToast(data.message || 'Elemento agregado con éxito');
            } else {
                alert(data.message || 'Error al guardar el elemento.');
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al servidor al guardar elemento.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar';
        }
    }

    // ==================== MODAL: REGISTRAR MOVIMIENTO ====================
    function abrirModalMovimiento(id) {
        const item = inventarioData.find(i => i.id === id);
        if (!item) return;

        selectedItemId = item.id;
        document.getElementById('formMovimiento').reset();
        document.getElementById('movItemId').value = item.id;
        document.getElementById('movItemNombre').textContent = item.nombre;
        document.getElementById('movItemCodigo').textContent = item.codigo;
        document.getElementById('movItemBodega').textContent = item.bodega;
        document.getElementById('movItemDisponible').textContent = `${item.cantidad} ${item.unidad_medida || 'unidad'}`;
        document.getElementById('movFecha').value = new Date().toISOString().split('T')[0];
        setTipoMovimiento('Entrada');

        document.getElementById('modalMovimientoBackdrop').classList.add('is-open');
    }

    function cerrarModalMovimiento() {
        document.getElementById('modalMovimientoBackdrop').classList.remove('is-open');
    }

    function setTipoMovimiento(tipo) {
        document.getElementById('movTipo').value = tipo;
        const btnE = document.getElementById('btnTipoEntrada');
        const btnS = document.getElementById('btnTipoSalida');
        const btnA = document.getElementById('btnTipoAjuste');

        btnE.className = 'tipo-pill-btn' + (tipo === 'Entrada' ? ' active' : '');
        btnS.className = 'tipo-pill-btn' + (tipo === 'Salida' ? ' active' : '');
        btnA.className = 'tipo-pill-btn' + (tipo === 'Ajuste' ? ' active' : '');
    }

    async function guardarMovimiento(e) {
        e.preventDefault();
        const id = document.getElementById('movItemId').value;
        const payload = {
            tipo: document.getElementById('movTipo').value,
            cantidad: parseInt(document.getElementById('movCantidad').value) || 1,
            fecha: document.getElementById('movFecha').value,
            motivo: document.getElementById('movMotivo').value.trim(),
            responsable: document.getElementById('movResponsable').value.trim(),
        };

        const btnSubmit = document.getElementById('btnSubmitMovimiento');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Confirmando...';

        try {
            const resp = await fetch(`${window.InventarioConfig?.baseUrl || "/apicola/inventario"}/${id}/movimiento`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.InventarioConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                const idx = inventarioData.findIndex(i => i.id == id);
                if (idx !== -1) {
                    inventarioData[idx] = data.data.item;
                }

                cerrarModalMovimiento();
                renderInventarioTable();
                actualizarMetricas();
                showToast(data.message || 'Movimiento registrado con éxito');
            } else {
                alert(data.message || 'Error al registrar el movimiento.');
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al registrar el movimiento.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Confirmar movimiento';
        }
    }

    // ==================== MODAL: HISTORIAL ====================
    async function abrirModalHistorial(id) {
        selectedItemId = id;
        const item = inventarioData.find(i => i.id === id);
        if (!item) return;

        document.getElementById('histCategoria').textContent = (item.categoria || 'HERRAMIENTA').toUpperCase();
        document.getElementById('histNombre').textContent = item.nombre;
        document.getElementById('histCodigo').textContent = item.codigo;
        document.getElementById('histBodega').textContent = item.bodega;
        document.getElementById('histDisponible').textContent = `${item.cantidad} ${item.unidad_medida || 'unidad'}`;

        const isStockBajo = parseInt(item.cantidad) <= parseInt(item.stock_minimo);
        const badge = document.getElementById('histEstadoBadge');
        if (isStockBajo) {
            badge.className = 'inv-badge-bajo';
            badge.textContent = 'Stock bajo';
        } else {
            badge.className = 'inv-badge-disponible';
            badge.textContent = 'Disponible';
        }

        const tbody = document.getElementById('historialTableBody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 1.25rem;">Cargando historial...</td></tr>';
        document.getElementById('modalHistorialBackdrop').classList.add('is-open');

        try {
            const resp = await fetch(`${window.InventarioConfig?.baseUrl || "/apicola/inventario"}/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const res = await resp.json();

            if (res.success && res.data.movimientos) {
                tbody.innerHTML = '';
                if (res.data.movimientos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Sin movimientos registrados aún.</td></tr>';
                    return;
                }

                res.data.movimientos.forEach(m => {
                    const tr = document.createElement('tr');
                    const badgeColor = m.tipo === 'Entrada' ? '#059669' : (m.tipo === 'Salida' ? '#dc2626' : '#0284c7');
                    const sign = m.tipo === 'Entrada' ? '+' : (m.tipo === 'Salida' ? '-' : '=');

                    tr.innerHTML = `
                        <td style="font-weight: 600; color: #64748b;">${m.fecha ? m.fecha.substring(0, 10) : '-'}</td>
                        <td>
                            <strong style="color: ${badgeColor}; font-size: 0.8rem;">
                                ${sign} ${m.tipo}
                            </strong>
                        </td>
                        <td style="font-weight: 800; color: #0f172a;">${m.cantidad}</td>
                        <td style="font-weight: 600; color: #64748b;">${m.stock_nuevo}</td>
                        <td>${m.motivo || '-'}</td>
                        <td style="color: #64748b;">${m.responsable || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        } catch (e) {
            console.error(e);
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #ef4444; padding: 1rem;">No se pudo cargar el historial.</td></tr>';
        }
    }

    function cerrarModalHistorial() {
        document.getElementById('modalHistorialBackdrop').classList.remove('is-open');
    }

    function abrirMovimientoDesdeHistorial() {
        const id = selectedItemId;
        cerrarModalHistorial();
        if (id) {
            abrirModalMovimiento(id);
        }
    }

    async function toggleInventarioStatus(id) {
        try {
            const resp = await fetch(`${window.InventarioConfig?.baseUrl || "/apicola/inventario"}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.InventarioConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
                    'Accept': 'application/json'
                }
            });

            const data = await resp.json();
            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'Error al cambiar estado.');
            }

            const item = inventarioData.find(i => i.id === id);
            if (item) {
                item.estado = data.data.estado;
            }

            showToast(data.message || 'Estado actualizado con éxito.');
            renderInventarioTable();
            actualizarMetricas();
        } catch (err) {
            console.error(err);
            alert(err.message || 'Error al cambiar estado del elemento.');
        }
    }

    async function desactivarInventarioItem(id, codigo, nombre) {
        if (!confirm(`¿Estás seguro de que deseas desactivar "${codigo} - ${nombre}" del inventario?`)) {
            return;
        }

        try {
            const resp = await fetch(`${window.InventarioConfig?.baseUrl || "/apicola/inventario"}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.InventarioConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
                    'Accept': 'application/json'
                }
            });

            const data = await resp.json();
            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'Error al desactivar el elemento.');
            }

            const item = inventarioData.find(i => i.id === id);
            if (item) {
                item.estado = 'Inactivo';
            }
            showToast(data.message || `Elemento ${codigo} desactivado con éxito.`);
            renderInventarioTable();
            actualizarMetricas();
        } catch (err) {
            console.error(err);
            alert(err.message || 'Error al desactivar elemento del inventario.');
        }
    }

    // Cerrar modal al hacer clic en el backdrop
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('ap-modal-backdrop')) {
            e.target.classList.remove('is-open');
        }
    });
