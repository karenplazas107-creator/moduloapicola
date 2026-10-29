<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Movimiento {{ $movimiento->numero_comprobante }} • SENA APÍCOLA</title>
    <style>
        @page {
            size: letter;
            margin: 1.5cm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #111827;
            background: #ffffff;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }

        .voucher-box {
            border: 2px solid #286043;
            border-radius: 12px;
            padding: 24px;
            max-width: 800px;
            margin: 0 auto;
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2ece4;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 800;
            color: #286043;
        }

        .brand-sub {
            font-size: 12px;
            color: #64748b;
        }

        .center-block {
            text-align: center;
        }

        .doc-title {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #111827;
        }

        .doc-number {
            font-size: 16px;
            font-weight: 900;
            color: #286043;
            margin-top: 4px;
        }

        .meta-block {
            text-align: right;
            font-size: 12px;
            line-height: 1.6;
        }

        .meta-block strong {
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        thead tr {
            background-color: #e2ece4;
        }

        th {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            font-weight: 800;
            text-align: left;
            font-size: 12px;
        }

        td {
            border: 1px solid #e2e8f0;
            padding: 9px 12px;
            font-size: 12px;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
        }

        .total-box {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 16px;
            background: #f8fafc;
        }

        .total-box .num {
            font-size: 20px;
            font-weight: 900;
            color: #286043;
        }

        .signature-box {
            text-align: center;
            width: 260px;
        }

        .sig-line {
            border-top: 1.5px solid #475569;
            margin-bottom: 6px;
        }

        .print-btn-bar {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background: #286043;
            color: #ffffff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        @media print {
            .print-btn-bar {
                display: none;
            }
            body {
                padding: 0;
            }
            .voucher-box {
                border-color: #000;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Guardar como PDF</button>
    </div>

    <div class="voucher-box">
        <div class="voucher-header">
            <div class="brand-block">
                <div>
                    <div class="brand-title">Unidad Apícola SENA</div>
                    <div class="brand-sub">Sistema de Inventario</div>
                </div>
            </div>

            <div class="center-block">
                <div class="doc-title">Factura de Movimiento</div>
                <div class="doc-number">N.° {{ $movimiento->numero_comprobante }}</div>
            </div>

            <div class="meta-block">
                <div>Tipo de movimiento: <strong>{{ $movimiento->tipo }}</strong></div>
                <div>Fecha: <strong>{{ $movimiento->fecha ? $movimiento->fecha->format('d/m/Y') : date('d/m/Y') }}</strong></div>
                <div>Bodega: <strong>{{ $movimiento->bodega }}</strong></div>
                <div>Usuario: <strong>{{ $movimiento->responsable ?: 'Karen Plazas' }}</strong></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Producto</th>
                    <th style="width: 90px; text-align: center;">Cantidad</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimiento->detalles as $idx => $d)
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $idx + 1 }}</td>
                        <td style="font-weight: bold;">{{ $d->producto_nombre }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $d->cantidad }}</td>
                        <td>{{ $d->observaciones ?? ($movimiento->tipo . ' de bodega') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer-row">
            <div class="total-box">
                <div style="font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase;">Total de productos</div>
                <div class="num">{{ $movimiento->total_productos }}</div>
            </div>

            <div class="signature-box">
                <div class="sig-line"></div>
                <div style="font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase;">Responsable</div>
                <div style="font-size: 13px; font-weight: bold; color: #111827;">{{ $movimiento->responsable }}</div>
                <div style="font-size: 11px; font-weight: bold; color: #4b5563;">SENA</div>
            </div>
        </div>
    </div>

</body>
</html>

