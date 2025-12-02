@extends('layouts.plain')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cronograma de Pagos Agrupado</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Arial', sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px; }
        .logo { width: 60px; height: 60px; }
        .empresa-info { text-align: center; flex-grow: 1; }
        .empresa-info h2 { font-size: 14px; margin: 0; font-weight: bold; }
        .empresa-info p { margin: 2px 0; font-size: 10px; }
        .datos-cliente { margin: 10px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .datos-cliente div { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10.5px; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: right; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; padding: 4px; }
        @media print { button { display: none; } }
        button { margin-top: 15px; padding: 6px 12px; background-color: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; }
        .a4-container { width: 95%; margin: auto; width: 210mm; min-height: 297mm; background-color: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 15mm; box-sizing: border-box; margin-top: 10mm; margin-bottom: 10mm; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
<div class="a4-container">
    <div class="header">
        <img src="{{ asset('storage/' . optional($empresa)->logo) }}" alt="Logo" class="logo">
        <div class="empresa-info">
            <h2>{{ optional($empresa)->nombre ?? 'CONSTRUCCIONES E INMOBILIARIA ALARCON SAC' }}</h2>
            <p>R.U.C. {{ optional($empresa)->ruc ?? '20603441568' }}</p>
            <p>{{ optional($empresa)->direccion ?? 'PSJ. SIMÓN BOLÍVAR N° 159 - MORALES' }}</p>
            <p>{{ optional($empresa)->descripcion ?? 'LOTIZACIÓN LOS CEDROS DE SAN JUAN' }}</p>
        </div>
        <button onclick="window.print()">🖨️ Imprimir</button>
    </div>
    
    <div style="text-align: center;">
        <h3>Cronograma de Pago Agrupado</h3>
        <small>ID Grupo: {{ $grupoId }}</small>
    </div>
    
    <!-- Datos del cliente -->
    <div class="datos-cliente">
        <div><strong>Cliente:</strong> {{ $cliente->nombre_cliente }}</div>
        <div><strong>DNI/RUC:</strong> {{ $cliente->dni_ruc }}</div>
        <div><strong>N° Ventas:</strong> {{ $ventas->count() }}</div>
        <div><strong>Total Lotes:</strong> {{ $ventas->count() }}</div>
    </div>
    
    <!-- Detalle de lotes -->
    <div style="margin: 15px 0;">
        <h5>Lotes incluidos:</h5>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Lote</th>
                    <th>Área (m²)</th>
                    <th>Precio Total</th>
                    <th>Inicial</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $venta)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $venta->lote->codigo ?? 'N/A' }} - {{ $venta->lote->nombre ?? 'N/A' }}</td>
                    <td>{{ $venta->lote->area_m2 ?? '0.00' }}</td>
                    <td>S/ {{ number_format(($venta->lote->area_m2 ?? 0) * ($venta->lote->precio_m2 ?? 0), 2) }}</td>
                    <td>S/ {{ number_format($venta->inicial ?? 0, 2) }}</td>
                    <td>S/ {{ number_format($venta->monto_financiar ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold;">
                    <td colspan="3">TOTAL</td>
                    <td>S/ {{ number_format($ventas->sum(function($v) { 
                        return ($v->lote->area_m2 ?? 0) * ($v->lote->precio_m2 ?? 0); 
                    }), 2) }}</td>
                    <td>S/ {{ number_format($totalInicial, 2) }}</td>
                    <td>S/ {{ number_format($totalFinanciar, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Cronograma de pagos AGRUPADO -->
    <table>
        <thead>
            <tr>
                <th>N° Cuota</th>
                <th>Fecha Pago</th>
                <th>Saldo Total</th>
                <th>Interés Total</th>
                <th>Amortización Total</th>
                <th>Cuota Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cronogramasAgrupados as $crono)
            <tr>
                <td class="text-center">{{ $crono['nro_cuota'] }}</td>
                <td>{{ date('Y-m-d', strtotime($crono['fecha_pago'])) }}</td>
                <td>S/ {{ number_format($crono['saldo_total'], 2) }}</td>
                <td>S/ {{ number_format($crono['interes_total'], 2) }}</td>
                <td>S/ {{ number_format($crono['amortizacion_total'], 2) }}</td>
                <td>S/ {{ number_format($crono['cuota_total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Resumen -->
    <div style="margin-top: 20px;">
        <table style="width: 100%;">
            <tr>
                <th colspan="2">RESUMEN DE PAGOS</th>
            </tr>
            <tr>
                <td>Total Inicial:</td>
                <td class="text-end">S/ {{ number_format($totalInicial, 2) }}</td>
            </tr>
            <tr>
                <td>Total a Financiar:</td>
                <td class="text-end">S/ {{ number_format($totalFinanciar, 2) }}</td>
            </tr>
            <tr>
                <td>Total Intereses:</td>
                <td class="text-end">S/ {{ number_format($totalInteres, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td>TOTAL A PAGAR:</td>
                <td class="text-end">S/ {{ number_format($totalPagar, 2) }}</td>
            </tr>
        </table>
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