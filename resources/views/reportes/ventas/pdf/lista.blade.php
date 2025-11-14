<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Ventas</title>
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

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo {
            width: 80px;
            height: auto;
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


        h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }

        .subtitulo {
            margin: 5px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .filtro {
            margin: 10px 0;
            font-size: 12px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 11.5px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        td {
            text-align: left;
        }

        td.right {
            text-align: right;
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
        <!-- Encabezado con logo y datos de la empresa -->
        <div class="header">
            <img src="{{ asset('storage/' . ($empresa->logo ?? 'images/logo.png')) }}" alt="Logo" class="logo">
            <div class="empresa-info">
                <h2>{{ optional($empresa)->nombre ?? 'CONSTRUCCIONES E INMOBILIARIA ALARCON SAC' }}</h2>
                <p>R.U.C. {{ optional($empresa)->ruc ?? '20603441568' }}</p>
                <p>{{ optional($empresa)->direccion ?? 'PSJ. SIMÓN BOLÍVAR N° 159 - MORALES' }}</p>
                <p>{{ optional($empresa)->descripcion ?? 'LOTIZACIÓN LOS CEDROS DE SAN JUAN' }}</p>
            </div>
            <button onclick="window.print()">🖨️ Imprimir</button>
        </div>

        <!-- Título del reporte -->
        <div class="subtitulo">
            REPORTE DE VENTAS - DESDE {{ $fechaDesde }} HASTA {{ $fechaHasta }}
        </div>

        <!-- Tabla de ventas -->
        <table>
            <thead>
                <tr>
                    <th>Nº</th>
                    <th>CLIENTE</th>
                    <th>FECHA</th>
                    <th>CÓDIGO LOTE</th>
                    <th>MÉTODO</th>
                    <th>MONTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $index => $v)
                <tr>
                    <td class="right">{{ $index + 1 }}</td>
                    <td>{{ $v->cliente->nombre_cliente }}</td>
                    <td>{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $v->lote->codigo }}</td>
                    <td>{{ $v->metodopago->nombre }}</td>
                    <td class="right">S/ {{ number_format($v->inicial + $v->monto_financiar, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" style="text-align:right;">TOTAL</th>
                    <th class="right">S/ {{ number_format($ventas->sum(function($v) { return $v->inicial + $v->monto_financiar; }), 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            
        </div>
    </div>

    <script>
        // Imprime automáticamente al abrir
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 700);
        });
    </script>
</body>
</html>