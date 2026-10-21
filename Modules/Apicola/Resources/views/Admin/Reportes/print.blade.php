<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Oficial SENA APÍCOLA - {{ ucfirst($tipo) }}</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        @page {
            size: letter portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            font-size: 11px;
            line-height: 1.35;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Barra Flotante de Acciones en Pantalla */
        .no-print-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 820px;
            margin: 14px auto;
            padding: 10px 18px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.4);
            border: 1px solid #1e293b;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 15px;
            border-radius: 7px;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-print {
            background: #047857;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.35);
        }

        .btn-print:hover {
            background: #065f46;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-close {
            background: rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-close:hover {
            background: rgba(239, 68, 68, 0.85);
            color: #ffffff;
            border-color: #ef4444;
        }

        /* Hoja de Reporte Diseñada para 1 Sola Página */
        .sheet {
            max-width: 820px;
            margin: 0 auto 1.5rem auto;
            background: #ffffff;
            padding: 26px 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            page-break-inside: avoid;
            page-break-after: avoid;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Encabezado Institucional SENA */
        .rep-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #047857;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .rep-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .rep-logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            padding: 2px;
            background: #ffffff;
        }

        .rep-inst-sena {
            font-size: 0.7rem;
            font-weight: 800;
            color: #047857;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .rep-inst-cefa {
            font-size: 0.65rem;
            color: #64748b;
            font-weight: 500;
        }

        .rep-app-brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.15rem;
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
            font-size: 0.72rem;
            color: #475569;
            line-height: 1.35;
        }

        .rep-header-right strong {
            color: #0f172a;
        }

        /* Banner de Título del Reporte */
        .rep-title-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #047857;
            color: #0f172a;
            padding: 9px 14px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .rep-title-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.2px;
        }

        .rep-badge-periodo {
            font-size: 0.7rem;
            font-weight: 700;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 3px 10px;
            border-radius: 12px;
            letter-spacing: 0.2px;
        }

        /* Mini Tarjetas de KPIs */
        .rep-kpis-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .kpi-mini-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #047857;
            border-radius: 6px;
            padding: 7px 11px;
            display: flex;
            flex-direction: column;
        }

        .kpi-mini-card.amber {
            border-left-color: #f59e0b;
        }

        .kpi-mini-label {
            font-size: 0.62rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .kpi-mini-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 1px;
        }

        .kpi-mini-value.amber {
            color: #b45309;
        }

        .kpi-mini-value.green {
            color: #047857;
        }

        /* Tabla Compacta y Elegante */
        .rep-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 0.73rem;
            margin-bottom: 12px;
            box-sizing: border-box;
        }

        .rep-table th {
            background: #0b2e23;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 7px 8px;
            border: 1px solid #0b2e23;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-sizing: border-box;
        }

        .rep-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            vertical-align: middle;
            font-size: 0.72rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-sizing: border-box;
        }

        .rep-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        .rep-table tr.total-row td {
            background: #f0fdf4;
            font-weight: 800;
            border-top: 1.5px solid #047857;
            color: #0f172a;
        }

        .badge-status-print {
            display: inline-block;
            font-size: 0.62rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            white-space: nowrap;
            max-width: 100%;
            box-sizing: border-box;
        }

        .badge-status-print.activa {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }

        .badge-status-print.revision {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }

        .badge-status-print.inactiva {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .badge-colmena-print {
            display: inline-block;
            font-weight: 800;
            color: #047857;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 0.68rem;
            white-space: nowrap;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Sección de Firmas Compacta */
        .rep-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.5rem;
            margin-top: 1.5rem;
            padding-top: 0.5rem;
            page-break-inside: avoid;
        }

        .rep-sign-box {
            text-align: center;
            border-top: 1px solid #94a3b8;
            padding-top: 4px;
        }

        .sign-name {
            font-size: 0.76rem;
            font-weight: 700;
            color: #0f172a;
        }

        .sign-charge {
            font-size: 0.65rem;
            color: #64748b;
        }

        /* Pie de Página */
        .rep-footer {
            margin-top: 1.25rem;
            padding-top: 5px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.62rem;
            color: #94a3b8;
            page-break-inside: avoid;
        }

        /* Ajustes Estrictos de Impresión (1 Sola Página) */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                height: 100% !important;
                overflow: hidden !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }

            .rep-title-strip {
                background: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                border-left: 4px solid #047857 !important;
                color: #0f172a !important;
            }

            .rep-title-text {
                color: #0f172a !important;
            }

            .rep-table th {
                background: #0b2e23 !important;
                color: #ffffff !important;
            }

            .kpi-mini-card {
                background: #f8fafc !important;
            }

            table, tr, td, th, div, header, footer {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Barra de Control Flotante -->
    <div class="no-print-bar">
        <div style="font-size: 0.85rem;">
            <i class="fas fa-file-pdf" style="color: #a7f3d0; margin-right: 6px;"></i>
            <strong>Reporte Oficial SENA APÍCOLA</strong> · Formato ejecutivo optimizado para 1 página
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-action btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir / Guardar en PDF
            </button>
            <button class="btn-action btn-close" onclick="window.close()">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    </div>

    <!-- Hoja de Reporte (A4 / Carta) -->
    <div class="sheet">

        <!-- 1. Encabezado Oficial -->
        <header class="rep-header">
            <div class="rep-header-left">
                <img src="{{ asset('modules/apicola/img/logo.png') }}" 
                     alt="SENA Logo" 
                     class="rep-logo"
                     onerror="this.onerror=null; this.src='{{ asset('apicola-assets/assets/img/logo-apicola-bee-original.png') }}';">
                <div>
                    <div class="rep-inst-sena">Servicio Nacional de Aprendizaje • SENA</div>
                    <div class="rep-inst-cefa">Centro de Formación Agroindustrial La Angostura</div>
                    <div class="rep-app-brand">SENA <span>APÍCOLA</span></div>
                </div>
            </div>

            <div class="rep-header-right">
                <div><strong>Fecha de emisión:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
                <div><strong>Generado por:</strong> {{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Administrador') }}</div>
                <div><strong>Rol:</strong> {{ Auth::user()?->primary_role ?? 'Administrador Apícola' }}</div>
            </div>
        </header>

        <!-- 2. Barra de Título del Reporte -->
        <div class="rep-title-strip">
            <div class="rep-title-text">
                @if($tipo === 'miel')
                    Reporte de Producción y Cosecha de Miel
                @elseif($tipo === 'colmenas')
                    Censo y Estado Sanitario de Colmenas
                @else
                    Inventario de Insumos, EPP y Material Apícola
                @endif
            </div>
            <div class="rep-badge-periodo">
                Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
            </div>
        </div>

        <!-- 3. KPIs del Reporte (Mini Tarjetas) -->
        <div class="rep-kpis-bar">
            @if($tipo === 'miel')
                @php
                    $totalMiel = array_sum(array_column($data, 'miel_kg'));
                    $totalVisitas = array_sum(array_column($data, 'visitas'));
                @endphp
                <div class="kpi-mini-card amber">
                    <span class="kpi-mini-label">Miel Cosechada</span>
                    <span class="kpi-mini-value amber">{{ number_format($totalMiel, 2, ',', '.') }} kg</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total Visitas / Registros</span>
                    <span class="kpi-mini-value green">{{ $totalVisitas }}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Colmenas Evaluadas</span>
                    <span class="kpi-mini-value">{{ count($data) }}</span>
                </div>
            @elseif($tipo === 'colmenas')
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total Colmenas</span>
                    <span class="kpi-mini-value green">{{ count($data) }}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Apiarios Involucrados</span>
                    <span class="kpi-mini-value">{{ count(array_unique(array_column($data, 'apiario'))) }}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Estado del Censo</span>
                    <span class="kpi-mini-value">Actualizado</span>
                </div>
            @else
                @php
                    $totalItems = array_sum(array_column($data, 'cantidad'));
                @endphp
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total Elementos en Stock</span>
                    <span class="kpi-mini-value green">{{ $totalItems }} unidades</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Ítems Registrados</span>
                    <span class="kpi-mini-value">{{ count($data) }}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Estado de Bodega</span>
                    <span class="kpi-mini-value">Activo</span>
                </div>
            @endif
        </div>

        <!-- 4. Tabla Compacta de Datos Reales -->
        <table class="rep-table">
            <thead>
                @if($tipo === 'miel')
                    <tr>
                        <th style="width: 25%; text-align: left;">Apiario</th>
                        <th style="width: 12%; text-align: center;">Colmena</th>
                        <th style="width: 14%; text-align: center;">Última Visita</th>
                        <th style="width: 10%; text-align: center;">Visitas</th>
                        <th style="width: 12%; text-align: right;">Miel (kg)</th>
                        <th style="width: 15%; text-align: left;">Responsable</th>
                        <th style="width: 12%; text-align: center;">Estado</th>
                    </tr>
                @elseif($tipo === 'colmenas')
                    <tr>
                        <th style="width: 12%; text-align: center;">Código</th>
                        <th style="width: 24%; text-align: left;">Apiario</th>
                        <th style="width: 18%; text-align: left;">Especie / Tipo</th>
                        <th style="width: 12%; text-align: center;">Instalación</th>
                        <th style="width: 12%; text-align: center;">Última Visita</th>
                        <th style="width: 10%; text-align: center;">Estado</th>
                        <th style="width: 12%; text-align: left;">Responsable</th>
                    </tr>
                @else
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
                @endif
            </thead>
            <tbody>
                @forelse($data as $row)
                    @if($tipo === 'miel')
                        <tr>
                            <td style="text-align: left;"><strong>{{ $row['apiario'] ?? '-' }}</strong></td>
                            <td style="text-align: center;"><span class="badge-colmena-print">{{ $row['colmena'] ?? '-' }}</span></td>
                            <td style="text-align: center;">{{ $row['ultima_visita'] ?? '-' }}</td>
                            <td style="text-align: center;">{{ $row['visitas'] ?? 1 }}</td>
                            <td style="text-align: right;"><strong style="color: #d97706;">{{ number_format((float)($row['miel_kg'] ?? 0), 2, ',', '.') }} kg</strong></td>
                            <td style="text-align: left;">{{ $row['responsable'] ?? '-' }}</td>
                            <td style="text-align: center;">
                                <span class="badge-status-print {{ stripos($row['estado'] ?? '', 'rev') !== false ? 'revision' : (stripos($row['estado'] ?? '', 'inact') !== false ? 'inactiva' : 'activa') }}">
                                    {{ $row['estado'] ?? 'Activa' }}
                                </span>
                            </td>
                        </tr>
                    @elseif($tipo === 'colmenas')
                        <tr>
                            <td style="text-align: center;"><span class="badge-colmena-print">{{ $row['codigo'] ?? '-' }}</span></td>
                            <td style="text-align: left;"><strong>{{ $row['apiario'] ?? '-' }}</strong></td>
                            <td style="text-align: left;"><em>{{ $row['tipo_abeja'] ?? 'Apis mellifera' }}</em></td>
                            <td style="text-align: center;">{{ $row['instalacion'] ?? '-' }}</td>
                            <td style="text-align: center;">{{ $row['ultima_visita'] ?? '-' }}</td>
                            <td style="text-align: center;">
                                <span class="badge-status-print {{ stripos($row['estado'] ?? '', 'rev') !== false ? 'revision' : (stripos($row['estado'] ?? '', 'inact') !== false ? 'inactiva' : 'activa') }}">
                                    {{ $row['estado'] ?? 'Activa' }}
                                </span>
                            </td>
                            <td style="text-align: left;">{{ $row['responsable'] ?? '-' }}</td>
                        </tr>
                    @else
                        <tr>
                            <td style="text-align: center;"><span class="badge-colmena-print">{{ $row['codigo'] ?? '-' }}</span></td>
                            <td style="text-align: left;"><strong>{{ $row['nombre'] ?? '-' }}</strong></td>
                            <td style="text-align: left;">{{ $row['categoria'] ?? '-' }}</td>
                            <td style="text-align: left;">{{ $row['bodega'] ?? '-' }}</td>
                            <td style="text-align: right;"><strong>{{ $row['cantidad'] ?? 0 }} {{ $row['unidad'] ?? '' }}</strong></td>
                            <td style="text-align: center;">{{ $row['stock_minimo'] ?? 0 }}</td>
                            <td style="text-align: center;"><span class="badge-status-print activa">{{ $row['estado'] ?? 'Activo' }}</span></td>
                            <td style="text-align: left;">{{ $row['responsable'] ?? '-' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 1.5rem; color: #94a3b8;">
                            No hay registros almacenados en la base de datos para los criterios seleccionados.
                        </td>
                    </tr>
                @endforelse

                @if(count($data) > 0 && $tipo === 'miel')
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right; text-transform: uppercase;">Total Consolidado Miel:</td>
                        <td style="text-align: right; color: #d97706; font-weight: 800;">{{ number_format(array_sum(array_column($data, 'miel_kg')), 2, ',', '.') }} kg</td>
                        <td colspan="2"></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- 5. Firmas Institucionales Compactas -->
        <div class="rep-signatures">
            <div class="rep-sign-box">
                <div class="sign-name">{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Administrador') }}</div>
                <div class="sign-charge">Administrador Unidad Apícola · SENA CEFA</div>
            </div>

            <div class="rep-sign-box">
                <div class="sign-name">Sergio Barrera</div>
                <div class="sign-charge">Instructor / Apicultor Técnico Responsable</div>
            </div>
        </div>

        <!-- 6. Pie de Página Oficial -->
        <footer class="rep-footer">
            <div>SENA Centro de Formación Agroindustrial La Angostura • Campoalegre, Huila</div>
            <div>Sistema de Información ERP SENA Empresa • SENA Apícola</div>
        </footer>

    </div>

</body>
</html>
