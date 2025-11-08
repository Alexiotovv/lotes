@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Listado de Cotizaciones</h3>
    <a href="{{ route('cotizaciones.create') }}" class="btn btn-light btn-sm">➕ Nueva Cotización</a>
</div>
<!-- 🔍 Barra de búsqueda -->
<form method="GET" action="{{ route('cotizaciones.index') }}" class="mb-4">
    <div class="input-group">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            placeholder="Buscar por: ID cotización, nombre cliente, código/nombre lote o fecha (YYYY-MM-DD)"
            value="{{ request('search') }}"
        >
        <button class="btn btn-outline-secondary" type="submit">🔍 Buscar</button>
        @if(request('search'))
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-danger">✕ Limpiar</a>
        @endif
    </div>
</form>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Id</th>
                <th>Dni/Ruc</th>
                <th>Cliente</th>
                <th>Método Pago</th>
                <th>Fecha Pago</th>
                <th>Inicial</th>
                <th>Interés</th>
                <th>Cuota</th>
                <th>Total (S/)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizaciones as $c)
            <tr>
                <td>{{$c->id}}</td>
                <td>{{ $c->cliente->dni_ruc }}</td>
                <td>{{ $c->cliente->nombre_cliente }}</td>
                <td>{{ $c->metodopago->nombre }}</td>
                <td>{{ $c->fecha_pago }}</td>
                <td>{{ $c->inicial }}</td>
                <td>{{ $c->tasa_interes * 100 }}</td>
                <td>S/ {{ number_format($c->cuota, 2) }}</td>
                <td>S/ {{ number_format($c->lote?->area_m2 * $c->lote?->precio_m2, 2, '.', ',') }}</td>
                <td>
                    <a href="{{ route('cotizaciones.cronograma', $c) }}" target="_blank" class="btn btn-outline-info btn-sm ms-1">🖨️ Cronograma</a>
                    <form action="{{ route('cotizaciones.destroy', $c->id) }}" method="POST" onsubmit="return confirm('¿Eliminar cotización?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-light btn-sm">❌</button>
                    </form>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Enlaces de paginación -->
<div class="d-flex justify-content-end mt-3">
    {{ $cotizaciones->links() }}
</div>
@endsection
