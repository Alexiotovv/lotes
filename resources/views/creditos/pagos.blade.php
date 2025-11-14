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
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .logo {
            width: 60px;
            height: 60px;
        }

        .empresa-info {
            text-align: center;
            flex-grow: 1;
        }

        .empresa-info h2 {
            font-size: 14px;
            margin: 0;
            font-weight: bold;
        }

        .empresa-info p {
            margin: 2px 0;
            font-size: 10px;
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
            margin-top: 15px;
            padding: 6px 12px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
        }

        button:hover {
            background-color: #0a58ca;
        }
        .a4-container {
            width: 95%;
            margin: auto;
            width: 210mm; /* Ancho A4 */
            min-height: 297mm; /* Alto A4 */
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Efecto de hoja real */
            padding: 15mm; /* Margen interno */
            box-sizing: border-box;
            margin-top: 10mm; /* Margen superior en pantalla */
            margin-bottom: 10mm; /* Margen inferior en pantalla */
        }
    </style>
</head>
<body>
<div class="a4-container">
    <div class="header">
        <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo" class="logo"> <!-- Reemplace con la ruta real de su logo -->
        <div class="empresa-info">
            <h2>{{ optional($empresa)->nombre ?? 'CONSTRUCCIONES E INMOBILIARIA ALARCON SAC' }}</h2>
            <p>R.U.C. {{ optional($empresa)->ruc ?? '20603441568' }}</p>
            <p>{{ optional($empresa)->direccion ?? 'PSJ. SIMÓN BOLÍVAR N° 159 - MORALES' }}</p>
            <p>{{ optional($empresa)->descripcion ?? 'LOTIZACIÓN LOS CEDROS DE SAN JUAN' }}</p>
        </div>
        <button onclick="window.print()">🖨️ Imprimir</button>
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