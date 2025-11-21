@extends('layouts.plain') {{-- Opcional: layout sin menú --}}
@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cronograma de Pagos</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
            min-height: 100vh;
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

        .datos-cliente {
            margin-top: 10px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .datos-cliente div {
            display: flex;
            justify-content: space-between;
        }

        .datos-cliente strong {
            font-weight: bold;
            margin-right: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 10.5px;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px 5px;
            text-align: right;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 4px;
        }

        td.left {
            text-align: left;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }

        @media print {
            button { display: none; }
            
            body {
                background-color: white; /* Quitar fondo gris al imprimir */
                /* display: block; Cambiar a bloque */
                align-items: normal; /* Quitar centrado vertical */
                
            }
            .a4-container {
                box-shadow: none; /* Quitar sombra */
                margin: 0; /* Quitar márgenes de pantalla */
                padding-top: 5mm; /* Asegurar márgenes internos */
                padding-bottom: 5mm;
                padding-left: 5mm;
                padding-right: 5mm;
            }
            @page {
                margin: 5mm 10mm; /* Margen superior/inferior: 20mm, laterales: 15mm */
                size: A4;
            }
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

        /* Estilos específicos para emular la imagen */
        .encabezado-tabla {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            border: 1px solid #000;
        }

        .tabla-cronograma th {
            background-color: #ffffff;
            color: #000;
            font-weight: bold;
            text-align: center;
            padding: 4px;
            border: 1px solid #000;
        }

        .tabla-cronograma td {
            border: 1px solid #000;
            padding: 3px 5px;
            text-align: right;
        }

        .tabla-cronograma td.nro {
            text-align: center;
            width: 40px;
        }

        .tabla-cronograma td.fecha {
            text-align: left;
            width: 100px;
        }

        .tabla-cronograma td.saldo,
        .tabla-cronograma td.interes,
        .tabla-cronograma td.amortizacion,
        .tabla-cronograma td.cuota {
            width: 80px;
        }

        .tabla-cronograma td.texto {
            text-align: left;
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
    <!-- Encabezado con logo y datos de la empresa -->
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
    <div class="row" style="text-align: center;">
        <h3>Cronograma de Pago</h3>
    </div>
    <!-- Datos del cliente y lote -->
    <div class="datos-cliente">
        <div><strong>Cliente:</strong> {{ $venta->cliente->nombre_cliente }}</div>
        <div><strong>DNI:</strong> {{ $venta->cliente->dni_ruc }}</div>
        <div><strong>Lote:</strong> {{ $venta->lote->nombre }} ({{ $venta->lote->area_m2 }} m²)</div>
        <div><strong>Precio:</strong> S/ {{ number_format($venta->lote->area_m2 * $venta->lote->precio_m2, 2) }}</div>
        <div><strong>Inicial:</strong> S/ {{ number_format($venta->inicial, 2) }}</div>
        <div><strong>Saldo:</strong> S/ {{ number_format($venta->monto_financiar, 2) }}</div>
        <div><strong>N° Cuotas:</strong> {{ $venta->numero_cuotas }}</div>
        <div><strong>TEA:</strong> {{ number_format($venta->tasa_interes * 100, 2) }}%</div>
        <div><strong>TEM:</strong> {{ number_format(($venta->tasa_interes > 0 ? pow(1 + $venta->tasa_interes, 1/12) - 1 : 0) * 100, 2) }}%</div>
    </div>

    <!-- Tabla de cronograma -->
    <table class="tabla-cronograma">
        <thead>
            <tr>
                <th class="nro">N°</th>
                <th class="fecha">FECHA PAGO</th>
                <th class="saldo">SALDO</th>
                <th class="interes">INTERÉS</th>
                <th class="amortizacion">AMORTIZACIÓN</th>
                <th class="cuota">CUOTA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
            <tr>
                <td class="nro">{{ $r['nro_cuota'] }}</td>
                <td class="fecha">{{ date('Y-m-d', strtotime($r['fecha_pago'])) }}</td>
                <td class="saldo">{{ $r['saldo'] }}</td>
                <td class="interes">{{ $r['interes'] }}</td>
                <td class="amortizacion">{{ $r['amortizacion'] }}</td>
                <td class="cuota">{{ $r['cuota'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <!-- Resumen final -->
<div style="margin-top: 15px;">
    <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; border: 1px solid #000;">
        <tr>
            <td colspan="6" style="text-align: center; font-weight: bold; background-color: #f0f0f0; padding: 4px; border: 1px solid #000;">TOTAL</td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 3px 5px; border: 1px solid #000;">{{ $totalInteresFormateado }}</td>
            <td style="text-align: right; padding: 3px 5px; border: 1px solid #000;">{{ $totalAmortizacionFormateado }}</td>
            <td style="text-align: right; padding: 3px 5px; border: 1px solid #000;">{{ $totalAPagarFormateado }}</td>
        </tr>
    </table>

    <!-- Resumen detallado -->
    <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; margin-top: 10px; border: 1px solid #000;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 4px; border: 1px solid #000; background-color: #ffffff;">INICIAL</th>
                <th style="text-align: left; padding: 4px; border: 1px solid #000; background-color: #ffffff;">SALDO</th>
                <th style="text-align: left; padding: 4px; border: 1px solid #000; background-color: #ffffff;">INTERESES</th>
                <th style="text-align: left; padding: 4px; border: 1px solid #000; background-color: #ffffff;">TOTAL A PAGAR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: right; padding: 3px 5px; border: 1px solid #000;">S/ {{ number_format($venta->inicial, 2) }}</td>
                <td style="text-align: right; padding: 3px 5px; border: 1px solid #000;">S/ {{ number_format($venta->monto_financiar, 2) }}</td>
                <td style="text-align: right; padding: 3px 5px; border: 1px solid #000;">S/ {{ $totalInteresFormateado }}</td>
                <td style="text-align: right; padding: 3px 5px; border: 1px solid #000;">S/ {{ $totalAPagarFormateado }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Nota de validez -->
    <div style="margin-top: 5px; font-size: 9px; font-style: italic;">
        {{ \Carbon\Carbon::parse($venta->fecha_pago)->format('d/m/Y') }}: Cotización válida por 30 días.
    </div>
</div>
    <!-- Pie de página -->
    <div class="footer">
        <p><strong>Nota:</strong> Las fechas y montos son referenciales según la tasa indicada.</p>
        
    </div>
</div>

<script>
    window.addEventListener('load', () => {
        setTimeout(() => window.print(), 700);
    });
</script>
</body>
</html>
@endsection