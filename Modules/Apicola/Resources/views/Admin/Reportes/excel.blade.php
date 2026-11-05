<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    @php
        $msoXml = '<!--[if gte mso 9]><xml>'
            . '<' . 'x:ExcelWorkbook>'
            . '<' . 'x:ExcelWorksheets>'
            . '<' . 'x:ExcelWorksheet>'
            . '<' . 'x:Name>' . strtoupper($tipo) . '</' . 'x:Name>'
            . '<' . 'x:WorksheetOptions>'
            . '<' . 'x:DisplayGridlines/>'
            . '</' . 'x:WorksheetOptions>'
            . '</' . 'x:ExcelWorksheet>'
            . '</' . 'x:ExcelWorksheets>'
            . '</' . 'x:ExcelWorkbook>'
            . '</xml><![endif]-->';
        echo $msoXml;
    @endphp
    <style>
        body, table, td, th {
            font-family: Calibri, 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000000;
        }
        .header-main {
            background-color: #23783a;
            color: #ffffff;
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 38px;
            border: 1px solid #1a5c2a;
        }
        .header-sub {
            background-color: #d4edda;
            color: #1e7e34;
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 28px;
            border: 1px solid #c3e6cb;
        }
        .meta-label {
            font-weight: bold;
            color: #000000;
            height: 22px;
            vertical-align: middle;
        }
        .meta-val {
            color: #000000;
            height: 22px;
            vertical-align: middle;
        }
        .th-col {
            background-color: #23783a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 30px;
            border: 1px solid #1a5c2a;
            padding: 5px 8px;
        }
        .td-cell {
            border: 1px solid #d0d7de;
            padding: 5px 8px;
            vertical-align: middle;
            height: 22px;
        }
        .td-left {
            text-align: left;
        }
        .td-center {
            text-align: center;
        }
        .td-right {
            text-align: right;
            mso-number-format: "\#\,\#\#0\.00";
        }
        .badge-inactiva {
            background-color: #fce8e6;
            color: #c5221f;
            font-weight: bold;
            text-align: center;
            border: 1px solid #d0d7de;
        }
        .badge-activa {
            background-color: #e6f4ea;
            color: #137333;
            font-weight: bold;
            text-align: center;
            border: 1px solid #d0d7de;
        }
        .badge-warning {
            background-color: #fef7e0;
            color: #b06000;
            font-weight: bold;
            text-align: center;
            border: 1px solid #d0d7de;
        }
        .totales-row td {
            font-weight: bold;
            border-top: 2.5pt solid #23783a;
            border-bottom: 2pt solid #23783a;
            height: 26px;
            vertical-align: middle;
        }
        .resumen-title {
            color: #1a5c2a;
            font-size: 13pt;
            font-weight: bold;
            height: 32px;
            vertical-align: bottom;
        }
        .td-resumen-label {
            border: 1px solid #7f7f7f;
            padding: 4px 10px;
            font-size: 11pt;
            text-align: left;
            height: 22px;
        }
        .td-resumen-val {
            border: 1px solid #7f7f7f;
            padding: 4px 10px;
            font-size: 11pt;
            font-weight: bold;
            text-align: right;
            height: 22px;
        }
    </style>
</head>
<body>

@php
    $totalCols = ($tipo === 'inventario') ? 9 : 7;
@endphp

<table style="border-collapse: collapse; width: 100%;">
    <!-- 1. Encabezado Oficial Verde Oscuro -->
    <tr>
        <td colspan="{{ $totalCols }}" class="header-main">
            SENA - CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA
        </td>
    </tr>

    <!-- 2. Subtítulo Oficial Verde Claro -->
    <tr>
        <td colspan="{{ $totalCols }}" class="header-sub">
            @if($tipo === 'miel')
                SENA APÍCOLA - REPORTE OFICIAL DE MIEL
            @elseif($tipo === 'colmenas')
                SENA APÍCOLA - REPORTE OFICIAL DE ESTADO DE COLMENAS
            @else
                SENA APÍCOLA - REPORTE OFICIAL DE INVENTARIO
            @endif
        </td>
    </tr>

    <!-- 3. Fila Vacía de Separación -->
    <tr style="height: 14px;"><td colspan="{{ $totalCols }}"></td></tr>

    <!-- 4. Metadatos -->
    <tr>
        <td class="meta-label">Período:</td>
        <td colspan="{{ $totalCols - 1 }}" class="meta-val">{{ $desde }} al {{ $hasta }}</td>
    </tr>
    <tr>
        <td class="meta-label">Generado por:</td>
        <td colspan="{{ $totalCols - 1 }}" class="meta-val">{{ strtoupper($autor) }}</td>
    </tr>
    <tr>
        <td class="meta-label">Fecha de emisión:</td>
        <td colspan="{{ $totalCols - 1 }}" class="meta-val">{{ $fechaEmision }}</td>
    </tr>

    <!-- 5. Fila Vacía de Separación -->
    <tr style="height: 14px;"><td colspan="{{ $totalCols }}"></td></tr>

    <!-- 6. Encabezados de la Tabla Principal -->
    @if($tipo === 'miel')
        <tr>
            <th class="th-col" style="width: 280px;">Apiario</th>
            <th class="th-col" style="width: 110px;">Colmena</th>
            <th class="th-col" style="width: 120px;">Última Visita</th>
            <th class="th-col" style="width: 110px;">Total Visitas</th>
            <th class="th-col" style="width: 150px;">Miel Cosechada (kg)</th>
            <th class="th-col" style="width: 180px;">Responsable</th>
            <th class="th-col" style="width: 110px;">Estado</th>
        </tr>

        <!-- Datos Miel -->
        @forelse($data as $row)
            @php
                $st = strtolower($row['estado'] ?? '');
                $badgeClass = 'badge-activa';
                if (str_contains($st, 'inact')) {
                    $badgeClass = 'badge-inactiva';
                } elseif (str_contains($st, 'trata') || str_contains($st, 'cuaren') || str_contains($st, 'bajo')) {
                    $badgeClass = 'badge-warning';
                }
            @endphp
            <tr>
                <td class="td-cell td-left">{{ $row['apiario'] ?? '' }}</td>
                <td class="td-cell td-center">{{ $row['colmena'] ?? '' }}</td>
                <td class="td-cell td-center">{{ $row['ultima_visita'] ?? '' }}</td>
                <td class="td-cell td-center" style="mso-number-format: '0';">{{ $row['visitas'] ?? 1 }}</td>
                <td class="td-cell td-right">{{ number_format((float)($row['miel_kg'] ?? 0), 2, ',', '.') }}</td>
                <td class="td-cell td-left">{{ strtoupper($row['responsable'] ?? '') }}</td>
                <td class="td-cell {{ $badgeClass }}">{{ ucfirst($row['estado'] ?? 'Activa') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="td-cell td-center" style="font-style: italic; color: #757575;">
                    No se encontraron registros de producción de miel para el período seleccionado.
                </td>
            </tr>
        @endforelse

        <!-- Filas de cuadrícula para mantener la estética de plantilla Excel -->
        @for($i = count($data); $i < 6; $i++)
            <tr>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
            </tr>
        @endfor

        <!-- Fila TOTALES Miel con línea verde superior -->
        <tr class="totales-row">
            <td class="td-left" style="font-weight: bold;">TOTALES</td>
            <td></td>
            <td></td>
            <td class="td-center" style="font-weight: bold; mso-number-format: '0';">{{ $totalVisitas }}</td>
            <td class="td-right" style="font-weight: bold;">{{ number_format($totalMiel, 2, ',', '.') }}</td>
            <td></td>
            <td></td>
        </tr>

    @elseif($tipo === 'colmenas')
        <tr>
            <th class="th-col" style="width: 110px;">Código</th>
            <th class="th-col" style="width: 250px;">Apiario</th>
            <th class="th-col" style="width: 160px;">Especie / Abeja</th>
            <th class="th-col" style="width: 130px;">Fecha Instalación</th>
            <th class="th-col" style="width: 120px;">Última Visita</th>
            <th class="th-col" style="width: 180px;">Responsable</th>
            <th class="th-col" style="width: 110px;">Estado</th>
        </tr>

        @forelse($data as $row)
            @php
                $st = strtolower($row['estado'] ?? '');
                $badgeClass = 'badge-activa';
                if (str_contains($st, 'inact')) {
                    $badgeClass = 'badge-inactiva';
                } elseif (str_contains($st, 'trata') || str_contains($st, 'cuaren') || str_contains($st, 'bajo')) {
                    $badgeClass = 'badge-warning';
                }
            @endphp
            <tr>
                <td class="td-cell td-center">{{ $row['codigo'] ?? '' }}</td>
                <td class="td-cell td-left">{{ $row['apiario'] ?? '' }}</td>
                <td class="td-cell td-center">{{ $row['tipo_abeja'] ?? 'Apis mellifera' }}</td>
                <td class="td-cell td-center">{{ $row['instalacion'] ?? '' }}</td>
                <td class="td-cell td-center">{{ $row['ultima_visita'] ?? '' }}</td>
                <td class="td-cell td-left">{{ strtoupper($row['responsable'] ?? '') }}</td>
                <td class="td-cell {{ $badgeClass }}">{{ ucfirst($row['estado'] ?? 'Activa') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="td-cell td-center" style="font-style: italic; color: #757575;">
                    No se encontraron colmenas registradas para los filtros aplicados.
                </td>
            </tr>
        @endforelse

        @for($i = count($data); $i < 6; $i++)
            <tr>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
            </tr>
        @endfor

        <tr class="totales-row">
            <td class="td-left" style="font-weight: bold;">TOTALES</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="td-right" style="font-weight: bold;">Total Colmenas:</td>
            <td class="td-center" style="font-weight: bold; mso-number-format: '0';">{{ count($data) }}</td>
        </tr>

    @else
        <!-- Inventario -->
        <tr>
            <th class="th-col" style="width: 100px;">Código</th>
            <th class="th-col" style="width: 220px;">Elemento</th>
            <th class="th-col" style="width: 130px;">Categoría</th>
            <th class="th-col" style="width: 150px;">Bodega</th>
            <th class="th-col" style="width: 110px;">Stock Actual</th>
            <th class="th-col" style="width: 110px;">Stock Mínimo</th>
            <th class="th-col" style="width: 100px;">Unidad</th>
            <th class="th-col" style="width: 160px;">Responsable</th>
            <th class="th-col" style="width: 110px;">Estado</th>
        </tr>

        @forelse($data as $row)
            @php
                $st = strtolower($row['estado'] ?? '');
                $badgeClass = 'badge-activa';
                if (str_contains($st, 'agotad') || str_contains($st, 'inact')) {
                    $badgeClass = 'badge-inactiva';
                } elseif (str_contains($st, 'bajo') || str_contains($st, 'revis')) {
                    $badgeClass = 'badge-warning';
                }
            @endphp
            <tr>
                <td class="td-cell td-center">{{ $row['codigo'] ?? '' }}</td>
                <td class="td-cell td-left">{{ $row['nombre'] ?? '' }}</td>
                <td class="td-cell td-center">{{ $row['categoria'] ?? '' }}</td>
                <td class="td-cell td-left">{{ $row['bodega'] ?? '' }}</td>
                <td class="td-cell td-center" style="mso-number-format: '0';">{{ $row['cantidad'] ?? 0 }}</td>
                <td class="td-cell td-center" style="mso-number-format: '0';">{{ $row['stock_minimo'] ?? 0 }}</td>
                <td class="td-cell td-center">{{ $row['unidad'] ?? 'Unidad' }}</td>
                <td class="td-cell td-left">{{ strtoupper($row['responsable'] ?? '') }}</td>
                <td class="td-cell {{ $badgeClass }}">{{ ucfirst($row['estado'] ?? 'Disponible') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="td-cell td-center" style="font-style: italic; color: #757575;">
                    No se encontraron elementos de inventario registrados.
                </td>
            </tr>
        @endforelse

        @for($i = count($data); $i < 6; $i++)
            <tr>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
                <td class="td-cell">&nbsp;</td>
            </tr>
        @endfor

        <tr class="totales-row">
            <td class="td-left" style="font-weight: bold;">TOTALES</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="td-center" style="font-weight: bold; mso-number-format: '0';">{{ array_sum(array_column($data, 'cantidad')) }}</td>
            <td class="td-center" style="font-weight: bold; mso-number-format: '0';">{{ array_sum(array_column($data, 'stock_minimo')) }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endif

    <!-- Espacio entre Totales y Resumen -->
    <tr style="height: 18px;"><td colspan="{{ $totalCols }}"></td></tr>
    <tr style="height: 14px;"><td colspan="{{ $totalCols }}"></td></tr>

    <!-- 7. Sección RESUMEN Oficial SENA -->
    <tr>
        <td colspan="2" class="resumen-title">RESUMEN</td>
        <td colspan="{{ $totalCols - 2 }}"></td>
    </tr>

    @if($tipo === 'miel')
        <tr>
            <td class="td-resumen-label">Total de colmenas registradas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ $totalColmenas }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Colmenas activas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ $colmenasActivas }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Colmenas inactivas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ $colmenasInactivas }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Miel cosechada total (kg)</td>
            <td class="td-resumen-val">{{ number_format($totalMiel, 2, ',', '.') }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Promedio de miel por colmena (kg)</td>
            <td class="td-resumen-val">{{ number_format($promedioMiel, 2, ',', '.') }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Total de visitas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ $totalVisitas }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>

    @elseif($tipo === 'colmenas')
        <tr>
            <td class="td-resumen-label">Total de colmenas registradas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ count($data) }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Colmenas activas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ $colmenasActivas }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Colmenas inactivas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ $colmenasInactivas }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Total de apiarios cubiertos</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ count(array_unique(array_column($data, 'apiario'))) }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>

    @else
        <tr>
            <td class="td-resumen-label">Total de elementos registrados</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ count($data) }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Total unidades en stock</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ array_sum(array_column($data, 'cantidad')) }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Elementos con stock bajo / agotado</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ count(array_filter($data, fn($r) => ($r['cantidad'] ?? 0) <= ($r['stock_minimo'] ?? 0))) }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
        <tr>
            <td class="td-resumen-label">Bodegas activas</td>
            <td class="td-resumen-val" style="mso-number-format: '0';">{{ count(array_unique(array_column($data, 'bodega'))) }}</td>
            <td colspan="{{ $totalCols - 2 }}"></td>
        </tr>
    @endif
</table>

</body>
</html>

