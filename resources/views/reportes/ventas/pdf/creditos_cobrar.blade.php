<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créditos por Cobrar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-info {
            font-size: 14px;
            margin-bottom: 10px;
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
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <strong>{{ $empresa->nombre }}</strong><br>
            RUC: {{ $empresa->ruc ?? 'N/A' }}<br>
            Dirección: {{ $empresa->direccion ?? 'N/A' }}
        </div>
        <div class="report-title">LISTA DE CRÉDITOS POR COBRAR</div>
        <div class="report-subtitle">
            Fecha de emisión: {{ now()->format('d/m/Y H:i') }}<br>
            @if($fecha_desde && $fecha_hasta)
                Rango de fechas: {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}
            @endif
        </div>
    </div>

    <div class="summary">
        <div><strong>Total de Créditos:</strong> {{ $total_creditos }}</div>
        <div><strong>Monto Total por Cobrar:</strong> S/ {{ number_format($monto_total, 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>DNI/RUC</th>
                <th>Lote</th>
                <th>Fecha Venta</th>
                <th>Cuota Mensual (S/)</th>
                <th>Total Deuda (S/)</th>
                <th>Próximo Pago</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
                <tr>
                    <td>{{ $venta->id }}</td>
                    <td>{{ $venta->cliente->nombre_cliente }}</td>
                    <td>{{ $venta->cliente->dni_ruc }}</td>
                    <td>{{ $venta->lote->codigo }} - {{ $venta->lote->nombre }}</td>
                    <td>{{ $venta->created_at->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($venta->cuota, 2) }}</td>
                    <td class="text-right">{{ number_format($venta->monto_financiar - $venta->cronogramas->where('estado', 'pagado')->sum('cuota'), 2) }}</td>
                    <td>
                        @if($venta->cronogramas->where('estado', 'pendiente')->first())
                            <span class="badge {{ $venta->cronogramas->where('estado', 'pendiente')->first()->fecha_pago < today() ? 'bg-danger' : 'bg-success' }}">
                                {{ $venta->cronogramas->where('estado', 'pendiente')->first()->fecha_pago }}
                            </span>
                        @else
                            <span class="badge bg-secondary">FINALIZADO</span>
                        @endif
                    </td>
                    <td>
                        @switch($venta->estado)
                            @case('finalizado')
                            @case('contado')
                                <span class="badge bg-secondary text-white">{{ $venta->estado }}</span>
                                @break
                            @case('vigente')
                                <span class="badge bg-success text-white">{{ $venta->estado }}</span>
                                @break
                            @default
                                <span class="badge bg-warning text-dark">{{ $venta->estado }}</span>
                        @endswitch
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No se encontraron créditos por cobrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Reporte generado automáticamente por el sistema.<br>
        Impreso por: {{ auth()->user()->name ?? 'Sistema' }} | Fecha: {{ now()->format('d/m/Y H:i') }}
    </div>

        <!-- Botón para imprimir (no se imprime) -->
    <button 
        onclick="window.print()" 
        style="
            position: fixed; 
            top: 20px; 
            right: 20px; 
            z-index: 1000; 
            display: block;
            padding: 0.5rem 1rem;
            font-size: 0.875rem; /* Tamaño similar a btn-sm */
            font-weight: 500;
            color: #212529; /* Color de texto oscuro */
            background-color: rgba(248, 249, 250, 0.9); /* Fondo claro casi blanco con leve transparencia */
            border: 1px solid rgba(0, 0, 0, 0.1); /* Borde muy sutil */
            border-radius: 0.375rem; /* Bordes ligeramente redondeados */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra suave */
            cursor: pointer;
            transition: all 0.2s ease-in-out; /* Transición suave para hover */
            backdrop-filter: blur(4px); /* Efecto de vidrio (opcional, moderno) */
        "
        onPrint="this.style.display='none'"
    >
        🖨️ Imprimir
    </button>

    <script>
        // Imprime automáticamente al abrir
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 700);
        });

        // Ocultar el botón al imprimir (antes de que se imprima)
        window.addEventListener('beforeprint', () => {
            document.querySelector('button[onclick=\"window.print()\"]').style.display = 'none';
        });

        // Mostrar el botón después de cancelar la impresión (después de que se cierra el diálogo)
        window.addEventListener('afterprint', () => {
            document.querySelector('button[onclick=\"window.print()\"]').style.display = 'block';
        });
    </script>
</body>
</html>

</body>
 <script>
        // Imprime automáticamente al abrir
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 700);
        });
    </script>
</html>


