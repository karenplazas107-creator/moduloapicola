/**
 * JavaScript para Gestión de Movimientos de Inventario
 * Diseño en 2 Columnas: Registro, Resumen, Historial y Factura de Movimiento
 * SENA APÍCOLA
 */

(function () {
    const config = window.MovimientosConfig || {};
    let productos = config.productos || [];
    let comprobanteActual = config.comprobanteActual || null;
    let historial = config.historial || [];
    let tipoActual = 'Salida'; // Por defecto según diseño
    let draftProducts = []; // Lista de productos acumulados para el movimiento

    document.addEventListener('DOMContentLoaded', () => {
        initResumen();
        renderDraftTable();
        initProductoSelector();
    });

    // =========================================================================
    // CONTROL DEL TIPO (ENTRADA / SALIDA)
    // =========================================================================
    function setTipo(tipo) {
        tipoActual = tipo;
        const btnEntrada = document.getElementById('btnToggleEntrada');
        const btnSalida = document.getElementById('btnToggleSalida');
        const sumBadge = document.getElementById('sumTipoBadge');

        if (btnEntrada && btnSalida) {
            if (tipo === 'Entrada') {
                btnEntrada.classList.add('active');
                btnSalida.classList.remove('active');
            } else {
                btnSalida.classList.add('active');
                btnEntrada.classList.remove('active');
            }
        }

        if (sumBadge) {
            sumBadge.innerHTML = `<i class="fa-solid fa-arrow-right-arrow-left"></i> ${tipo}`;
            if (tipo === 'Entrada') {
                sumBadge.style.background = '#059669';
            } else {
                sumBadge.style.background = '#286043';
            }
        }
    }

    // =========================================================================
    // FECHA Y BODEGA
    // =========================================================================
    function handleFechaChange(val) {
        const sumFecha = document.getElementById('sumFechaText');
        if (!sumFecha || !val) return;
        const parts = val.split('-');
        if (parts.length === 3) {
            sumFecha.textContent = `${parts[2]}/${parts[1]}/${parts[0]}`;
        } else {
            sumFecha.textContent = val;
        }
    }

    function getBodegaActual() {
        const sel = document.getElementById('selectBodega');
        if (!sel) return 'Bodega Central';
        if (sel.value === '__nueva__') {
            const input = document.getElementById('inputNombreNuevaBodega');
            return input ? (input.value.trim() || 'Nueva Bodega') : 'Nueva Bodega';
        }
        return sel.value || 'Bodega Central';
    }

    function handleBodegaChange(val) {
        const wrapNueva = document.getElementById('seccionNuevaBodegaWrap');
        const inputNueva = document.getElementById('inputNombreNuevaBodega');
        const sumBodega = document.getElementById('sumBodegaText');

        if (val === '__nueva__') {
            if (wrapNueva) wrapNueva.style.display = 'block';
            if (inputNueva) {
                inputNueva.required = true;
                inputNueva.focus();
                inputNueva.oninput = () => {
                    if (sumBodega) sumBodega.textContent = inputNueva.value.trim() || 'Nueva Bodega';
                };
            }
            if (sumBodega) sumBodega.textContent = 'Nueva Bodega';
        } else {
            if (wrapNueva) wrapNueva.style.display = 'none';
            if (inputNueva) inputNueva.required = false;
            if (sumBodega) sumBodega.textContent = val;
        }

        // Actualizar el selector de productos filtrado por esta bodega
        initProductoSelector();
    }

    function initResumen() {
        const fechaInput = document.getElementById('inputFecha');
        if (fechaInput && fechaInput.value) {
            handleFechaChange(fechaInput.value);
        }
        const bodegaSelect = document.getElementById('selectBodega');
        if (bodegaSelect) {
            const sumBodega = document.getElementById('sumBodegaText');
            if (sumBodega) sumBodega.textContent = bodegaSelect.value;
        }
        setTipo(tipoActual);
    }

    function initProductoSelector() {
        const sel = document.getElementById('selectProducto');
        if (!sel) return;

        const bodega = getBodegaActual().toLowerCase();
        sel.innerHTML = '<option value="">Selecciona un producto</option>';

        // Filtrar productos por bodega seleccionada o mostrar todos si es nueva
        const prods = productos.filter(p => {
            const b = (p.bodega || '').toLowerCase();
            return b.includes(bodega) || bodega.includes(b);
        });

        const listToDisplay = prods.length > 0 ? prods : productos;

        listToDisplay.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.dataset.nombre = p.nombre;
            opt.dataset.stock = p.cantidad;
            opt.dataset.unidad = p.unidad_medida || 'unidad';
            opt.textContent = `${p.nombre} (Stock: ${p.cantidad} ${p.unidad_medida || 'unidad'})`;
            sel.appendChild(opt);
        });

        const optManual = document.createElement('option');
        optManual.value = '__manual__';
        optManual.textContent = '+ Escribir otro producto...';
        sel.appendChild(optManual);

        sel.onchange = () => {
            const wrapManual = document.getElementById('seccionNuevoProductoTexto');
            const inputManual = document.getElementById('inputNombreProductoManual');
            if (sel.value === '__manual__') {
                if (wrapManual) wrapManual.style.display = 'block';
                if (inputManual) {
                    inputManual.required = true;
                    inputManual.focus();
                }
            } else {
                if (wrapManual) wrapManual.style.display = 'none';
                if (inputManual) inputManual.required = false;
            }
        };
    }

    // =========================================================================
    // LISTA DE PRODUCTOS A REGISTRAR (RESUMEN INFERIOR)
    // =========================================================================
    function agregarProductoADraft() {
        const selProducto = document.getElementById('selectProducto');
        const inputManual = document.getElementById('inputNombreProductoManual');
        const inputCantidad = document.getElementById('inputCantidad');
        const inputObservaciones = document.getElementById('inputObservaciones');

        let prodId = null;
        let nombre = '';
        let stockDisponible = 9999;
        let unidad = 'unidad';

        if (selProducto.value === '__manual__') {
            nombre = inputManual ? inputManual.value.trim() : '';
        } else if (selProducto.value) {
            prodId = selProducto.value;
            const opt = selProducto.options[selProducto.selectedIndex];
            nombre = opt.dataset.nombre || opt.textContent.split('(')[0].trim();
            stockDisponible = parseInt(opt.dataset.stock) || 0;
            unidad = opt.dataset.unidad || 'unidad';
        }

        if (!nombre) {
            Swal.fire({
                icon: 'warning',
                title: 'Selecciona un producto',
                text: 'Por favor selecciona o escribe el producto que deseas agregar.',
                confirmButtonColor: '#286043'
            });
            return;
        }

        const cantidad = parseInt(inputCantidad.value);
        if (!cantidad || cantidad <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cantidad inválida',
                text: 'Ingresa una cantidad mayor a 0.',
                confirmButtonColor: '#286043'
            });
            inputCantidad.focus();
            return;
        }

        const bodega = getBodegaActual();
        if (tipoActual === 'Salida' && prodId && cantidad > stockDisponible) {
            Swal.fire({
                icon: 'error',
                title: 'Stock insuficiente',
                text: `Solo hay ${stockDisponible} ${unidad} disponibles de "${nombre}" en ${bodega}.`,
                confirmButtonColor: '#286043'
            });
            return;
        }

        const obs = inputObservaciones ? inputObservaciones.value.trim() : '';

        // Agregar al arreglo
        draftProducts.push({
            id: Date.now() + Math.random(),
            inventario_id: prodId,
            nombre: nombre,
            cantidad: cantidad,
            observaciones: obs || `${tipoActual} de bodega`,
            stockDisponible: stockDisponible,
            unidad: unidad
        });

        // Limpiar inputs de producto para el siguiente
        selProducto.value = '';
        if (inputManual) inputManual.value = '';
        const wrapManual = document.getElementById('seccionNuevoProductoTexto');
        if (wrapManual) wrapManual.style.display = 'none';
        inputCantidad.value = '';
        if (inputObservaciones) inputObservaciones.value = '';

        renderDraftTable();
    }

    function removeDraftItem(index) {
        draftProducts.splice(index, 1);
        renderDraftTable();
    }

    function renderDraftTable() {
        const tbody = document.getElementById('draftTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (draftProducts.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; padding: 1.75rem 1rem; color: #94a3b8; font-size: 0.85rem;">
                        <i class="fa-solid fa-cart-flatbed" style="font-size: 1.4rem; display: block; margin-bottom: 6px; opacity: 0.6;"></i>
                        No hay productos agregados todavía.<br>Selecciona un producto y cantidad arriba y pulsa <strong>"+ Agregar producto"</strong>.
                    </td>
                </tr>
            `;
            return;
        }

        draftProducts.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight: 700; color: #111827;">${item.nombre}</td>
                <td style="text-align: center; font-weight: 800; color: #111827;">${item.cantidad}</td>
                <td style="text-align: center;">
                    <button type="button" class="mov-btn-trash-sm" title="Quitar este producto" onclick="window.MovimientosApp.removeDraftItem(${idx})">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limpiarFormulario() {
        document.getElementById('formMovimiento').reset();
        document.getElementById('inputFecha').value = new Date().toISOString().split('T')[0];
        setTipo('Salida');
        handleBodegaChange(document.getElementById('selectBodega').value);
        draftProducts = [];
        renderDraftTable();
    }

    // =========================================================================
    // REGISTRO DEL MOVIMIENTO (BOTÓN "REGISTRAR MOVIMIENTO")
    // =========================================================================
    async function submitMovimiento() {
        // Si hay datos en los inputs superiores y no se ha pulsado "+ Agregar producto", agregarlo automáticamente
        const selProducto = document.getElementById('selectProducto');
        const inputCantidad = document.getElementById('inputCantidad');

        if (selProducto && selProducto.value && inputCantidad && parseInt(inputCantidad.value) > 0) {
            agregarProductoADraft();
        }

        if (draftProducts.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin productos',
                text: 'Agrega al menos un producto con su cantidad usando el botón "+ Agregar producto".',
                confirmButtonColor: '#286043'
            });
            return;
        }

        const fecha = document.getElementById('inputFecha').value;
        const bodega = getBodegaActual();
        const tipo = tipoActual;

        const payload = {
            fecha: fecha,
            bodega: bodega,
            tipo: tipo,
            observaciones: `${tipo} de bodega`,
            productos: draftProducts.map(p => ({
                inventario_id: p.inventario_id,
                nombre: p.nombre,
                cantidad: p.cantidad,
                observaciones: p.observaciones || `${tipo} de bodega`
            }))
        };

        const btnSubmit = document.getElementById('btnSubmitMovimiento');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Registrando...`;

        try {
            const resp = await fetch(config.storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json();

            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'Error al procesar el registro.');
            }

            // Éxito: Notificar
            Swal.fire({
                icon: 'success',
                title: '¡Movimiento Registrado!',
                html: `Se generó la factura <strong>N.° ${data.movimiento.numero_comprobante}</strong> correctamente.<br>El inventario ha sido actualizado.`,
                confirmButtonColor: '#286043'
            });

            // Actualizar comprobante actual y mostrar la factura
            comprobanteActual = data.movimiento;
            historial.unshift(data.movimiento);
            renderFactura(data.movimiento);
            renderHistorialTable();

            // Limpiar formulario y borrador para el siguiente movimiento
            draftProducts = [];
            renderDraftTable();
            document.getElementById('inputCantidad').value = '';
            document.getElementById('inputObservaciones').value = '';
            document.getElementById('selectProducto').value = '';

        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#286043'
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `Registrar movimiento`;
        }
    }

    // =========================================================================
    // HISTORIAL Y FACTURA DE MOVIMIENTO (COLUMNA DERECHA)
    // =========================================================================
    function renderHistorialTable(filtroTipo = 'all') {
        const tbody = document.getElementById('historialTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        let filtrados = historial;
        if (filtroTipo && filtroTipo !== 'all') {
            filtrados = historial.filter(h => h.tipo === filtroTipo);
        }

        if (filtrados.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                        <i class="fa-regular fa-folder-open" style="font-size: 1.5rem; display: block; margin-bottom: 6px;"></i>
                        No hay movimientos registrados para este filtro.
                    </td>
                </tr>
            `;
            return;
        }

        filtrados.forEach(h => {
            const tr = document.createElement('tr');
            tr.className = `mov-hist-row ${comprobanteActual && comprobanteActual.id == h.id ? 'selected' : ''}`;
            tr.onclick = () => seleccionarMovimiento(h.id);

            let fechaStr = h.fecha ? (typeof h.fecha === 'string' ? h.fecha.substring(0, 10) : h.fecha) : '-';
            if (fechaStr.includes('-')) {
                const parts = fechaStr.split('-');
                if (parts.length === 3) fechaStr = `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            const badgeClass = h.tipo === 'Entrada' ? 'entrada' : 'salida';
            const total = h.total_unidades || h.total_productos || (h.detalles ? h.detalles.length : 0);

            tr.innerHTML = `
                <td style="font-weight: 600;">${fechaStr}</td>
                <td>
                    <span class="mov-badge-tipo ${badgeClass}">
                        ${h.tipo}
                    </span>
                </td>
                <td>${h.bodega}</td>
                <td style="text-align: center; font-weight: 700;">${total}</td>
                <td>${h.responsable || 'Karen Plazas'}</td>
                <td style="text-align: right;">
                    <i class="fa-solid fa-chevron-right mov-chevron-icon"></i>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    function filtrarHistorial(tipo) {
        renderHistorialTable(tipo);
    }

    async function seleccionarMovimiento(id) {
        let mov = historial.find(h => h.id == id);
        if (!mov || !mov.detalles) {
            try {
                const resp = await fetch(`${config.showUrl}/${id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();
                if (data.success && data.comprobante) {
                    mov = data.comprobante;
                    const idx = historial.findIndex(h => h.id == id);
                    if (idx !== -1) historial[idx] = mov;
                }
            } catch (e) {
                console.error(e);
            }
        }

        if (mov) {
            comprobanteActual = mov;
            renderFactura(mov);
            renderPosTicket(mov);

            // Actualizar clase selected en la tabla
            document.querySelectorAll('.mov-hist-row').forEach(r => r.classList.remove('selected'));
            renderHistorialTable(document.getElementById('selectFiltroHistorial')?.value || 'all');
        }
    }

    function renderFactura(comp) {
        if (!comp) return;

        const elNum = document.getElementById('invNumeroText');
        const elTipo = document.getElementById('invTipoText');
        const elFecha = document.getElementById('invFechaText');
        const elBodega = document.getElementById('invBodegaText');
        const elUsuario = document.getElementById('invUsuarioText');
        const elTotal = document.getElementById('invTotalProductosNum');
        const elSig = document.getElementById('invSigName');
        const tbody = document.getElementById('invoiceTableBody');

        if (elNum) elNum.textContent = `N.° ${comp.numero_comprobante}`;
        if (elTipo) elTipo.textContent = comp.tipo;

        let fechaStr = comp.fecha ? (typeof comp.fecha === 'string' ? comp.fecha.substring(0, 10) : comp.fecha) : '-';
        if (fechaStr.includes('-')) {
            const parts = fechaStr.split('-');
            if (parts.length === 3) fechaStr = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        if (elFecha) elFecha.textContent = fechaStr;
        if (elBodega) elBodega.textContent = comp.bodega;
        if (elUsuario) elUsuario.textContent = comp.responsable || 'Karen Plazas';
        if (elSig) elSig.textContent = comp.responsable || 'Karen Michelle Plazas Rojas';

        const total = comp.total_unidades || comp.total_productos || (comp.detalles ? comp.detalles.reduce((acc, d) => acc + parseInt(d.cantidad || 0), 0) : 0);
        if (elTotal) elTotal.textContent = total;

        if (tbody) {
            tbody.innerHTML = '';
            const detalles = comp.detalles || [];
            if (detalles.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 1.5rem; color: #94a3b8;">Sin productos en esta factura</td></tr>`;
                return;
            }

            detalles.forEach((det, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="color: #64748b; font-weight: 700;">${idx + 1}</td>
                    <td style="font-weight: 700; color: #111827;">${det.producto_nombre}</td>
                    <td style="font-weight: 800; color: #111827;">${det.cantidad}</td>
                    <td style="color: #4b5563;">${det.observaciones || (comp.tipo + ' de bodega')}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    // =========================================================================
    // MODAL FLOTANTE Y COMPROBANTE TIPO POS (80mm)
    // =========================================================================
    function abrirModalPos(id = null) {
        let mov = null;
        if (id) {
            mov = historial.find(h => h.id == id);
        }
        if (!mov) {
            mov = comprobanteActual || (historial.length > 0 ? historial[0] : null);
        }

        if (!mov) {
            Swal.fire({
                icon: 'info',
                title: 'Sin comprobante',
                text: 'No hay ningún movimiento registrado o seleccionado para imprimir.',
                confirmButtonColor: '#286043'
            });
            return;
        }

        comprobanteActual = mov;
        renderPosTicket(mov);
        const backdrop = document.getElementById('modalPosBackdrop');
        if (backdrop) backdrop.classList.add('is-open');
    }

    function cerrarModalPos() {
        const backdrop = document.getElementById('modalPosBackdrop');
        if (backdrop) backdrop.classList.remove('is-open');
    }

    function renderPosTicket(comp) {
        if (!comp) return;

        const posNum = document.getElementById('posNumero');
        const posTipo = document.getElementById('posTipo');
        const posFecha = document.getElementById('posFecha');
        const posBodega = document.getElementById('posBodega');
        const posUsuario = document.getElementById('posUsuario');
        const posSig = document.getElementById('posSigName');
        const posTotalItems = document.getElementById('posTotalItems');
        const posTotalUnidades = document.getElementById('posTotalUnidades');
        const tbody = document.getElementById('posTicketTableBody');

        if (posNum) posNum.textContent = comp.numero_comprobante || '0001-2026';
        if (posTipo) {
            posTipo.textContent = (comp.tipo || 'SALIDA').toUpperCase();
            posTipo.className = `pos-meta-badge ${(comp.tipo || 'salida').toLowerCase()}`;
        }

        let fechaStr = comp.fecha ? (typeof comp.fecha === 'string' ? comp.fecha.substring(0, 10) : comp.fecha) : '-';
        if (fechaStr.includes('-')) {
            const parts = fechaStr.split('-');
            if (parts.length === 3) fechaStr = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        if (posFecha) posFecha.textContent = fechaStr;
        if (posBodega) posBodega.textContent = comp.bodega || 'Bodega Central';
        if (posUsuario) posUsuario.textContent = comp.responsable || 'Karen Plazas';
        if (posSig) posSig.textContent = comp.responsable || 'Karen Michelle Plazas Rojas';

        const detalles = comp.detalles || [];
        const totalItems = detalles.length;
        const totalUnidades = comp.total_unidades || comp.total_productos || detalles.reduce((acc, d) => acc + parseInt(d.cantidad || 0), 0);

        if (posTotalItems) posTotalItems.textContent = totalItems;
        if (posTotalUnidades) posTotalUnidades.textContent = totalUnidades;

        if (tbody) {
            tbody.innerHTML = '';
            if (detalles.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; padding: 10px; color: #666;">Sin artículos registrados</td></tr>`;
                return;
            }

            detalles.forEach(d => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight: 800; text-align: center;">${d.cantidad}</td>
                    <td style="font-weight: 700; word-break: break-word;">${d.producto_nombre}</td>
                    <td style="text-align: right; font-size: 9px; color: #4b5563;">${d.observaciones || (comp.tipo + ' bodega')}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    function imprimirTicketDirecto() {
        const ticketElem = document.getElementById('posTicketPaper');
        if (!ticketElem) return;

        const iframe = document.getElementById('posPrintIframe');
        if (!iframe) {
            window.print();
            return;
        }

        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Ticket POS - ${comprobanteActual ? comprobanteActual.numero_comprobante : 'Comprobante'}</title>
                <style>
                    @page {
                        size: 80mm auto;
                        margin: 2mm 3mm;
                    }
                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }
                    body {
                        font-family: 'Courier New', Courier, monospace, 'Segoe UI', Tahoma, sans-serif;
                        font-size: 11px;
                        line-height: 1.35;
                        color: #000000;
                        background: #ffffff;
                        width: 72mm;
                        margin: 0 auto;
                        padding: 4px 2px;
                    }
                    .pos-ticket-header {
                        text-align: center;
                        margin-bottom: 8px;
                    }
                    .pos-ticket-logo img {
                        width: 40px;
                        height: 40px;
                        object-fit: contain;
                        display: block;
                        margin: 0 auto 4px;
                    }
                    .pos-ticket-org {
                        font-weight: 800;
                        font-size: 11px;
                        letter-spacing: 0.5px;
                    }
                    .pos-ticket-title {
                        font-weight: 900;
                        font-size: 13px;
                    }
                    .pos-ticket-center {
                        font-size: 9.5px;
                    }
                    .pos-ticket-slogan {
                        font-size: 9px;
                        font-style: italic;
                        margin-top: 2px;
                    }
                    .pos-ticket-divider-double {
                        border-top: 2px dashed #000;
                        margin: 6px 0;
                    }
                    .pos-ticket-divider-solid {
                        border-top: 1px solid #000;
                        margin: 6px 0;
                    }
                    .pos-ticket-divider-dash {
                        border-top: 1px dashed #000;
                        margin: 6px 0;
                    }
                    .pos-ticket-meta {
                        font-size: 10.5px;
                        margin: 4px 0;
                    }
                    .pos-meta-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 2px;
                    }
                    .pos-meta-lbl {
                        font-weight: 700;
                    }
                    .pos-meta-val {
                        font-weight: 800;
                        text-align: right;
                    }
                    .pos-meta-badge {
                        font-weight: 900;
                        text-decoration: underline;
                    }
                    table.pos-ticket-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 6px 0;
                        font-size: 10px;
                    }
                    table.pos-ticket-table th {
                        border-bottom: 1px dashed #000;
                        padding: 3px 0;
                        font-weight: 800;
                        font-size: 10px;
                    }
                    table.pos-ticket-table td {
                        padding: 3px 0;
                        vertical-align: top;
                    }
                    .pos-ticket-totals {
                        font-size: 11px;
                        margin: 4px 0;
                    }
                    .pos-total-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 2px;
                    }
                    .pos-total-row.major {
                        font-size: 12px;
                        font-weight: 900;
                    }
                    .pos-ticket-signature {
                        text-align: center;
                        margin-top: 18px;
                        padding-top: 8px;
                    }
                    .pos-sig-line {
                        width: 70%;
                        margin: 0 auto 4px;
                        border-top: 1px solid #000;
                    }
                    .pos-sig-name {
                        font-size: 10px;
                        font-weight: 800;
                    }
                    .pos-sig-title {
                        font-size: 9px;
                        font-weight: 600;
                    }
                    .pos-sig-entity {
                        font-size: 8px;
                    }
                    .pos-ticket-footer {
                        text-align: center;
                        font-size: 8.5px;
                        margin-top: 12px;
                        line-height: 1.3;
                        border-top: 1px dashed #000;
                        padding-top: 6px;
                    }
                </style>
            </head>
            <body>
                ${ticketElem.innerHTML}
            </body>
            </html>
        `);
        iframeDoc.close();

        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 250);
    }

    function imprimirFactura() {
        abrirModalPos();
    }

    // Tecla Escape para cerrar modal flotante
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarModalPos();
        }
    });

    // Exponer al scope global para eventos del DOM
    window.MovimientosApp = {
        setTipo,
        handleFechaChange,
        handleBodegaChange,
        agregarProductoADraft,
        removeDraftItem,
        limpiarFormulario,
        submitMovimiento,
        seleccionarMovimiento,
        filtrarHistorial,
        imprimirFactura,
        abrirModalPos,
        cerrarModalPos,
        imprimirTicketDirecto
    };

})();
