// ==================== ESTADO Y DATOS DE INSPECCIÓN ====================
    let activeColmena = window.AprendizInspeccionConfig?.activeColmena || "";

    // Lista de inspecciones sincronizada con la base de datos registro_inspeccion (exclusiva del aprendiz)
    let inspeccionesList = window.AprendizInspeccionConfig?.inspeccionesList || [];
    try {
        localStorage.removeItem('apicola_inspecciones_list');
    } catch(e) {}
    inspeccionesList.forEach(v => {
        if (!v.estado) v.estado = 'Activa';
    });

    document.addEventListener('DOMContentLoaded', () => {
        renderVisitasTable();
        updateInspeccionMetrics();
    });

    function showToast(msg) {
        const toast = document.getElementById('inspeccionToast');
        if (!toast) return;
        toast.textContent = msg;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3200);
    }

    function changeActiveColmena(newCode) {
        activeColmena = newCode;
        const curEl = document.getElementById('currentColmenaCode');
        if (curEl) curEl.textContent = newCode || 'Sin colmena';
        const colmTag = document.getElementById('colmenaTag');
        if (colmTag) colmTag.textContent = newCode || 'GENERAL';
        const subEl = document.getElementById('metricColmenaSub');
        if (subEl) subEl.textContent = newCode ? `Colmena ${newCode}` : 'Colmenas registradas';
        const titleEl = document.getElementById('historyColmenaTitle');
        if (titleEl) titleEl.textContent = newCode ? `Historial de la colmena ${newCode}` : 'Historial de inspecciones';
        const modalColm = document.getElementById('modalVisitaColmena');
        if (modalColm) modalColm.value = newCode;
        
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    // Actualizar 3 tarjetas superiores
    function updateInspeccionMetrics() {
        const filtered = getFilteredVisitas();
        const activeVisitas = filtered.filter(v => v.estado !== 'Inactiva');
        const totalVisitas = filtered.length;
        const mielAcumulada = activeVisitas.reduce((sum, v) => sum + (parseFloat(v.mielKg) || 0), 0);
        const alertasSanitarias = activeVisitas.filter(v => v.enfermedades && v.enfermedades.length > 0).length;

        document.getElementById('metricTotalVisitas').textContent = totalVisitas;
        document.getElementById('metricMielTotal').textContent = mielAcumulada.toFixed(1).replace('.', ',');
        document.getElementById('metricAlertasTotal').textContent = alertasSanitarias;

        const fIni = document.getElementById('filterFechaInicio')?.value;
        const fFin = document.getElementById('filterFechaFin')?.value;
        if (fIni && fFin) {
            document.getElementById('visitasSummaryText').textContent = `${totalVisitas} visitas entre el ${fIni} y el ${fFin}`;
        } else {
            document.getElementById('visitasSummaryText').textContent = `${totalVisitas} visitas registradas`;
        }
        document.getElementById('mielHarvestSummaryText').textContent = `Total cosechado: ${mielAcumulada.toFixed(1).replace('.', ',')} kg`;
    }

    function parseDateDMY(dmyStr) {
        if (!dmyStr) return null;
        if (dmyStr.includes('/')) {
            const parts = dmyStr.split('/');
            if (parts.length === 3) {
                return new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
            }
        }
        if (dmyStr.includes('-')) {
            const parts = dmyStr.split('-');
            if (parts.length === 3) {
                return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            }
        }
        return null;
    }

    function toIsoDate(dateStr) {
        if (!dateStr) return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
        const parts = dateStr.split('/');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }
        return '';
    }

    function toDmyDate(isoStr) {
        if (!isoStr) return '';
        if (/^\d{2}\/\d{2}\/\d{4}$/.test(isoStr)) return isoStr;
        const parts = isoStr.split('-');
        if (parts.length === 3) {
            return `${parts[2].padStart(2, '0')}/${parts[1].padStart(2, '0')}/${parts[0]}`;
        }
        return isoStr;
    }

    function getFilteredVisitas() {
        const fInicio = parseDateDMY(document.getElementById('filterFechaInicio')?.value);
        const fFin = parseDateDMY(document.getElementById('filterFechaFin')?.value);

        return inspeccionesList.filter(item => {
            const vDate = parseDateDMY(item.fecha);
            if (!vDate) return true;
            if (fInicio && vDate < fInicio) return false;
            if (fFin && vDate > fFin) return false;
            return true;
        });
    }

    function filterVisitas() {
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    function renderVisitasTable() {
        const tbody = document.getElementById('visitasTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        const items = getFilteredVisitas();

        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 0.5rem; display:block;"></i>
                        No se encontraron visitas registradas en el rango de fechas seleccionado.
                    </td>
                </tr>
            `;
            return;
        }

        items.forEach(v => {
            const tr = document.createElement('tr');
            const isInactive = v.estado === 'Inactiva';
            if (isInactive) {
                tr.style.opacity = '0.65';
                tr.style.backgroundColor = '#f8fafc';
            }

            // Enfermedad badge
            let enfermedadesHtml = '<span style="color: #64748b;">Ninguna</span>';
            if (v.enfermedades && v.enfermedades.length > 0) {
                enfermedadesHtml = v.enfermedades.map(enf => `<span class="ap-status-badge alerta" style="font-size:0.75rem; padding: 2px 10px;">${enf}</span>`).join(' ');
            }

            tr.innerHTML = `
                <td style="font-weight: 700; color: #0f172a;">${v.fecha}</td>
                <td style="color: #475569; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.85rem;">
                    ${v.regNumber}
                    ${isInactive ? '<span class="ap-status-badge inactiva" style="margin-left: 6px; font-size: 0.68rem; padding: 2px 7px;">Inactiva</span>' : ''}
                </td>
                <td style="color: #334155;">${v.estadoGeneral}</td>
                <td style="color: #334155;">${v.presenciaMiel}</td>
                <td style="color: #0f172a; font-weight: 700;">${parseFloat(v.mielKg).toFixed(1)}</td>
                <td>${enfermedadesHtml}</td>
                <td style="color: #334155;">${v.responsable}</td>
                <td style="text-align: center;">
                    <div class="ap-actions-group" style="justify-content: center;">
                        <button type="button" class="ap-action-btn view" onclick="openVisitaDetailModal(${v.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                        <button type="button" class="ap-action-btn edit" onclick="openEditVisitaModal(${v.id})" title="Editar visita"><i class="fas fa-pen-to-square"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ==================== MODAL DE NUEVA / EDITAR VISITA ====================
    function setEstadoGeneral(val) {
        document.getElementById('modalVisitaEstadoGeneral').value = val;
        document.getElementById('btnEstGenBueno').className = 'ap-status-pill-btn' + (val === 'Bueno' ? ' selected-bueno' : '');
        document.getElementById('btnEstGenRegular').className = 'ap-status-pill-btn' + (val === 'Regular' ? ' selected-regular' : '');
        document.getElementById('btnEstGenMalo').className = 'ap-status-pill-btn' + (val === 'Malo' ? ' selected-malo' : '');
    }

    function setPresenciaMiel(val) {
        document.getElementById('modalVisitaPresenciaMiel').value = val;
        document.getElementById('btnPresMielBueno').className = 'ap-status-pill-btn' + (val === 'Bueno' ? ' selected-bueno' : '');
        document.getElementById('btnPresMielRegular').className = 'ap-status-pill-btn' + (val === 'Regular' ? ' selected-regular' : '');
        document.getElementById('btnPresMielMalo').className = 'ap-status-pill-btn' + (val === 'Malo' ? ' selected-malo' : '');
    }

    function toggleCheckboxCard(checkbox, labelId) {
        const lbl = document.getElementById(labelId);
        if (!lbl) return;
        if (checkbox.checked) {
            lbl.classList.add('is-checked');
        } else {
            lbl.classList.remove('is-checked');
        }
    }

    // ==================== VALIDACIÓN DE TEXTO SIN NÚMEROS ====================
    function validarTextoSinNumerosVisita(input, errorSpanId) {
        const errorSpan = document.getElementById(errorSpanId);
        if (/\d/.test(input.value)) {
            if (errorSpan) errorSpan.style.display = 'block';
            input.style.borderColor = '#dc2626';
        } else {
            if (errorSpan) errorSpan.style.display = 'none';
            input.style.borderColor = '#cbd5e1';
        }
    }

    // ==================== MANEJO DE COLOR DE LA REINA ====================
    function handleColorMarcaChange(val) {
        const wrap = document.getElementById('wrapperColorMarcaOtro');
        const input = document.getElementById('modalVisitaColorMarcaOtro');
        const errorSpan = document.getElementById('errorColorMarcaOtroAprendiz') || document.getElementById('errorColorMarcaOtro');
        if (errorSpan) errorSpan.style.display = 'none';
        if (input) input.style.borderColor = '#cbd5e1';
        if (!wrap) return;
        if (val === 'Otro') {
            wrap.style.display = 'block';
            if (input) input.focus();
        } else {
            wrap.style.display = 'none';
        }
    }

    function agregarColorMarcaCustom() {
        const input = document.getElementById('modalVisitaColorMarcaOtro');
        const select = document.getElementById('modalVisitaColorMarca');
        const errorSpan = document.getElementById('errorColorMarcaOtroAprendiz') || document.getElementById('errorColorMarcaOtro');
        if (!input || !select) return;
        const val = input.value.trim();
        if (!val) {
            input.focus();
            return;
        }

        // Validación: no números
        if (/\d/.test(val)) {
            if (errorSpan) errorSpan.style.display = 'block';
            input.style.borderColor = '#dc2626';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Color inválido',
                    text: 'No se permiten números en el color de la reina. Solo se permiten letras.',
                    confirmButtonColor: '#286043'
                });
            } else {
                alert('No se permiten números en el color de la reina. Solo se permiten letras.');
            }
            input.focus();
            return;
        }
        if (errorSpan) errorSpan.style.display = 'none';
        input.style.borderColor = '#cbd5e1';

        let found = false;
        for (let opt of select.options) {
            if (opt.value.toLowerCase() === val.toLowerCase()) {
                select.value = opt.value;
                found = true;
                break;
            }
        }

        if (!found) {
            const newOpt = document.createElement('option');
            newOpt.value = val;
            newOpt.textContent = val + ' (Personalizado)';
            const otroOpt = Array.from(select.options).find(o => o.value === 'Otro');
            if (otroOpt) {
                select.insertBefore(newOpt, otroOpt);
            } else {
                select.appendChild(newOpt);
            }
            select.value = val;
        }
        document.getElementById('wrapperColorMarcaOtro').style.display = 'none';
    }

    // ==================== HELPER ESCAPE HTML ====================
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ==================== GESTIÓN DE CATÁLOGO DE ENFERMEDADES ====================
    let enfermedadesCatalogo = window.AprendizInspeccionConfig?.enfermedadesList || [];

    function openEnfermedadesModal() {
        const modal = document.getElementById('modalEnfermedadesBackdrop');
        if (!modal) return;
        modal.classList.add('is-open');

        const input = document.getElementById('inputNuevaEnfermedadCatalogo');
        const err = document.getElementById('errorNuevaEnfermedadCatalogo');
        if (input) {
            input.value = '';
            input.style.borderColor = '#10b981';
            setTimeout(() => input.focus(), 150);
        }
        if (err) err.style.display = 'none';

        cargarEnfermedadesCatalogo();
    }

    function closeEnfermedadesModal() {
        const modal = document.getElementById('modalEnfermedadesBackdrop');
        if (modal) modal.classList.remove('is-open');
    }

    function cargarEnfermedadesCatalogo() {
        const url = window.AprendizInspeccionConfig?.enfermedadesUrl;
        if (!url) {
            renderEnfermedadesCatalogo(enfermedadesCatalogo);
            return;
        }

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                enfermedadesCatalogo = res.data;
                renderEnfermedadesCatalogo(enfermedadesCatalogo);
                renderVisitaEnfermedadesCheckboxes(enfermedadesCatalogo);
            } else {
                renderEnfermedadesCatalogo(enfermedadesCatalogo);
            }
        })
        .catch(() => {
            renderEnfermedadesCatalogo(enfermedadesCatalogo);
        });
    }

    function renderEnfermedadesCatalogo(lista) {
        const container = document.getElementById('listaEnfermedadesCatalogo');
        const badge = document.getElementById('badgeTotalEnfermedades');
        if (badge) {
            badge.textContent = `${lista.length} registrada${lista.length === 1 ? '' : 's'}`;
        }
        if (!container) return;

        if (!lista || lista.length === 0) {
            container.innerHTML = '<p style="margin: 12px 0; font-size: 0.85rem; color: #64748b; font-style: italic; width: 100%; text-align: center;">No hay enfermedades registradas en el catálogo. Use el campo superior para registrar una nueva enfermedad.</p>';
            return;
        }

        container.innerHTML = lista.map(enf => {
            const nombre = typeof enf === 'string' ? enf : (enf.nombre || '');
            const id = typeof enf === 'object' && enf.id ? enf.id : null;
            const deleteBtn = id 
                ? `<button type="button" class="ap-enf-pill-del" onclick="eliminarEnfermedadCatalogo(${id}, '${escapeHtml(nombre)}')" title="Eliminar ${escapeHtml(nombre)}" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:1.15rem; line-height:1; padding:0 3px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; transition:transform 0.15s ease;">&times;</button>`
                : '';

            return `
                <div class="ap-enf-pill" style="display: inline-flex; align-items: center; gap: 8px; background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; padding: 6px 14px; border-radius: 20px; font-size: 0.86rem; font-weight: 700;">
                    <i class="fas fa-virus" style="color: #059669; font-size: 0.82rem;"></i>
                    <span>${escapeHtml(nombre)}</span>
                    ${deleteBtn}
                </div>
            `;
        }).join('');
    }

    function guardarNuevaEnfermedadCatalogo() {
        const input = document.getElementById('inputNuevaEnfermedadCatalogo');
        const err = document.getElementById('errorNuevaEnfermedadCatalogo');
        const btn = document.getElementById('btnGuardarEnfermedadCatalogo');
        const valor = (input ? input.value : '').trim();

        if (!valor) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nombre requerido',
                    text: 'Por favor escribe el nombre de la enfermedad.',
                    confirmButtonColor: '#286043'
                });
            } else {
                alert('Por favor escribe el nombre de la enfermedad.');
            }
            if (input) input.focus();
            return;
        }

        if (/\d/.test(valor)) {
            if (err) err.style.display = 'block';
            if (input) input.style.borderColor = '#dc2626';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Enfermedad inválida',
                    text: 'No se permiten números en el nombre de la enfermedad. Solo se permiten letras.',
                    confirmButtonColor: '#286043'
                });
            } else {
                alert('No se permiten números en el nombre de la enfermedad. Solo se permiten letras.');
            }
            if (input) input.focus();
            return;
        }

        if (err) err.style.display = 'none';
        if (input) input.style.borderColor = '#10b981';

        const url = window.AprendizInspeccionConfig?.enfermedadesUrl;
        const csrf = window.AprendizInspeccionConfig?.csrfToken;

        if (btn) btn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ nombre: valor })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (btn) btn.disabled = false;
            if (status === 201 || body.success) {
                if (input) input.value = '';
                if (Array.isArray(body.data)) {
                    enfermedadesCatalogo = body.data;
                } else if (body.enfermedad) {
                    enfermedadesCatalogo.push(body.enfermedad);
                }
                renderEnfermedadesCatalogo(enfermedadesCatalogo);
                renderVisitaEnfermedadesCheckboxes(enfermedadesCatalogo);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enfermedad registrada',
                        text: body.message || 'La enfermedad fue agregada al catálogo.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
                if (input) input.focus();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: body.message || 'No fue posible registrar la enfermedad.',
                        confirmButtonColor: '#286043'
                    });
                } else {
                    alert(body.message || 'No fue posible registrar la enfermedad.');
                }
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Ocurrió un problema al guardar la enfermedad.',
                    confirmButtonColor: '#286043'
                });
            }
        });
    }

    function eliminarEnfermedadCatalogo(id, nombre) {
        if (!id) return;

        const url = window.AprendizInspeccionConfig?.enfermedadesUrl;
        const csrf = window.AprendizInspeccionConfig?.csrfToken;

        const ejecutarEliminacion = () => {
            fetch(`${url}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(body => {
                if (body.success) {
                    if (Array.isArray(body.data)) {
                        enfermedadesCatalogo = body.data;
                    } else {
                        enfermedadesCatalogo = enfermedadesCatalogo.filter(e => e.id !== id);
                    }
                    renderEnfermedadesCatalogo(enfermedadesCatalogo);
                    renderVisitaEnfermedadesCheckboxes(enfermedadesCatalogo);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminada',
                            text: body.message || `La enfermedad "${nombre}" fue eliminada del catálogo.`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: body.message || 'No se pudo eliminar la enfermedad.',
                            confirmButtonColor: '#286043'
                        });
                    }
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No fue posible comunicarse con el servidor.',
                        confirmButtonColor: '#286043'
                    });
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar enfermedad?',
                text: `¿Desea quitar "${nombre}" del catálogo de enfermedades disponibles?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    ejecutarEliminacion();
                }
            });
        } else {
            if (confirm(`¿Desea eliminar "${nombre}" del catálogo?`)) {
                ejecutarEliminacion();
            }
        }
    }

    function renderVisitaEnfermedadesCheckboxes(lista) {
        const container = document.getElementById('containerEnfermedades');
        if (!container) return;

        const currentlyChecked = new Set();
        container.querySelectorAll('input[name="enfermedades"]:checked').forEach(cb => {
            currentlyChecked.add(cb.value);
        });

        if (!lista || lista.length === 0) {
            container.innerHTML = '<p id="msgSinEnfermedades" style="font-size: 0.85rem; color: #64748b; font-style: italic; margin: 6px 0;">No hay enfermedades registradas en el sistema. Puede registrarlas desde el botón superior "Enfermedades".</p>';
            return;
        }

        container.innerHTML = lista.map(enf => {
            const nombre = typeof enf === 'string' ? enf : (enf.nombre || '');
            const slug = nombre.toLowerCase().replace(/[^a-z0-9]/g, '_');
            const enfId = 'lblEnf_' + slug;
            const isChecked = currentlyChecked.has(nombre);

            return `
                <label class="ap-checkbox-card ${isChecked ? 'is-checked' : ''}" id="${enfId}">
                    <input type="checkbox" name="enfermedades" value="${escapeHtml(nombre)}" ${isChecked ? 'checked' : ''} onchange="toggleCheckboxCard(this, '${enfId}')">
                    <span>${escapeHtml(nombre)}</span>
                </label>
            `;
        }).join('');
    }

    // ==================== MANEJO DE OTRAS LABORES (ESTILO IDÉNTICO A OTRA ENFERMEDAD) ====================
    function toggleSeccionNuevaLabor(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const container = document.getElementById('containerNuevaLabor');
        const input = document.getElementById('inputNuevaLaborTexto');
        const errorSpan = document.getElementById('errorNuevaLaborAprendiz') || document.getElementById('errorNuevaLabor');
        if (errorSpan) errorSpan.style.display = 'none';
        if (input) input.style.borderColor = '#cbd5e1';
        if (!container) return;

        if (container.style.display === 'none' || !container.style.display) {
            container.style.display = 'block';
            if (input) {
                input.value = '';
                input.focus();
            }
        } else {
            container.style.display = 'none';
        }
    }

    function confirmarNuevaLabor() {
        const input = document.getElementById('inputNuevaLaborTexto');
        const errorSpan = document.getElementById('errorNuevaLaborAprendiz') || document.getElementById('errorNuevaLabor');
        const valor = (input ? input.value : '').trim();
        if (!valor) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nombre requerido',
                    text: 'Por favor escribe el nombre de la labor realizada.',
                    confirmButtonColor: '#286043'
                });
            } else {
                alert('Por favor escribe el nombre de la labor realizada.');
            }
            if (input) input.focus();
            return;
        }

        // Validación: no números
        if (/\d/.test(valor)) {
            if (errorSpan) errorSpan.style.display = 'block';
            if (input) input.style.borderColor = '#dc2626';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Labor inválida',
                    text: 'No se permiten números en el nombre de la labor. Solo se permiten letras.',
                    confirmButtonColor: '#286043'
                });
            } else {
                alert('No se permiten números en el nombre de la labor. Solo se permiten letras.');
            }
            if (input) input.focus();
            return;
        }
        if (errorSpan) errorSpan.style.display = 'none';
        if (input) input.style.borderColor = '#cbd5e1';

        agregarNuevaLabor(valor, true);

        const container = document.getElementById('containerNuevaLabor');
        if (container) container.style.display = 'none';
        if (input) input.value = '';
    }

    function toggleLaborOtra(cb) {
        toggleSeccionNuevaLabor();
    }

    function agregarNuevaLabor(nombreForzado = null, isChecked = true) {
        const input = document.getElementById('inputNuevaLaborTexto') || document.getElementById('txtNuevaLabor');
        const errorSpan = document.getElementById('errorNuevaLaborAprendiz') || document.getElementById('errorNuevaLabor');
        const valor = (nombreForzado !== null ? nombreForzado : (input ? input.value : '')).trim();
        if (!valor) {
            if (input) input.focus();
            return;
        }

        // Validación: no números
        if (/\d/.test(valor)) {
            if (errorSpan) errorSpan.style.display = 'block';
            if (input) input.style.borderColor = '#dc2626';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Labor inválida',
                    text: 'No se permiten números en el nombre de la labor. Solo se permiten letras.',
                    confirmButtonColor: '#286043'
                });
            } else {
                alert('No se permiten números en el nombre de la labor. Solo se permiten letras.');
            }
            if (input) input.focus();
            return;
        }
        if (errorSpan) errorSpan.style.display = 'none';
        if (input) input.style.borderColor = '#cbd5e1';

        const container = document.getElementById('containerLabores');
        if (!container) return;

        const existing = container.querySelector(`input[name="labores"][value="${CSS.escape(valor)}"]`);
        if (existing) {
            existing.checked = true;
            const parentLbl = existing.closest('.ap-checkbox-card');
            if (parentLbl) parentLbl.classList.add('is-checked');
            if (input && nombreForzado === null) input.value = '';
            return;
        }

        const customId = 'lblLabCust_' + Math.random().toString(36).substring(2, 9);
        const label = document.createElement('label');
        label.className = 'ap-checkbox-card is-custom-card' + (isChecked ? ' is-checked' : '');
        label.id = customId;

        label.innerHTML = `
            <input type="checkbox" name="labores" value="${valor}" ${isChecked ? 'checked' : ''} onchange="toggleCheckboxCard(this, '${customId}')">
            <span style="flex: 1; word-break: break-word;">${valor}</span>
            <button type="button" class="ap-btn-remove-card" onclick="removerCardItem(event, '${customId}')" title="Eliminar">&times;</button>
        `;

        const otraLabel = document.getElementById('lblLabOtra');
        if (otraLabel && otraLabel.parentNode === container) {
            container.insertBefore(label, otraLabel);
        } else {
            container.appendChild(label);
        }

        if (input && nombreForzado === null) {
            input.value = '';
        }
    }

    function removerCardItem(e, id) {
        e.stopPropagation();
        e.preventDefault();
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function openNewVisitaModal() {
        document.getElementById('modalVisitaTitle').textContent = 'Registrar visita';
        document.getElementById('modalVisitaId').value = '';
        
        // Fecha por defecto hoy en formato ISO para el calendario
        const todayIso = new Date().toISOString().split('T')[0];
        document.getElementById('modalVisitaFecha').value = todayIso;
        document.getElementById('modalVisitaColmena').value = activeColmena || '';
        document.getElementById('modalVisitaResponsable').value = window.AprendizInspeccionConfig?.currentUserName || document.getElementById('modalVisitaResponsable')?.value || 'Aprendiz';
        document.getElementById('modalVisitaMielKg').value = '';
        document.getElementById('modalVisitaAlimentacion').value = '';
        document.getElementById('modalVisitaObservaciones').value = '';

        setEstadoGeneral('Bueno');
        setPresenciaMiel('Bueno');

        // Reina (desmarcada por defecto)
        const chkReina = document.getElementById('chkSeVioReina');
        chkReina.checked = false;
        toggleCheckboxCard(chkReina, 'lblSeVioReina');

        const chkMarcada = document.getElementById('chkEstaMarcada');
        chkMarcada.checked = false;
        toggleCheckboxCard(chkMarcada, 'lblEstaMarcada');

        // Color de la marca
        document.getElementById('modalVisitaColorMarca').value = 'Sin marcar';
        const wrapColor = document.getElementById('wrapperColorMarcaOtro');
        if (wrapColor) wrapColor.style.display = 'none';
        const inputColor = document.getElementById('modalVisitaColorMarcaOtro');
        if (inputColor) inputColor.value = '';

        // Limpiar cards personalizadas previas
        document.querySelectorAll('.is-custom-card').forEach(el => el.remove());

        // Enfermedades (todas desmarcadas)
        document.querySelectorAll('input[name="enfermedades"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });
        const chkEnfOtra = document.getElementById('chkEnfOtra');
        if (chkEnfOtra) {
            chkEnfOtra.checked = false;
            toggleEnfermedadOtra(chkEnfOtra);
        }
        const txtEnfOtra = document.getElementById('modalVisitaEnfOtraTexto');
        if (txtEnfOtra) txtEnfOtra.value = '';
        const txtNewEnf = document.getElementById('txtNuevaEnfermedad');
        if (txtNewEnf) txtNewEnf.value = '';

        // Labores (todas desmarcadas)
        document.querySelectorAll('input[name="labores"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });
        const contNewLab = document.getElementById('containerNuevaLabor');
        if (contNewLab) contNewLab.style.display = 'none';
        const txtNewLab = document.getElementById('inputNuevaLaborTexto');
        if (txtNewLab) {
            txtNewLab.value = '';
            txtNewLab.style.borderColor = '#cbd5e1';
        }
        const errNewLab = document.getElementById('errorNuevaLaborAprendiz') || document.getElementById('errorNuevaLabor');
        if (errNewLab) errNewLab.style.display = 'none';

        document.getElementById('modalVisitaBackdrop').classList.add('is-open');
    }

    function openEditVisitaModal(id) {
        const item = inspeccionesList.find(v => v.id === id);
        if (!item) return;

        document.getElementById('modalVisitaTitle').textContent = 'Editar visita';
        document.getElementById('modalVisitaId').value = item.id;
        document.getElementById('modalVisitaFecha').value = toIsoDate(item.fecha);
        document.getElementById('modalVisitaColmena').value = item.colmena;
        document.getElementById('modalVisitaResponsable').value = item.responsable;
        document.getElementById('modalVisitaMielKg').value = item.mielKg;
        document.getElementById('modalVisitaAlimentacion').value = item.alimenSuministrada || '';
        document.getElementById('modalVisitaObservaciones').value = item.notas || '';

        setEstadoGeneral(item.estadoGeneral);
        setPresenciaMiel(item.presenciaMiel);

        // Reina
        const chkReina = document.getElementById('chkSeVioReina');
        chkReina.checked = !!item.reinaVista;
        toggleCheckboxCard(chkReina, 'lblSeVioReina');

        const chkMarcada = document.getElementById('chkEstaMarcada');
        chkMarcada.checked = !!item.reinaMarcada;
        toggleCheckboxCard(chkMarcada, 'lblEstaMarcada');

        // Color de la marca
        const color = item.colorMarca || 'Sin marcar';
        const selColor = document.getElementById('modalVisitaColorMarca');
        const wrapColor = document.getElementById('wrapperColorMarcaOtro');
        const inputColor = document.getElementById('modalVisitaColorMarcaOtro');

        let colorFound = false;
        for (let opt of selColor.options) {
            if (opt.value.toLowerCase() === color.toLowerCase()) {
                selColor.value = opt.value;
                colorFound = true;
                break;
            }
        }
        if (!colorFound && color !== 'Sin marcar') {
            const newOpt = document.createElement('option');
            newOpt.value = color;
            newOpt.textContent = color + ' (Personalizado)';
            const otroOpt = Array.from(selColor.options).find(o => o.value === 'Otro');
            if (otroOpt) selColor.insertBefore(newOpt, otroOpt);
            else selColor.appendChild(newOpt);
            selColor.value = color;
        }
        if (wrapColor) {
            if (selColor.value === 'Otro') {
                wrapColor.style.display = 'block';
                if (inputColor) inputColor.value = color !== 'Otro' ? color : '';
            } else {
                wrapColor.style.display = 'none';
                if (inputColor) inputColor.value = '';
            }
        }

        // Limpiar cards personalizadas previas
        document.querySelectorAll('.is-custom-card').forEach(el => el.remove());

        // Enfermedades
        const chkEnfOtra = document.getElementById('chkEnfOtra');
        const txtEnfOtra = document.getElementById('modalVisitaEnfOtraTexto');
        if (chkEnfOtra) chkEnfOtra.checked = false;
        if (txtEnfOtra) txtEnfOtra.value = '';

        document.querySelectorAll('input[name="enfermedades"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });

        (item.enfermedades || []).forEach(enf => {
            const existing = document.querySelector(`input[name="enfermedades"][value="${CSS.escape(enf)}"]`);
            if (existing) {
                existing.checked = true;
                const lbl = existing.closest('.ap-checkbox-card');
                if (lbl) lbl.classList.add('is-checked');
            } else if (enf === 'Otra') {
                if (chkEnfOtra) chkEnfOtra.checked = true;
            } else {
                agregarNuevaEnfermedad(enf, true);
            }
        });
        if (chkEnfOtra) toggleEnfermedadOtra(chkEnfOtra);

        // Labores
        const chkLabOtra = document.getElementById('chkLabOtra');
        const txtLabOtra = document.getElementById('modalVisitaLabOtraTexto');
        if (chkLabOtra) chkLabOtra.checked = false;
        if (txtLabOtra) txtLabOtra.value = '';

        document.querySelectorAll('input[name="labores"]').forEach(cb => {
            cb.checked = false;
            const lbl = cb.closest('.ap-checkbox-card');
            if (lbl) lbl.classList.remove('is-checked');
        });

        (item.labores || []).forEach(lab => {
            const existing = document.querySelector(`input[name="labores"][value="${CSS.escape(lab)}"]`);
            if (existing) {
                existing.checked = true;
                const lbl = existing.closest('.ap-checkbox-card');
                if (lbl) lbl.classList.add('is-checked');
            } else if (lab === 'Otra') {
                if (chkLabOtra) chkLabOtra.checked = true;
            } else {
                agregarNuevaLabor(lab, true);
            }
        });
        if (chkLabOtra) toggleLaborOtra(chkLabOtra);

        document.getElementById('modalVisitaBackdrop').classList.add('is-open');
    }

    function closeVisitaModal() {
        document.getElementById('modalVisitaBackdrop').classList.remove('is-open');
    }

    function saveVisitaForm(e) {
        e.preventDefault();

        const id = document.getElementById('modalVisitaId').value;
        const rawFecha = document.getElementById('modalVisitaFecha').value.trim();
        const fecha = toDmyDate(rawFecha) || rawFecha;
        const colmena = document.getElementById('modalVisitaColmena').value.trim();
        const responsable = document.getElementById('modalVisitaResponsable').value.trim();
        const estadoGeneral = document.getElementById('modalVisitaEstadoGeneral').value;
        const presenciaMiel = document.getElementById('modalVisitaPresenciaMiel').value;
        const mielKg = parseFloat(document.getElementById('modalVisitaMielKg').value) || 0;
        
        const reinaVista = document.getElementById('chkSeVioReina').checked;
        const reinaMarcada = document.getElementById('chkEstaMarcada').checked;
        
        // Color reina
        let colorMarca = document.getElementById('modalVisitaColorMarca').value;
        if (colorMarca === 'Otro') {
            const inputColorOtro = document.getElementById('modalVisitaColorMarcaOtro');
            const errorColorOtro = document.getElementById('errorColorMarcaOtroAprendiz') || document.getElementById('errorColorMarcaOtro');
            const custom = (inputColorOtro?.value || '').trim();
            if (custom && /\d/.test(custom)) {
                if (errorColorOtro) errorColorOtro.style.display = 'block';
                if (inputColorOtro) inputColorOtro.style.borderColor = '#dc2626';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Color inválido',
                        text: 'No se permiten números en el color de la reina. Solo se permiten letras.',
                        confirmButtonColor: '#286043'
                    });
                } else {
                    alert('No se permiten números en el color de la reina. Solo se permiten letras.');
                }
                if (inputColorOtro) inputColorOtro.focus();
                return;
            }
            colorMarca = custom || 'Otro';
        }

        // Enfermedades
        const enfermedades = [];
        document.querySelectorAll('input[name="enfermedades"]:checked').forEach(cb => {
            enfermedades.push(cb.value);
        });
        const inputNuevaEnf = document.getElementById('inputNuevaEnfermedadTexto');
        if (inputNuevaEnf && inputNuevaEnf.value.trim()) {
            const extraEnf = inputNuevaEnf.value.trim();
            if (/\d/.test(extraEnf)) {
                const errEnf = document.getElementById('errorNuevaEnfermedadAprendiz') || document.getElementById('errorNuevaEnfermedad');
                if (errEnf) errEnf.style.display = 'block';
                inputNuevaEnf.style.borderColor = '#dc2626';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Enfermedad inválida',
                        text: 'No se permiten números en el nombre de la enfermedad. Solo se permiten letras.',
                        confirmButtonColor: '#286043'
                    });
                }
                inputNuevaEnf.focus();
                return;
            }
            if (!enfermedades.includes(extraEnf)) {
                enfermedades.push(extraEnf);
            }
        }

        // Labores
        const labores = [];
        document.querySelectorAll('input[name="labores"]:checked').forEach(cb => {
            labores.push(cb.value);
        });

        const inputNuevaLab = document.getElementById('inputNuevaLaborTexto');
        if (inputNuevaLab && inputNuevaLab.value.trim()) {
            const extraLab = inputNuevaLab.value.trim();
            if (/\d/.test(extraLab)) {
                const errLab = document.getElementById('errorNuevaLaborAprendiz') || document.getElementById('errorNuevaLabor');
                if (errLab) errLab.style.display = 'block';
                inputNuevaLab.style.borderColor = '#dc2626';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Labor inválida',
                        text: 'No se permiten números en el nombre de la labor. Solo se permiten letras.',
                        confirmButtonColor: '#286043'
                    });
                }
                inputNuevaLab.focus();
                return;
            }
            if (!labores.includes(extraLab)) {
                labores.push(extraLab);
            }
        }

        const inputAlimen = document.getElementById('modalVisitaAlimentacion');
        const errorAlimen = document.getElementById('errorAlimentacionAprendiz') || document.getElementById('errorAlimentacion');
        const alimenSuministrada = (inputAlimen?.value || '').trim();

        if (alimenSuministrada && /\d/.test(alimenSuministrada)) {
            if (errorAlimen) errorAlimen.style.display = 'block';
            if (inputAlimen) inputAlimen.style.borderColor = '#dc2626';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Alimentación inválida',
                    text: 'No se permiten números en la alimentación suministrada. Solo se permiten letras.',
                    confirmButtonColor: '#286043'
                });
            } else {
                alert('No se permiten números en la alimentación suministrada. Solo se permiten letras.');
            }
            if (inputAlimen) inputAlimen.focus();
            return;
        }
        if (errorAlimen) errorAlimen.style.display = 'none';
        if (inputAlimen) inputAlimen.style.borderColor = '#cbd5e1';

        const notas = document.getElementById('modalVisitaObservaciones').value.trim();

        if (id) {
            // Editar existente
            const item = inspeccionesList.find(v => v.id === parseInt(id));
            if (item) {
                item.fecha = fecha;
                item.colmena = colmena;
                item.responsable = responsable;
                item.estadoGeneral = estadoGeneral;
                item.presenciaMiel = presenciaMiel;
                item.mielKg = mielKg;
                item.reinaVista = reinaVista;
                item.reinaMarcada = reinaMarcada;
                item.colorMarca = colorMarca;
                item.enfermedades = enfermedades;
                item.labores = labores;
                item.alimenSuministrada = alimenSuministrada;
                item.notas = notas;
                showToast(`Visita ${item.regNumber} actualizada con éxito.`);
            }
        } else {
            // Crear nueva
            const maxReg = inspeccionesList.reduce((max, v) => {
                const match = v.regNumber.match(/REG-(\d+)/);
                if (match) {
                    const num = parseInt(match[1]);
                    return num > max ? num : max;
                }
                return max;
            }, 0);
            const nextReg = `REG-${String(maxReg + 1).padStart(4, '0')}`;
            const newId = inspeccionesList.length ? Math.max(...inspeccionesList.map(v => v.id)) + 1 : 1;

            inspeccionesList.unshift({
                id: newId,
                regNumber: nextReg,
                colmena: colmena,
                fecha: fecha,
                estadoGeneral: estadoGeneral,
                presenciaMiel: presenciaMiel,
                mielKg: mielKg,
                enfermedades: enfermedades,
                responsable: responsable,
                reinaVista: reinaVista,
                reinaMarcada: reinaMarcada,
                colorMarca: colorMarca,
                labores: labores,
                alimenSuministrada: alimenSuministrada,
                notas: notas,
                estado: 'Activa'
            });

            showToast(`Visita ${nextReg} registrada con éxito.`);

            // Envío asíncrono para persistencia en backend
            const formattedIso = toIsoDate(rawFecha) || (fecha.includes('/') ? `${fecha.split('/')[2]}-${fecha.split('/')[1]}-${fecha.split('/')[0]}` : new Date().toISOString().split('T')[0]);

            fetch((window.AprendizInspeccionConfig?.storeUrl || "/apicola/aprendiz/inspecciones"), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.AprendizInspeccionConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    registration_number: nextReg,
                    colmena_code: colmena,
                    date: formattedIso,
                    responsable: responsable,
                    general_state: estadoGeneral,
                    honey_presence: presenciaMiel,
                    harvested_honey_kg: mielKg,
                    queen_seen: reinaVista,
                    queen_marked: reinaMarcada,
                    queen_color: colorMarca,
                    diseases: enfermedades,
                    tasks: labores,
                    feed_supplied: alimenSuministrada,
                    notes: notas
                })
            }).then(res => res.json()).then(data => {
                if (data && data.id_registro) {
                    const newItem = inspeccionesList.find(v => v.regNumber === nextReg);
                    if (newItem) newItem.id = data.id_registro;
                }
            }).catch(err => console.log('Registro guardado'));
        }

        closeVisitaModal();
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    // Desactivar visita (sin eliminar de la base de datos)
    async function desactivarVisita(id, regNumber) {
        if (!confirm(`¿Estás seguro de que deseas desactivar la visita "${regNumber}"?`)) {
            return;
        }

        try {
            const response = await fetch(`${window.AprendizInspeccionConfig?.baseUrl || "/apicola/aprendiz/inspecciones"}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.AprendizInspeccionConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')),
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No se pudo desactivar la visita.');
            }
        } catch (err) {
            console.warn('Persistencia en backend:', err.message);
        }

        const item = inspeccionesList.find(v => v.id === id);
        if (item) {
            item.estado = 'Inactiva';
        }
        try {
            localStorage.setItem('apicola_inspecciones_list', JSON.stringify(inspeccionesList));
        } catch(e) {
            console.error(e);
        }
        showToast(`Visita "${regNumber}" desactivada con éxito.`);
        renderVisitasTable();
        updateInspeccionMetrics();
    }

    // Activar o Alternar estado de la visita
    async function toggleVisitaStatus(id, regNumber) {
        showToast('El rol de aprendiz no tiene permisos para desactivar inspecciones.');
    }

    // ==================== DETALLE DE VISITA ====================
    function openVisitaDetailModal(id) {
        const item = inspeccionesList.find(v => v.id === id);
        if (!item) return;

        document.getElementById('detailVisitaRegNum').textContent = item.regNumber;
        const badgeEstado = document.getElementById('detailVisitaEstadoBadge');
        if (badgeEstado) {
            const isInactive = item.estado === 'Inactiva';
            badgeEstado.textContent = isInactive ? 'Inactiva' : 'Activa';
            badgeEstado.className = 'ap-status-badge ' + (isInactive ? 'inactiva' : 'bueno');
        }
        document.getElementById('detailVisitaColmena').textContent = `Inspección ${item.colmena}`;
        document.getElementById('detailVisitaFecha').textContent = item.fecha;

        const badgeGen = document.getElementById('detailVisitaEstadoGen');
        badgeGen.textContent = item.estadoGeneral;
        badgeGen.className = 'ap-status-badge ' + (item.estadoGeneral === 'Bueno' ? 'bueno' : (item.estadoGeneral === 'Regular' ? 'regular' : 'alerta'));

        const badgeMiel = document.getElementById('detailVisitaPresMiel');
        badgeMiel.textContent = item.presenciaMiel;
        badgeMiel.className = 'ap-status-badge ' + (item.presenciaMiel === 'Bueno' ? 'bueno' : (item.presenciaMiel === 'Regular' ? 'regular' : 'alerta'));

        document.getElementById('detailVisitaMielKg').textContent = `${parseFloat(item.mielKg).toFixed(1)} kg`;
        document.getElementById('detailVisitaResp').textContent = item.responsable;

        const badgeEnf = document.getElementById('detailVisitaEnfermedades');
        if (item.enfermedades && item.enfermedades.length > 0) {
            badgeEnf.textContent = item.enfermedades.join(', ');
            badgeEnf.className = 'ap-status-badge alerta';
        } else {
            badgeEnf.textContent = 'Ninguna';
            badgeEnf.className = 'ap-status-badge bueno';
        }

        let reinaStr = item.reinaVista ? 'Vista' : 'No vista';
        if (item.reinaMarcada) reinaStr += ` (Marcada: ${item.colorMarca})`;
        document.getElementById('detailVisitaReinaInfo').textContent = reinaStr;

        document.getElementById('detailVisitaLabores').textContent = (item.labores && item.labores.length > 0) ? item.labores.join(', ') : 'Ninguna';
        document.getElementById('detailVisitaNotas').textContent = item.notas || 'Sin observaciones adicionales.';

        document.getElementById('btnEditFromVisitaDetail').onclick = () => {
            closeVisitaDetailModal();
            openEditVisitaModal(item.id);
        };

        document.getElementById('modalVisitaDetailBackdrop').classList.add('is-open');
    }

    function closeVisitaDetailModal() {
        document.getElementById('modalVisitaDetailBackdrop').classList.remove('is-open');
    }

    // Exponer globalmente en window para llamadas inline HTML
    window.openNewVisitaModal = openNewVisitaModal;
    window.openEditVisitaModal = openEditVisitaModal;
    window.closeVisitaModal = closeVisitaModal;
    window.saveVisitaForm = saveVisitaForm;
    window.toggleCheckboxCard = toggleCheckboxCard;
    window.handleColorMarcaChange = handleColorMarcaChange;
    window.agregarColorMarcaCustom = agregarColorMarcaCustom;
    window.openEnfermedadesModal = openEnfermedadesModal;
    window.closeEnfermedadesModal = closeEnfermedadesModal;
    window.guardarNuevaEnfermedadCatalogo = guardarNuevaEnfermedadCatalogo;
    window.eliminarEnfermedadCatalogo = eliminarEnfermedadCatalogo;
    window.cargarEnfermedadesCatalogo = cargarEnfermedadesCatalogo;
    window.renderEnfermedadesCatalogo = renderEnfermedadesCatalogo;
    window.renderVisitaEnfermedadesCheckboxes = renderVisitaEnfermedadesCheckboxes;
    window.removerCardItem = removerCardItem;
    window.toggleLaborOtra = toggleLaborOtra;
    window.toggleSeccionNuevaLabor = toggleSeccionNuevaLabor;
    window.confirmarNuevaLabor = confirmarNuevaLabor;
    window.agregarNuevaLabor = agregarNuevaLabor;
    window.validarTextoSinNumerosVisita = validarTextoSinNumerosVisita;
    window.openVisitaDetailModal = openVisitaDetailModal;
    window.closeVisitaDetailModal = closeVisitaDetailModal;
    window.changeActiveColmena = changeActiveColmena;
    window.filterVisitas = filterVisitas;
