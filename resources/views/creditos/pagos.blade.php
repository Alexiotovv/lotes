<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos Realizados</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            margin: auto;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .datos {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .datos p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #555;
            font-size: 11.5px;
        }

        th, td {
            border: 1px solid #555;
            padding: 4px;
        }

        th {
            background-color: #f0f0f0;
        }

        td {
            text-align: right;
        }

        td.left {
            text-align: left;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 11px;
        }

        @media print {
            button { display: none; }
        }

        button {
            margin-top: 20px;
            padding: 8px 14px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0a58ca;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ optional($empresa)->nombre ?? 'EMPRESA' }}</h2>
        <h4>REPORTE DE PAGOS REALIZADOS POR EL CLIENTE</h4>
    </div>

    <div class="datos">
        <p><strong>Cliente:</strong> {{ $venta->cliente->nombre_cliente }}</p>
        <p><strong>DNI:</strong> {{ $venta->cliente->dni_ruc }}</p>
        <p><strong>Lote:</strong> {{ $venta->lote->codigo }} - {{ $venta->lote->nombre }} ({{ $venta->lote->area_m2 }} m²)</p>
        <p><strong>Total Crédito:</strong> S/ {{ number_format($venta->monto_financiar, 2) }} &nbsp;&nbsp;
           <strong>Inicial:</strong> S/ {{ number_format($venta->inicial, 2) }} &nbsp;&nbsp;
           <strong>Total Venta:</strong> S/ {{ number_format($venta->lote->area_m2 * $venta->lote->precio_m2, 2) }}</p>
        <p><strong>N° Cuotas:</strong> {{ $venta->numero_cuotas }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th class="left">FECHA PAGO</th>
                <th class="left">TIPO CUOTA</th>
                <th>AMORTIZACIÓN</th>
                <th>MONTO RESTANTE</th>
            </tr>
        </thead>
        <tbody>
            <!-- Cuota inicial -->
            <tr>
                <td>-</td>
                <td class="left">{{ $venta->created_at->format('d/m/Y') }}</td>
                <td class="left">INICIAL</td>
                <td>S/ {{ number_format($venta->inicial, 2) }}</td>
                <td>S/ {{ number_format($venta->monto_financiar, 2) }}</td>
            </tr>

            <!-- Cuotas mensuales -->
            @php
                $saldo = $venta->monto_financiar;
            @endphp
            @foreach ($pagos as $cronograma)
                @php
                    $pagoTotal = $cronograma->pagos->sum('monto_pagado');
                    $saldo -= $pagoTotal;
                @endphp
                <tr>
                    <td>{{ $cronograma->nro_cuota }}</td>
                    <td class="left">{{ $cronograma->fecha_pago }}</td>
                    <td class="left">CUOTA</td>
                    <td>S/ {{ number_format($pagoTotal, 2) }}</td>
                    <td>S/ {{ number_format(max(0, $saldo), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">TOTAL AMORTIZADO</th>
                <th>S/ {{ number_format($venta->monto_financiar - $saldo, 2) }}</th>
                <th>--</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p><strong>Nota:</strong> Reporte generado por el sistema de ventas de terrenos.</p>
        <button onclick="window.print()">🖨️ Imprimir</button>
    </div>
</div>

<script>
    window.addEventListener('load', () => {
        setTimeout(() => window.print(), 700);
    });
</script>
</body>
</html>