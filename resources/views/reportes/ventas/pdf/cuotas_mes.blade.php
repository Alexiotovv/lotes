<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuotas del Mes - {{$empresa->nombre}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
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
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }
        .report-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .summary {
            font-size: 14px;
            margin-bottom: 20px;
        }
        .summary div {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="a4-container">
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
        
        
        
        <div class="summary">
            <div class="report-title">CUOTAS DEL MES</div>
            {{-- 
            <div class="report-subtitle">
                Fecha de emisión: {{ now()->format('d/m/Y H:i') }}<br>
                Mes: {{ \DateTime::createFromFormat('!m', $mes)->format('F') }} de {{ $anio }}
            </div> --}}

            <div><strong>Total de Cuotas:</strong> {{ $cronogramas->count() }}</div>
            <div><strong>Monto Total Programado:</strong> S/ {{ number_format($cronogramas->sum('cuota'), 2) }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID Cuota</th>
                    <th>Cliente</th>
                    <th>DNI/RUC</th>
                    <th>Lote</th>
                    <th>N° Cuota</th>
                    <th>Fecha Pago</th>
                    <th>Cuota (S/)</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cronogramas as $crono)
                    <tr>
                        <td>{{ $crono->id }}</td>
                        <td>{{ $crono->venta->cliente->nombre_cliente ?? 'N/A' }}</td>
                        <td>{{ $crono->venta->cliente->dni_ruc ?? 'N/A' }}</td>
                        <td>{{ $crono->venta->lote->codigo ?? 'N/A' }} - {{ $crono->venta->lote->nombre ?? 'N/A' }}</td>
                        <td>{{ $crono->nro_cuota }}</td>
                        <td>{{ $crono->fecha_pago->format('d/m/Y') }}</td>
                        <td class="text-right">{{ number_format($crono->cuota, 2) }}</td>
                        <td>
                            @switch($crono->estado)
                                @case('pagado')
                                    <span class="badge badge-success">PAGADO</span>
                                    @break
                                @case('vencido')
                                    <span class="badge badge-danger">VENCIDO</span>
                                    @break
                                @case('pendiente')
                                    <span class="badge badge-warning">PENDIENTE</span>
                                    @break
                                @default
                                    <span class="badge" style="background-color: #e2e3e5; color: #383d41;">{{ strtoupper($crono->estado) }}</span>
                            @endswitch
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No se encontraron cuotas programadas para este mes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Reporte generado automáticamente por el sistema.<br>
            Impreso por: {{ auth()->user()->name ?? 'Sistema' }} | Fecha: {{ now()->format('d/m/Y H:i') }}
        </div>

       
    </div>
    <script>
        // Imprime automáticamente al abrir
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 700);
        });

        // Ocultar el botón al imprimir (antes de que se imprima)
        window.addEventListener('beforeprint', () => {
            document.querySelector('button[onclick="window.print()"]').style.display = 'none';
        });

        // Mostrar el botón después de cancelar la impresión (después de que se cierra el diálogo)
        window.addEventListener('afterprint', () => {
            document.querySelector('button[onclick="window.print()"]').style.display = 'block';
        });
    </script>
</body>
</html>