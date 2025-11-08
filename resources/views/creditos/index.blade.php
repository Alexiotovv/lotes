@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Lista de Créditos</h3>
</div>

<!-- Barra de búsqueda -->
<form method="GET" action="{{ route('creditos.index') }}" class="mb-4">
    <div class="input-group">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            placeholder="Buscar por: ID, cliente, lote o fecha"
            value="{{ request('search') }}"
        >
        <button class="btn btn-outline-secondary" type="submit">🔍 Buscar</button>
        @if(request('search'))
            <a href="{{ route('creditos.index') }}" class="btn btn-outline-danger">✕ Limpiar</a>
        @endif
    </div>
</form>
@if(request('search'))
    <div class="alert alert-info mb-4">
        <strong>Búsqueda:</strong> "{{ request('search') }}"
        <a href="{{ route('creditos.index') }}" class="float-end text-decoration-none">✖️ Quitar filtro</a>
    </div>
@endif
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Fecha Venta</th>
            <th>Próximo Pago</th>
            <th>Cuota Mes</th>
            <th>Total Deuda</th>
            <th>Total Venta</th>
            {{-- <th>Calendario</th> --}}
            <th>Pagos</th>
        </tr>
    </thead>
    <tbody>
        @foreach($creditos as $credito)
        <tr>
            <td>{{ $credito->id }}</td>
            <td>{{ $credito->cliente->nombre_cliente }}</td>
            <td>{{ $credito->created_at->format('d/m/Y') }}</td>
            <td>
                @if($credito->proxima_cuota_fecha)
                    @if($credito->estado_proxima == 'vencido')
                        <span class="badge bg-danger">{{ $credito->proxima_cuota_fecha }}</span>
                    @else
                        <span class="badge bg-success">{{ $credito->proxima_cuota_fecha }}</span>
                    @endif
                @else
                    <span class="badge bg-secondary">FINALIZADO</span>
                @endif
            </td>
            <td><span class="badge bg-info">{{ number_format($credito->cuota, 2) }}</span></td>
            <td><span class="badge bg-warning">{{ number_format($credito->total_deuda, 2) }}</span></td>
            <td><span class="badge bg-dark">{{ number_format($credito->lote_precio_total, 2) }}</span></td>
            {{-- <td>
                <a href="{{ route('creditos.calendario', $credito) }}" target="_blank" class="btn btn-sm btn-primary">
                    📅 Calendario
                </a>
            </td> --}}
            <td>
                <a href="{{ route('creditos.pagos', $credito) }}" target="_blank" class="btn btn-sm btn-success">
                    💰 Realizados
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $creditos->links() }}
@endsection

@section('styles')
<style>
    .badge.bg-purple {
        background-color: #6f42c1 !important;
        color: white;
    }
</style>
@endsection