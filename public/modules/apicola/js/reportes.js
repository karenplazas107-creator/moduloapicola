// Datos en memoria iniciales
    let currentTipo = window.ReportesConfig?.tipoReporte || "apiarios";
    let currentData = window.ReportesConfig?.previewData || [];
    const ultimosReportesData = window.ReportesConfig?.ultimosReportes || [];

    // Definición de metadatos por tipo de reporte
    const configReportes = {
        miel: {
            titulo: 'Producción de miel',
            desc: 'Suma los kilogramos registrados en las visitas vigentes. Las visitas anuladas no se cuentan.',
            labelApiario: 'APIARIO',
            labelEstado: 'ESTADO DE LA COLMENA',
            opcionesEstado: [
                { val: 'all', txt: 'Todos los estados' },
                { val: 'Activa', txt: 'Activa' },
                { val: 'En revisión', txt: 'En revisión' },
                { val: 'Inactiva', txt: 'Inactiva' }
            ],
            columnas: ['Apiario', 'Colmena', 'Última visita', 'Visitas', 'Miel (kg)', 'Responsable', 'Estado'],
            headersTh: ['Apiario', 'Colmena', 'Última visita', 'Visitas', 'Miel (kg)', 'Responsable', 'Estado'],
            renderRow: function(item) {
                const statusLower = (item.estado || '').toLowerCase();
                const statusClass = statusLower.includes('rev') ? 'revision' : (statusLower.includes('inact') || statusLower.includes('malo') ? 'inactiva' : 'activa');
                return `
                    <tr>
                        <td style="font-weight: 600; color: #0f172a; white-space: nowrap;">${item.apiario || '-'}</td>
                        <td style="white-space: nowrap;"><span class="rep-colmena-badge">${item.colmena || '-'}</span></td>
                        <td style="white-space: nowrap;">${item.ultima_visita || '-'}</td>
                        <td style="white-space: nowrap;"><strong style="font-weight: 700;">${item.visitas || 1}</strong> <span style="color:#64748b; font-size:0.75rem;">visitas</span></td>
                        <td style="font-weight: 800; color: #d97706; white-space: nowrap;">${parseFloat(item.miel_kg || 0).toFixed(2)} kg</td>
                        <td style="white-space: nowrap;">${item.responsable || '-'}</td>
                        <td style="white-space: nowrap;"><span class="rep-status-badge ${statusClass}">${item.estado || 'Activa'}</span></td>
                    </tr>
                `;
            }
        },
        colmenas: {
            titulo: 'Estado de colmenas',
            desc: 'Estado sanitario, registro de reinas, especies y última visita registrada en cada colmena.',
            labelApiario: 'APIARIO',
            labelEstado: 'ESTADO SANITARIO',
            opcionesEstado: [
                { val: 'all', txt: 'Todos los estados' },
                { val: 'Activa', txt: 'Activa' },
                { val: 'En revisión', txt: 'En revisión' },
                { val: 'Inactiva', txt: 'Inactiva' }
            ],
            columnas: ['Código', 'Apiario', 'Tipo de abeja', 'Instalación', 'Última visita', 'Estado', 'Responsable'],
            headersTh: ['Código', 'Apiario', 'Especie / Tipo', 'Instalación', 'Última visita', 'Estado', 'Responsable'],
            renderRow: function(item) {
                const statusLower = (item.estado || '').toLowerCase();
                const statusClass = statusLower.includes('rev') ? 'revision' : (statusLower.includes('inact') ? 'inactiva' : 'activa');
                return `
                    <tr>
                        <td style="white-space: nowrap;"><span class="rep-colmena-badge">${item.codigo || '-'}</span></td>
                        <td style="font-weight: 600; color: #0f172a; white-space: nowrap;">${item.apiario || '-'}</td>
                        <td style="white-space: nowrap;"><em>${item.tipo_abeja || 'Apis mellifera'}</em></td>
                        <td style="white-space: nowrap;">${item.instalacion || '-'}</td>
                        <td style="white-space: nowrap;">${item.ultima_visita || '-'}</td>
                        <td style="white-space: nowrap;"><span class="rep-status-badge ${statusClass}">${item.estado || 'Activa'}</span></td>
                        <td style="white-space: nowrap;">${item.responsable || '-'}</td>
                    </tr>
                `;
            }
        },
        inventario: {
            titulo: 'Inventario',
            desc: 'Existencias, control de stock mínimo, herramientas, EPP y material biológico del apiario.',
            labelApiario: 'BODEGA / UBICACIÓN',
            labelEstado: 'ESTADO DEL ÍTEM',
            opcionesEstado: [
                { val: 'all', txt: 'Todos los estados' },
                { val: 'Activo', txt: 'Activo' },
                { val: 'Inactivo', txt: 'Inactivo' }
            ],
            columnas: ['Código', 'Elemento', 'Categoría', 'Bodega', 'Stock actual', 'Stock mín', 'Estado', 'Responsable'],
            headersTh: ['Código', 'Elemento', 'Categoría', 'Bodega', 'Stock actual', 'Stock mín', 'Estado', 'Responsable'],
            renderRow: function(item) {
                const statusLower = (item.estado || '').toLowerCase();
                const statusClass = statusLower.includes('inact') ? 'inactivo' : 'activo';
                const bajoStock = item.cantidad <= item.stock_minimo;
                return `
                    <tr>
                        <td style="white-space: nowrap;"><span class="rep-colmena-badge">${item.codigo || '-'}</span></td>
                        <td style="font-weight: 600; color: #0f172a; white-space: nowrap;">${item.nombre || '-'}</td>
                        <td style="white-space: nowrap;"><span style="font-size: 0.76rem; background: #f1f5f9; padding: 2px 7px; border-radius: 4px; font-weight:600; color:#475569;">${item.categoria || '-'}</span></td>
                        <td style="white-space: nowrap;">${item.bodega || '-'}</td>
                        <td style="white-space: nowrap;">
                            <strong style="color: ${bajoStock ? '#dc2626' : '#0f172a'}; font-size: 0.95rem;">${item.cantidad}</strong>
                            <small style="color: #64748b;"> ${item.unidad || 'U'}</small>
                            ${bajoStock ? '<span style="color:#dc2626; font-size:0.68rem; font-weight:700; margin-left:4px;">⚠️ Bajo</span>' : ''}
                        </td>
                        <td style="color: #64748b; white-space: nowrap;">${item.stock_minimo}</td>
                        <td style="white-space: nowrap;"><span class="rep-status-badge ${statusClass}">${item.estado || 'Activo'}</span></td>
                        <td style="white-space: nowrap;">${item.responsable || '-'}</td>
                    </tr>
                `;
            }
        }
    };

    // Al cargar la página, dibujar tabla inicial
    document.addEventListener('DOMContentLoaded', function() {
        renderTabla(currentTipo, currentData);
    });

    // Cambiar tipo de reporte (Miel, Colmenas, Inventario)
    function seleccionarTipoReporte(tipo, element) {
        if (currentTipo === tipo) return;
        currentTipo = tipo;
        document.getElementById('inputTipoReporte').value = tipo;

        // Actualizar tarjetas activas
        document.querySelectorAll('.rep-type-card').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');

        // Actualizar textos e información
        const conf = configReportes[tipo];
        document.getElementById('infoReporteTitulo').textContent = conf.titulo;
        document.getElementById('infoReporteDesc').textContent = conf.desc;
        document.getElementById('labelApiarioFiltro').textContent = conf.labelApiario;
        document.getElementById('labelEstadoFiltro').textContent = conf.labelEstado;

        // Mostrar / ocultar fechas si es inventario
        const groupFechas = document.getElementById('groupFechas');
        if (tipo === 'inventario') {
            groupFechas.style.opacity = '0.45';
        } else {
            groupFechas.style.opacity = '1';
        }

        // Actualizar chips de columnas
        const chipsContainer = document.getElementById('chipsColumnas');
        chipsContainer.innerHTML = conf.columnas.map(c => `<span class="rep-column-chip">${c}</span>`).join('');

        // Actualizar opciones del select de estados
        const selectEstado = document.getElementById('selectEstado');
        selectEstado.innerHTML = conf.opcionesEstado.map(o => `<option value="${o.val}">${o.txt}</option>`).join('');

        // Actualizar lista de últimos reportes
        renderUltimosReportes(tipo);

        // Disparar carga de datos
        aplicarFiltros();
    }

    // Renderizar historial de reportes
    function renderUltimosReportes(tipo) {
        const listContainer = document.getElementById('listaUltimosReportes');
        const reportes = ultimosReportesData[tipo] || [];
        
        if (reportes.length === 0) {
            listContainer.innerHTML = `<div style="padding: 1rem 0; color: #94a3b8; font-size: 0.85rem;">No hay reportes recientes registrados para esta sección.</div>`;
            return;
        }

        listContainer.innerHTML = reportes.map(rep => `
            <div class="rep-history-item">
                <div class="rep-history-left">
                    <span class="rep-history-item-title">${rep.titulo}</span>
                    <div class="rep-history-item-meta">
                        <span class="rep-history-badge ${rep.formato.toLowerCase()}">${rep.formato}</span>
                        <span>·</span>
                        <span>${rep.registros} registros</span>
                        <span>·</span>
                        <span>${rep.autor}</span>
                    </div>
                </div>
                <div>
                    <a href="javascript:void(0)" onclick="descargarHistorico('${rep.formato}', '${rep.tipo}')" class="rep-history-download-link">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                </div>
            </div>
        `).join('');
    }

    // Aplicar filtros vía AJAX a la API
    function aplicarFiltros() {
        const spinner = document.getElementById('repLoadingSpinner');
        const tableWrapper = document.getElementById('repTableWrapper');
        
        spinner.style.display = 'block';
        tableWrapper.style.opacity = '0.3';

        const apiario = document.getElementById('selectApiario').value;
        const desde = document.getElementById('inputDesde').value;
        const hasta = document.getElementById('inputHasta').value;
        const estado = document.getElementById('selectEstado').value;

        const params = new URLSearchParams({
            tipo: currentTipo,
            apiario: apiario,
            desde: desde,
            hasta: hasta,
            estado: estado
        });

        fetch(`${window.ReportesConfig?.previewUrl || ""}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(response => {
            spinner.style.display = 'none';
            tableWrapper.style.opacity = '1';

            if (response.success) {
                currentData = response.data;
                renderTabla(currentTipo, currentData);
            }
        })
        .catch(err => {
            console.error('Error al consultar datos:', err);
            spinner.style.display = 'none';
            tableWrapper.style.opacity = '1';
        });
    }

    // Dibujar la tabla con el arreglo de datos
    function renderTabla(tipo, items) {
        const conf = configReportes[tipo];
        const thead = document.getElementById('tablaPreviewHead');
        const tbody = document.getElementById('tablaPreviewBody');
        const badge = document.getElementById('badgeTotalRegistros');

        badge.textContent = `${items.length} registros`;

        // Encabezados
        thead.innerHTML = `<tr>${conf.headersTh.map(h => `<th>${h}</th>`).join('')}</tr>`;

        // Cuerpo
        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${conf.headersTh.length}" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 8px; opacity: 0.5;"></i>
                        <p style="margin: 0; font-size: 0.9rem;">No se encontraron registros con los filtros seleccionados.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = items.map(conf.renderRow).join('');
    }

    // Desplazarse suavemente a la vista previa
    function scrollToPreview() {
        const el = document.getElementById('seccionVistaPrevia');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Exportar a Excel (CSV con formato Excel y BOM)
    function exportarExcel() {
        const apiario = document.getElementById('selectApiario').value;
        const desde = document.getElementById('inputDesde').value;
        const hasta = document.getElementById('inputHasta').value;
        const estado = document.getElementById('selectEstado').value;

        const url = `${window.ReportesConfig?.excelUrl || ""}?tipo=${currentTipo}&apiario=${encodeURIComponent(apiario)}&desde=${desde}&hasta=${hasta}&estado=${encodeURIComponent(estado)}`;
        window.location.href = url;
    }

    // Exportar a PDF / Vista de Impresión Oficial SENA
    function exportarPdf() {
        const apiario = document.getElementById('selectApiario').value;
        const desde = document.getElementById('inputDesde').value;
        const hasta = document.getElementById('inputHasta').value;
        const estado = document.getElementById('selectEstado').value;

        const url = `${window.ReportesConfig?.pdfUrl || ""}?tipo=${currentTipo}&apiario=${encodeURIComponent(apiario)}&desde=${desde}&hasta=${hasta}&estado=${encodeURIComponent(estado)}`;
        window.open(url, '_blank');
    }

    // Descargar desde el historial
    function descargarHistorico(formato, tipo) {
        if (formato.toLowerCase() === 'excel') {
            const url = `${window.ReportesConfig?.excelUrl || ""}?tipo=${tipo}`;
            window.location.href = url;
        } else {
            abrirModalReporteFlotante(tipo);
        }
    }

    // ================= FUNCIONES DEL MODAL FLOTANTE =================
    function abrirModalReporteFlotante(tipoOverride) {
        const tipo = tipoOverride || currentTipo;
        const modal = document.getElementById('modalReporteFlotanteBackdrop');
        const container = document.getElementById('repFloatSheet');
        const desdeVal = document.getElementById('inputDesde').value;
        const hastaVal = document.getElementById('inputHasta').value;

        // Formato amigable de fechas
        const dObj = new Date(desdeVal + 'T00:00:00');
        const hObj = new Date(hastaVal + 'T00:00:00');
        const dStr = (!isNaN(dObj.getTime())) ? dObj.toLocaleDateString('es-CO') : desdeVal;
        const hStr = (!isNaN(hObj.getTime())) ? hObj.toLocaleDateString('es-CO') : hastaVal;

        let tituloRep = 'Reporte de Producción y Cosecha de Miel';
        if (tipo === 'colmenas') tituloRep = 'Censo y Estado Sanitario de Colmenas';
        if (tipo === 'inventario') tituloRep = 'Inventario de Insumos, EPP y Material Apícola';

        let totalMiel = 0;
        let totalVisitas = 0;
        let totalItems = 0;

        let theadHtml = '';
        let tbodyHtml = '';

        if (tipo === 'miel') {
            theadHtml = `
                <tr>
                    <th style="width: 25%; text-align: left;">Apiario</th>
                    <th style="width: 12%; text-align: center;">Colmena</th>
                    <th style="width: 14%; text-align: center;">Última Visita</th>
                    <th style="width: 10%; text-align: center;">Visitas</th>
                    <th style="width: 12%; text-align: right;">Miel (kg)</th>
                    <th style="width: 15%; text-align: left;">Responsable</th>
                    <th style="width: 12%; text-align: center;">Estado</th>
                </tr>
            `;
            if (currentData.length === 0) {
                tbodyHtml = `<tr><td colspan="7" style="text-align:center; padding:1.5rem; color:#94a3b8;">No se encontraron registros en la base de datos para los criterios seleccionados.</td></tr>`;
            } else {
                tbodyHtml = currentData.map(r => {
                    totalMiel += parseFloat(r.miel_kg || 0);
                    totalVisitas += parseInt(r.visitas || 1);
                    const st = r.estado || 'Activa';
                    const stLower = st.toLowerCase();
                    const stClass = stLower.includes('rev') ? 'revision' : (stLower.includes('inact') ? 'inactiva' : 'activa');
                    return `
                        <tr>
                            <td style="text-align: left;" title="${r.apiario || '-'}"><strong>${r.apiario || '-'}</strong></td>
                            <td style="text-align: center;"><span class="badge-colmena-print">${r.colmena || '-'}</span></td>
                            <td style="text-align: center;">${r.ultima_visita || '-'}</td>
                            <td style="text-align: center;">${r.visitas || 1}</td>
                            <td style="text-align: right;"><strong style="color:#d97706;">${parseFloat(r.miel_kg || 0).toFixed(2)} kg</strong></td>
                            <td style="text-align: left;" title="${r.responsable || '-'}">${r.responsable || '-'}</td>
                            <td style="text-align: center;"><span class="badge-status-print ${stClass}">${st}</span></td>
                        </tr>
                    `;
                }).join('');

                tbodyHtml += `
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right; text-transform:uppercase;">Total Consolidado Miel:</td>
                        <td style="text-align:right; color:#d97706; font-weight:800;">${totalMiel.toFixed(2)} kg</td>
                        <td colspan="2"></td>
                    </tr>
                `;
            }
        } else if (tipo === 'colmenas') {
            theadHtml = `
                <tr>
                    <th style="width: 12%; text-align: center;">Código</th>
                    <th style="width: 24%; text-align: left;">Apiario</th>
                    <th style="width: 18%; text-align: left;">Especie / Tipo</th>
                    <th style="width: 12%; text-align: center;">Instalación</th>
                    <th style="width: 12%; text-align: center;">Última Visita</th>
                    <th style="width: 10%; text-align: center;">Estado</th>
                    <th style="width: 12%; text-align: left;">Responsable</th>
                </tr>
            `;
            if (currentData.length === 0) {
                tbodyHtml = `<tr><td colspan="7" style="text-align:center; padding:1.5rem; color:#94a3b8;">No hay colmenas registradas en la base de datos.</td></tr>`;
            } else {
                tbodyHtml = currentData.map(r => {
                    const st = r.estado || 'Activa';
                    const stLower = st.toLowerCase();
                    const stClass = stLower.includes('rev') ? 'revision' : (stLower.includes('inact') ? 'inactiva' : 'activa');
                    return `
                        <tr>
                            <td style="text-align: center;"><span class="badge-colmena-print">${r.codigo || '-'}</span></td>
                            <td style="text-align: left;" title="${r.apiario || '-'}"><strong>${r.apiario || '-'}</strong></td>
                            <td style="text-align: left;"><em>${r.tipo_abeja || 'Apis mellifera'}</em></td>
                            <td style="text-align: center;">${r.instalacion || '-'}</td>
                            <td style="text-align: center;">${r.ultima_visita || '-'}</td>
                            <td style="text-align: center;"><span class="badge-status-print ${stClass}">${st}</span></td>
                            <td style="text-align: left;" title="${r.responsable || '-'}">${r.responsable || '-'}</td>
                        </tr>
                    `;
                }).join('');
            }
        } else { // inventario
            theadHtml = `
                <tr>
                    <th style="width: 11%; text-align: center;">Código</th>
                    <th style="width: 24%; text-align: left;">Elemento</th>
                    <th style="width: 14%; text-align: left;">Categoría</th>
                    <th style="width: 13%; text-align: left;">Bodega</th>
                    <th style="width: 13%; text-align: right;">Stock Actual</th>
                    <th style="width: 8%; text-align: center;">Stock Mín</th>
                    <th style="width: 8%; text-align: center;">Estado</th>
                    <th style="width: 9%; text-align: left;">Responsable</th>
                </tr>
            `;
            if (currentData.length === 0) {
                tbodyHtml = `<tr><td colspan="8" style="text-align:center; padding:1.5rem; color:#94a3b8;">No hay elementos de inventario registrados.</td></tr>`;
            } else {
                tbodyHtml = currentData.map(r => {
                    totalItems += parseInt(r.cantidad || 0);
                    const st = r.estado || 'Activo';
                    const stLower = st.toLowerCase();
                    const stClass = stLower.includes('inact') ? 'inactiva' : 'activa';
                    return `
                        <tr>
                            <td style="text-align: center;"><span class="badge-colmena-print">${r.codigo || '-'}</span></td>
                            <td style="text-align: left;" title="${r.nombre || '-'}"><strong>${r.nombre || '-'}</strong></td>
                            <td style="text-align: left;">${r.categoria || '-'}</td>
                            <td style="text-align: left;">${r.bodega || '-'}</td>
                            <td style="text-align: right;"><strong>${r.cantidad || 0} ${r.unidad || ''}</strong></td>
                            <td style="text-align: center;">${r.stock_minimo || 0}</td>
                            <td style="text-align: center;"><span class="badge-status-print ${stClass}">${st}</span></td>
                            <td style="text-align: left;" title="${r.responsable || '-'}">${r.responsable || '-'}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        // Mini KPIs
        let kpisHtml = '';
        if (tipo === 'miel') {
            kpisHtml = `
                <div class="kpi-mini-card amber">
                    <span class="kpi-mini-label">Miel Cosechada</span>
                    <span class="kpi-mini-value amber">${totalMiel.toFixed(2)} kg</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total Visitas</span>
                    <span class="kpi-mini-value green">${totalVisitas}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Colmenas Evaluadas</span>
                    <span class="kpi-mini-value">${currentData.length}</span>
                </div>
            `;
        } else if (tipo === 'colmenas') {
            kpisHtml = `
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total Colmenas</span>
                    <span class="kpi-mini-value green">${currentData.length}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Apiarios Involucrados</span>
                    <span class="kpi-mini-value">${new Set(currentData.map(d => d.apiario)).size}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Censo</span>
                    <span class="kpi-mini-value">Actualizado</span>
                </div>
            `;
        } else {
            kpisHtml = `
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total en Stock</span>
                    <span class="kpi-mini-value green">${totalItems} unidades</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Ítems Registrados</span>
                    <span class="kpi-mini-value">${currentData.length}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Estado Bodega</span>
                    <span class="kpi-mini-value">Activo</span>
                </div>
            `;
        }

        const userName = window.ReportesConfig?.userName || "Administrador";
        const nowStr = new Date().toLocaleString('es-CO');

        container.innerHTML = `
            <div class="rep-header">
                <div class="rep-header-left">
                    <img src="${window.ReportesConfig?.logoUrl || ""}" 
                         alt="SENA Logo" 
                         class="rep-logo"
                         onerror="this.onerror=null; this.src='${window.ReportesConfig?.logoFallback || ""}';">
                    <div>
                        <div class="rep-inst-sena">Servicio Nacional de Aprendizaje • SENA</div>
                        <div class="rep-inst-cefa">Centro de Formación Agroindustrial La Angostura</div>
                        <div class="rep-app-brand">SENA <span>APÍCOLA</span></div>
                    </div>
                </div>
                <div class="rep-header-right">
                    <div><strong>Fecha de emisión:</strong> ${nowStr}</div>
                    <div><strong>Generado por:</strong> ${userName}</div>
                    <div><strong>Rol:</strong> Administrador Apícola</div>
                </div>
            </div>

            <div class="rep-title-strip">
                <div class="rep-title-text">${tituloRep}</div>
                <div class="rep-badge-periodo">Período: ${dStr} al ${hStr}</div>
            </div>

            <div class="rep-kpis-bar">
                ${kpisHtml}
            </div>

            <table class="rep-table">
                <thead>
                    ${theadHtml}
                </thead>
                <tbody>
                    ${tbodyHtml}
                </tbody>
            </table>

            <div class="rep-signatures">
                <div class="rep-sign-box">
                    <div class="sign-name">${userName}</div>
                    <div class="sign-charge">Administrador Unidad Apícola · SENA CEFA</div>
                </div>
                <div class="rep-sign-box">
                    <div class="sign-name">Sergio Barrera</div>
                    <div class="sign-charge">Instructor / Apicultor Técnico Responsable</div>
                </div>
            </div>

            <footer class="rep-footer">
                <div>SENA Centro de Formación Agroindustrial La Angostura • Campoalegre, Huila</div>
                <div>Sistema de Información ERP SENA Empresa • SENA Apícola</div>
            </footer>
        `;

        modal.classList.add('is-open');
    }

    function cerrarModalReporteFlotante() {
        const modal = document.getElementById('modalReporteFlotanteBackdrop');
        if (modal) modal.classList.remove('is-open');
    }

    // Imprimir "ahí mismo" en la misma pantalla sin abrir pestañas y garantizando 1 sola página
    function imprimirReporteAhiMismo() {
        const sheet = document.getElementById('repFloatSheet');
        if (!sheet) return;

        let printFrame = document.getElementById('repPrintIframe');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'repPrintIframe';
            printFrame.style.position = 'fixed';
            printFrame.style.right = '0';
            printFrame.style.bottom = '0';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = '0';
            document.body.appendChild(printFrame);
        }

        const doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Reporte SENA APÍCOLA</title>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
                <style>
                    @page {
                        size: letter portrait;
                        margin: 6mm 8mm 6mm 8mm;
                    }
                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }
                    html, body {
                        background: #ffffff !important;
                        color: #0f172a;
                        font-family: 'Inter', sans-serif;
                        font-size: 10px;
                        line-height: 1.25;
                        height: 100% !important;
                        overflow: hidden !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .rep-float-sheet {
                        width: 100% !important;
                        max-width: 100% !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        box-shadow: none !important;
                        border: none !important;
                        page-break-inside: avoid !important;
                        page-break-after: avoid !important;
                        overflow: hidden !important;
                    }
                    .rep-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        border-bottom: 2px solid #047857 !important;
                        padding-bottom: 8px;
                        margin-bottom: 8px;
                    }
                    .rep-header-left {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .rep-logo {
                        width: 40px;
                        height: 40px;
                        object-fit: contain;
                        border-radius: 6px;
                        border: 1px solid #e2e8f0;
                        padding: 2px;
                    }
                    .rep-inst-sena {
                        font-size: 0.65rem;
                        font-weight: 800;
                        color: #047857 !important;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .rep-inst-cefa {
                        font-size: 0.62rem;
                        color: #64748b;
                    }
                    .rep-app-brand {
                        font-family: 'Plus Jakarta Sans', sans-serif;
                        font-size: 1.1rem;
                        font-weight: 800;
                        color: #0f172a;
                        margin-top: 1px;
                        line-height: 1.1;
                    }
                    .rep-app-brand span {
                        color: #d97706;
                    }
                    .rep-header-right {
                        text-align: right;
                        font-size: 0.7rem;
                        color: #475569;
                        line-height: 1.35;
                    }
                    .rep-header-right strong {
                        color: #0f172a;
                    }
                    .rep-title-strip {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        background: #f8fafc !important;
                        border: 1px solid #e2e8f0 !important;
                        border-left: 4px solid #047857 !important;
                        color: #0f172a !important;
                        padding: 6px 12px;
                        border-radius: 5px;
                        margin-bottom: 8px;
                    }
                    .rep-title-text {
                        font-family: 'Plus Jakarta Sans', sans-serif;
                        font-size: 0.88rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        color: #0f172a !important;
                    }
                    .rep-badge-periodo {
                        font-size: 0.65rem;
                        font-weight: 700;
                        background: #ecfdf5 !important;
                        color: #047857 !important;
                        border: 1px solid #a7f3d0 !important;
                        padding: 2px 8px;
                        border-radius: 10px;
                    }
                    .rep-kpis-bar {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 6px;
                        margin-bottom: 8px;
                    }
                    .kpi-mini-card {
                        background: #f8fafc !important;
                        border: 1px solid #e2e8f0;
                        border-left: 3px solid #047857 !important;
                        border-radius: 5px;
                        padding: 5px 8px;
                    }
                    .kpi-mini-card.amber {
                        border-left-color: #f59e0b !important;
                    }
                    .kpi-mini-label {
                        font-size: 0.6rem;
                        font-weight: 800;
                        color: #64748b;
                        text-transform: uppercase;
                    }
                    .kpi-mini-value {
                        font-family: 'Plus Jakarta Sans', sans-serif;
                        font-size: 0.98rem;
                        font-weight: 800;
                        color: #0f172a;
                        margin-top: 1px;
                    }
                    .kpi-mini-value.amber {
                        color: #b45309;
                    }
                    .kpi-mini-value.green {
                        color: #047857 !important;
                    }
                    .rep-table {
                        width: 100% !important;
                        table-layout: fixed !important;
                        border-collapse: collapse !important;
                        font-size: 0.68rem !important;
                        margin-bottom: 8px !important;
                    }
                    .rep-table th {
                        background: #0b2e23 !important;
                        color: #ffffff !important;
                        font-weight: 700;
                        font-size: 0.62rem;
                        text-transform: uppercase;
                        padding: 5px 6px !important;
                        border: 1px solid #0b2e23 !important;
                        white-space: nowrap !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                    }
                    .rep-table td {
                        padding: 4.5px 6px !important;
                        border: 1px solid #e2e8f0 !important;
                        color: #1e293b;
                        vertical-align: middle;
                        white-space: nowrap !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                    }
                    .rep-table tr:nth-child(even) td {
                        background: #f8fafc;
                    }
                    .rep-table tr.total-row td {
                        background: #f0fdf4 !important;
                        font-weight: 800;
                        border-top: 1.5px solid #047857 !important;
                        color: #0f172a;
                    }
                    .badge-colmena-print {
                        display: inline-block;
                        font-weight: 800;
                        color: #047857 !important;
                        background: #f0fdf4 !important;
                        border: 1px solid #bbf7d0 !important;
                        padding: 1px 5px;
                        border-radius: 4px;
                        font-size: 0.68rem;
                        white-space: nowrap;
                    }
                    .badge-status-print {
                        display: inline-block;
                        font-size: 0.6rem;
                        font-weight: 700;
                        padding: 1px 5px;
                        border-radius: 4px;
                        border: 1px solid #cbd5e1;
                        background: #f8fafc;
                        color: #334155;
                        white-space: nowrap;
                    }
                    .badge-status-print.activa {
                        background: #ecfdf5 !important;
                        color: #047857 !important;
                        border-color: #a7f3d0 !important;
                    }
                    .badge-status-print.revision {
                        background: #fffbeb !important;
                        color: #b45309 !important;
                        border-color: #fde68a !important;
                    }
                    .badge-status-print.inactiva {
                        background: #fef2f2 !important;
                        color: #b91c1c !important;
                        border-color: #fecaca !important;
                    }
                    .rep-signatures {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 2rem;
                        margin-top: 1.25rem;
                        padding-top: 4px;
                        page-break-inside: avoid !important;
                    }
                    .rep-sign-box {
                        text-align: center;
                        border-top: 1px solid #94a3b8;
                        padding-top: 3px;
                    }
                    .sign-name {
                        font-size: 0.72rem;
                        font-weight: 700;
                        color: #0f172a;
                    }
                    .sign-charge {
                        font-size: 0.62rem;
                        color: #64748b;
                    }
                    .rep-footer {
                        margin-top: 1rem;
                        padding-top: 4px;
                        border-top: 1px solid #e2e8f0;
                        display: flex;
                        justify-content: space-between;
                        font-size: 0.6rem;
                        color: #94a3b8;
                        page-break-inside: avoid !important;
                    }
                </style>
            </head>
            <body>
                ${sheet.outerHTML}
            </body>
            </html>
        `);
        doc.close();

        setTimeout(() => {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 300);
    }

    // Atajo de teclado: Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalReporteFlotante();
        }
    });
