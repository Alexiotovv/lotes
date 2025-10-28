<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cronograma de Pagos</title>
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
        <h4>CRONOGRAMA DE PAGOS</h4>
    </div>

    <div class="datos">
        <p><strong>Cliente:</strong> {{ $venta->cliente->nombre_cliente }}</p>
        <p><strong>DNI:</strong> {{ $venta->cliente->dni_ruc }}</p>
        <p><strong>Lote:</strong> {{ $venta->lote->codigo }} - {{ $venta->lote->nombre }} ({{ $venta->lote->area_m2 }} m²)</p>
        <p><strong>Precio total:</strong> S/ {{ number_format($venta->lote->area_m2 * $venta->lote->precio_m2, 2) }}</p>
        <p><strong>Inicial:</strong> S/ {{ number_format($venta->inicial, 2) }} &nbsp;&nbsp;
           <strong>Saldo:</strong> S/ {{ number_format($venta->monto_financiar, 2) }}</p>
        <p><strong>N° Cuotas:</strong> {{ $venta->numero_cuotas }} &nbsp;&nbsp;
           <strong>Tasa Anual:</strong> {{ number_format($venta->tasa_interes * 100, 2) }}%</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th class="left">Fecha Pago</th>
                <th>Saldo</th>
                <th>Interés</th>
                <th>Amortización</th>
                <th>Cuota</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cronogramas as $c)
            <tr>
                <td style="text-align:center">{{ $c->nro_cuota }}</td>
                <td class="left">{{ $c->fecha_pago }}</td>
                <td>{{ number_format($c->saldo, 2) }}</td>
                <td>{{ number_format($c->interes, 2) }}</td>
                <td>{{ number_format($c->amortizacion, 2) }}</td>
                <td>{{ number_format($c->cuota, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Nota:</strong> Las fechas y montos son referenciales según la tasa indicada.</p>
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