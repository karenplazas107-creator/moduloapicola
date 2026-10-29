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

    <link rel="stylesheet" href="{{ asset('modules/apicola/css/reportes-print.css') }}">
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
