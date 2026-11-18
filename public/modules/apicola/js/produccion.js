// ==================== SUBMÓDULO PRODUCCIÓN (cosechas de miel por colmena) ====================
(function () {
    const cfg = window.ProduccionConfig || {};
    let producciones = cfg.produccionesList || [];
    let activeColmena = cfg.selected || '';

    const esc = (t) => String(t ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const fmtKg = (n) => (parseFloat(n) || 0).toFixed(1).replace('.', ',');
    const csrf = () => cfg.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

    document.addEventListener('DOMContentLoaded', () => {
        renderProduccionesTable();
        updateMetrics();
        renderRelatedInspecciones();
    });

    // ---------------------------------------------------------------- filtros y tabla
    function parseDMY(str) {
        if (!str) return null;
        const p = str.split('/');
        return p.length === 3 ? new Date(`${p[2]}-${p[1]}-${p[0]}T00:00:00`) : null;
    }

    function getFiltered() {
        const ini = document.getElementById('filterFechaInicio')?.value;
        const fin = document.getElementById('filterFechaFin')?.value;
        const dIni = ini ? new Date(ini + 'T00:00:00') : null;
        const dFin = fin ? new Date(fin + 'T23:59:59') : null;

        return producciones.filter(p => {
            if (activeColmena && p.colmena !== activeColmena) return false;
            const d = parseDMY(p.fecha);
            if (dIni && d && d < dIni) return false;
            if (dFin && d && d > dFin) return false;
            return true;
        });
    }

    function renderProduccionesTable() {
        const tbody = document.getElementById('produccionesTableBody');
        if (!tbody) return;
        const items = getFiltered();

        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-jar" style="font-size: 1.5rem; margin-bottom: 0.5rem; display:block;"></i>
                        No se encontraron producciones registradas.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = items.map(p => {
            const inactiva = p.estado === 'Inactiva';
            return `
                <tr style="${inactiva ? 'opacity:0.65; background-color:#f8fafc;' : ''}">
                    <td style="font-weight: 700; color: #0f172a;">${esc(p.fecha)}</td>
                    <td style="color: #475569; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 0.85rem;">
                        ${esc(p.regNumber)}
                        ${inactiva ? '<span class="ap-status-badge inactiva" style="margin-left: 6px; font-size: 0.68rem; padding: 2px 7px;">Inactiva</span>' : ''}
                    </td>
                    <td style="color: #334155; font-weight: 600;">${esc(p.colmena)}</td>
                    <td style="color: #0f172a; font-weight: 700;">${fmtKg(p.mielKg)}</td>
                    <td style="color: #334155;">${esc(p.responsable)}</td>
                    <td style="text-align: center;">
                        <div class="ap-actions-group" style="justify-content: center;">
                            <button type="button" class="ap-action-btn view" onclick="openProduccionDetailModal(${p.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                            <button type="button" class="ap-action-btn edit" onclick="openEditProduccionModal(${p.id})" title="Editar producción"><i class="fas fa-pen-to-square"></i></button>
                            ${inactiva
                                ? `<button type="button" class="ap-action-btn activate" style="color: #059669;" onclick="toggleProduccionStatus(${p.id})" title="Activar producción"><i class="fas fa-circle-check"></i></button>`
                                : `<button type="button" class="ap-action-btn deactivate" style="color: #ef4444;" onclick="desactivarProduccion(${p.id})" title="Desactivar producción"><i class="fas fa-ban"></i></button>`}
                        </div>
                    </td>
                </tr>`;
        }).join('');
    }

    function updateMetrics() {
        const items = getFiltered();
        const activas = items.filter(p => p.estado !== 'Inactiva');
        const total = activas.reduce((s, p) => s + (parseFloat(p.mielKg) || 0), 0);

        document.getElementById('metricTotalProd').textContent = items.length;
        document.getElementById('metricMielTotal').textContent = fmtKg(total);
        document.getElementById('metricUltima').textContent = activas.length ? activas[0].fecha : '-';

        const ini = document.getElementById('filterFechaInicio')?.value;
        const fin = document.getElementById('filterFechaFin')?.value;
        document.getElementById('produccionesSummaryText').textContent = (ini && fin)
            ? `${items.length} producciones entre el ${ini} y el ${fin}`
            : `${items.length} producciones registradas`;
        document.getElementById('mielHarvestSummaryText').textContent = `Total cosechado: ${fmtKg(total)} kg`;
    }

    function filterProducciones() {
        renderProduccionesTable();
        updateMetrics();
    }

    function changeActiveColmena(code) {
        activeColmena = code || '';
        const set = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
        set('currentColmenaCode', activeColmena || 'Todas las colmenas');
        set('colmenaTag', activeColmena || 'GENERAL');
        set('metricColmenaSub', activeColmena ? `Colmena ${activeColmena}` : 'Todas las colmenas');
        set('historyColmenaTitle', activeColmena ? `Producción de la colmena ${activeColmena}` : 'Producción de todas las colmenas');
        renderProduccionesTable();
        updateMetrics();
        renderRelatedInspecciones();
    }

    // ---------------------------------------------------------------- relación con las inspecciones
    function renderRelatedInspecciones() {
        const box = document.getElementById('relatedInspecciones');
        if (!box) return;

        if (!activeColmena) {
            box.innerHTML = `<div class="prod-related-title"><span><i class="fas fa-clipboard-check" style="color:#047857;"></i> Inspecciones</span></div>
                <p class="prod-related-empty">Elige una colmena para ver sus inspecciones relacionadas.</p>`;
            return;
        }

        const resumen = (cfg.inspeccionesResumen || {})[activeColmena];
        const link = `${cfg.inspeccionesBaseUrl}/${encodeURIComponent(activeColmena)}/inspecciones`;
        let filas = '<p class="prod-related-empty">Esta colmena todavía no tiene inspecciones.</p>';
        if (resumen && resumen.lista && resumen.lista.length) {
            filas = resumen.lista.map(i => `
                <div class="prod-related-row">
                    <span><strong>${esc(i.regNumber)}</strong> · ${esc(i.fecha)}</span>
                    <span>Estado: ${esc(i.estado)}</span>
                </div>`).join('');
        }

        // Miel total de la colmena = producción vigente + miel cosechada durante inspecciones
        const kgProd = producciones
            .filter(p => p.colmena === activeColmena && p.estado !== 'Inactiva')
            .reduce((t, p) => t + (parseFloat(p.mielKg) || 0), 0);
        const kgInsp = resumen ? (parseFloat(resumen.kg) || 0) : 0;

        box.innerHTML = `
            <div class="prod-related-title">
                <span><i class="fas fa-clipboard-check" style="color:#047857;"></i> Inspecciones de la colmena ${esc(activeColmena)} (${resumen ? resumen.total : 0})</span>
                <a class="prod-related-link" href="${link}">Ver inspecciones <i class="fas fa-arrow-right"></i></a>
            </div>
            ${filas}
            <div class="prod-related-row" style="font-weight:800; color:#0f172a;">
                <span>Miel total de la colmena (producción ${fmtKg(kgProd)} kg + inspecciones ${fmtKg(kgInsp)} kg)</span>
                <span>${fmtKg(kgProd + kgInsp)} kg</span>
            </div>`;
    }

    // ---------------------------------------------------------------- responsable (lista compartida)
    function buildResponsablesOptions(selectedVal) {
        const lista = Array.isArray(cfg.responsables) ? cfg.responsables.slice() : [];
        if (!lista.some(n => String(n).toLowerCase() === 'sergio barrera')) lista.unshift('SERGIO BARRERA');
        if (selectedVal && !lista.some(n => String(n).toLowerCase() === String(selectedVal).toLowerCase())) lista.push(selectedVal);
        const sel = selectedVal ? String(selectedVal).toLowerCase() : '';
        return `<option value="" disabled ${sel ? '' : 'selected'}>Selecciona un responsable...</option>`
            + lista.map(n => `<option value="${esc(n)}" ${String(n).toLowerCase() === sel ? 'selected' : ''}>${esc(n)}</option>`).join('')
            + `<option value="__otro__" style="color: #047857; font-weight: 700;">➕ Añadir a otro...</option>`;
    }

    function handleProduccionRespSelect(val) {
        const cont = document.getElementById('containerOtroRespProduccion');
        const input = document.getElementById('modalProduccionOtroRespInput');
        const hidden = document.getElementById('modalProduccionResponsable');
        if (val === '__otro__') {
            if (cont) cont.style.display = 'block';
            if (input) { input.value = ''; input.focus(); }
            if (hidden) hidden.value = '';
        } else {
            if (cont) cont.style.display = 'none';
            if (hidden) hidden.value = val;
        }
    }

    function confirmarOtroResponsableProduccion() {
        const input = document.getElementById('modalProduccionOtroRespInput');
        const select = document.getElementById('modalProduccionRespSelect');
        const hidden = document.getElementById('modalProduccionResponsable');
        const cont = document.getElementById('containerOtroRespProduccion');
        const nuevo = (input?.value || '').trim();

        const aviso = (icon, title, text) => Swal.fire({ icon, title, text, confirmButtonColor: '#286043' });

        if (!nuevo) { aviso('warning', 'Nombre requerido', 'Escribe el nombre del responsable.'); input?.focus(); return false; }
        if (/\d/.test(nuevo) || !/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s.'-]+$/.test(nuevo) || nuevo.length < 3) {
            aviso('error', 'Nombre inválido', 'Ingresa un nombre real de persona (solo letras, mínimo 3 caracteres).');
            input?.focus();
            return false;
        }

        const existente = Array.from(select.options).find(o => o.value.toLowerCase() === nuevo.toLowerCase());
        const valor = existente ? existente.value : nuevo;
        if (!existente) {
            const opt = new Option(nuevo, nuevo, true, true);
            select.insertBefore(opt, Array.from(select.options).find(o => o.value === '__otro__'));
            if (!Array.isArray(cfg.responsables)) cfg.responsables = [];
            cfg.responsables.push(nuevo);
            fetch(cfg.responsablesUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ nombre: nuevo })
            }).catch(() => {});
        }
        select.value = valor;
        if (hidden) hidden.value = valor;
        if (cont) cont.style.display = 'none';
        return true;
    }

    // ---------------------------------------------------------------- modal registrar / editar
    function resetRespUI(valor) {
        const select = document.getElementById('modalProduccionRespSelect');
        select.innerHTML = buildResponsablesOptions(valor || '');
        document.getElementById('modalProduccionResponsable').value = valor || '';
        document.getElementById('containerOtroRespProduccion').style.display = 'none';
        document.getElementById('modalProduccionOtroRespInput').value = '';
    }

    function openNewProduccionModal() {
        document.getElementById('modalProduccionTitle').textContent = 'Registrar producción';
        document.getElementById('btnSaveProduccion').textContent = 'Guardar producción';
        document.getElementById('modalProduccionId').value = '';

        const selCol = document.getElementById('modalProduccionColmena');
        const preferida = Array.from(selCol.options).find(o => o.value === activeColmena && !o.disabled)
            || Array.from(selCol.options).find(o => !o.disabled);
        if (preferida) selCol.value = preferida.value;

        document.getElementById('modalProduccionFecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('modalProduccionMiel').value = '';
        document.getElementById('modalProduccionObs').value = '';
        resetRespUI('');

        document.getElementById('modalProduccionBackdrop').classList.add('is-open');
    }

    function openEditProduccionModal(id) {
        const p = producciones.find(x => x.id == id);
        if (!p) return;

        document.getElementById('modalProduccionTitle').textContent = 'Editar producción';
        document.getElementById('btnSaveProduccion').textContent = 'Actualizar producción';
        document.getElementById('modalProduccionId').value = p.id;
        document.getElementById('modalProduccionColmena').value = p.colmena;
        document.getElementById('modalProduccionFecha').value = p.fechaRaw;
        document.getElementById('modalProduccionMiel').value = p.mielKg;
        document.getElementById('modalProduccionObs').value = p.notas || '';
        resetRespUI(p.responsable);

        closeProduccionDetailModal();
        document.getElementById('modalProduccionBackdrop').classList.add('is-open');
    }

    function closeProduccionModal() {
        document.getElementById('modalProduccionBackdrop').classList.remove('is-open');
    }

    async function saveProduccionForm(e) {
        e.preventDefault();

        const id = document.getElementById('modalProduccionId').value;
        const select = document.getElementById('modalProduccionRespSelect');
        const hidden = document.getElementById('modalProduccionResponsable');

        if (select.value === '__otro__') {
            if (!confirmarOtroResponsableProduccion()) return;
        }
        const responsable = (hidden.value || (select.value !== '__otro__' ? select.value : '')).trim();
        if (!responsable) {
            Swal.fire({ icon: 'warning', title: 'Responsable requerido', text: 'Selecciona un responsable o añade uno nuevo.', confirmButtonColor: '#286043' });
            return;
        }

        const miel = parseFloat(document.getElementById('modalProduccionMiel').value);
        if (!miel || miel <= 0) {
            Swal.fire({ icon: 'warning', title: 'Miel inválida', text: 'La miel cosechada debe ser mayor a 0 kg.', confirmButtonColor: '#286043' });
            return;
        }

        const payload = {
            colmena_code: document.getElementById('modalProduccionColmena').value,
            fecha: document.getElementById('modalProduccionFecha').value,
            miel_kg: miel,
            responsable: responsable,
            observaciones: document.getElementById('modalProduccionObs').value.trim()
        };

        const btn = document.getElementById('btnSaveProduccion');
        const textoOriginal = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Guardando...';

        try {
            const resp = await fetch(id ? `${cfg.baseUrl}/${id}` : cfg.storeUrl, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify(payload)
            });
            const result = await resp.json();
            if (!resp.ok || !result.success) {
                let msg = result.message || 'No se pudo guardar la producción.';
                if (result.errors) msg = Object.values(result.errors).flat().join(' | ');
                throw new Error(msg);
            }

            if (id) {
                const idx = producciones.findIndex(x => x.id == id);
                if (idx !== -1) producciones[idx] = result.data;
            } else {
                producciones.unshift(result.data);
            }
            producciones.sort((a, b) => (b.fechaRaw || '').localeCompare(a.fechaRaw || '') || b.id - a.id);

            closeProduccionModal();
            renderProduccionesTable();
            updateMetrics();
            renderRelatedInspecciones();
            showToast(result.message);
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: err.message, confirmButtonColor: '#286043' });
        } finally {
            btn.disabled = false;
            btn.textContent = textoOriginal;
        }
    }

    // ---------------------------------------------------------------- detalle
    function openProduccionDetailModal(id) {
        const p = producciones.find(x => x.id == id);
        if (!p) return;
        document.getElementById('detailProdRegNum').textContent = p.regNumber;
        const badge = document.getElementById('detailProdEstado');
        badge.textContent = p.estado;
        badge.className = 'ap-status-badge ' + (p.estado === 'Inactiva' ? 'inactiva' : 'bueno');
        document.getElementById('detailProdColmena').textContent = `Producción Colmena ${p.colmena}`;
        document.getElementById('detailProdFecha').textContent = p.fecha;
        document.getElementById('detailProdMiel').textContent = `${fmtKg(p.mielKg)} kg`;
        document.getElementById('detailProdResp').textContent = p.responsable;
        document.getElementById('detailProdNotas').textContent = p.notas || 'Sin observaciones.';
        document.getElementById('btnEditFromProduccionDetail').onclick = () => openEditProduccionModal(p.id);
        document.getElementById('modalProduccionDetailBackdrop').classList.add('is-open');
    }

    function closeProduccionDetailModal() {
        document.getElementById('modalProduccionDetailBackdrop').classList.remove('is-open');
    }

    // ---------------------------------------------------------------- activar / desactivar (con motivo)
    async function enviarEstado(url, method, motivo) {
        const resp = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ motivo })
        });
        const result = await resp.json();
        if (!resp.ok || !result.success) {
            throw new Error(result.message || 'No se pudo cambiar el estado.');
        }
        return result;
    }

    async function desactivarProduccion(id) {
        const p = producciones.find(x => x.id == id);
        if (!p) return;
        const motivo = await pedirMotivoBaja({
            titulo: '¿Desactivar producción?',
            texto: `Vas a desactivar la producción <b>${esc(p.regNumber)}</b> de la colmena <b>${esc(p.colmena)}</b>.`,
            confirmar: 'Sí, desactivar'
        });
        if (!motivo) return;

        try {
            const result = await enviarEstado(`${cfg.baseUrl}/${id}`, 'DELETE', motivo);
            p.estado = 'Inactiva';
            renderProduccionesTable();
            updateMetrics();
            showToast(result.message);
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'No se pudo desactivar', text: err.message, confirmButtonColor: '#286043' });
        }
    }

    async function toggleProduccionStatus(id) {
        const p = producciones.find(x => x.id == id);
        if (!p) return;
        if (p.estado !== 'Inactiva') return desactivarProduccion(id);

        if (!confirm(`¿Activar de nuevo la producción "${p.regNumber}"?`)) return;
        try {
            const result = await enviarEstado(`${cfg.baseUrl}/${id}/toggle-status`, 'POST', null);
            p.estado = result.nuevo_estado || 'Activa';
            renderProduccionesTable();
            updateMetrics();
            showToast(result.message);
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'No se pudo activar', text: err.message, confirmButtonColor: '#286043' });
        }
    }

    Object.assign(window, {
        filterProducciones, changeActiveColmena,
        openNewProduccionModal, openEditProduccionModal, closeProduccionModal, saveProduccionForm,
        openProduccionDetailModal, closeProduccionDetailModal,
        desactivarProduccion, toggleProduccionStatus,
        handleProduccionRespSelect, confirmarOtroResponsableProduccion
    });
})();
