@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Listado de Ventas</h3>
    <a href="{{ route('ventas.create') }}" class="btn btn-light btn-sm">➕ Nueva Venta</a>
</div>

<!-- 🔍 Barra de búsqueda -->
<form method="GET" action="{{ route('ventas.index') }}" class="mb-4">
    <div class="input-group">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            placeholder="Buscar por: ID venta, nombre cliente, código/nombre lote o fecha (YYYY-MM-DD)"
            value="{{ request('search') }}"
        >
        <button class="btn btn-outline-secondary" type="submit">🔍 Buscar</button>
        @if(request('search'))
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-danger">✕ Limpiar</a>
        @endif
    </div>
</form>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Id</th>
            <th>DNI/RUC</th>
            <th>Cliente</th>
            <th>Cod.Lote</th>
            <th>Tipo de Venta</th>
            <th>Fecha Pago</th>
            <th>Inicial (S/)</th>
            <th>Interés (%)</th>
            <th>Cuota (S/)</th>
            <th>Total (S/)</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ventas as $v)
        <tr>
            <td>{{ $v->id }}</td>
            <td>{{ $v->cliente->dni_ruc }}</td>
            <td>{{ $v->cliente->nombre_cliente }}</td>
            <td>{{$v->lote->codigo}}</td>
            <td>{{ $v->metodopago->nombre }}</td>
            <td>{{ $v->fecha_pago }}</td>
            <td>{{ number_format($v->inicial, 2) }}</td>
            <td>{{ $v->tasa_interes * 100 }}</td>
            <td>S/ {{ number_format($v->cuota, 2) }}</td>
            <td>S/ {{ number_format($v->lote?->area_m2 * $v->lote?->precio_m2, 2, '.', ',') }}</td>
            <td>
                @switch($v->estado)
                    @case('finalizado')
                    @case('contado')
                        <span class="badge bg-secondary text-white">{{$v->estado}}</span>
                        @break
                    @case('vigente')
                        <span class="badge bg-success text-white">{{$v->estado}}</span>
                        @break
                    @default
                        <span class="badge bg-warning text-dark">{{$v->estado}}</span>
                @endswitch
            </td>
            <td>
                @if(!$v->cronograma_generado)
                    <a href="{{ route('ventas.edit', $v) }}" class="btn btn-outline-primary btn-sm">✏️ Editar</a>
                @endif

                @if($v->cronograma_generado)
                    <a href="{{ route('ventas.cronograma', $v) }}" target="_blank" class="btn btn-outline-info btn-sm">
                        🖨️ Cronograma
                    </a>
                @elseif($v->metodopago && $v->metodopago->es_credito)
                    {{-- ✅ Solo mostrar "Generar Cronograma" si es venta al crédito --}}
                    <form action="{{ route('ventas.generar-cronograma', $v) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de generar el cronograma? Esta acción no se puede deshacer.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            📊 Generar Cronograma
                        </button>
                    </form>
                @endif

                @if(Auth::user()->is_admin || Auth::user()->role === 'admin')
                    <form action="{{ route('ventas.destroy', $v->id) }}" method="POST" onsubmit="return confirm('¿Eliminar venta?')" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-light btn-sm">❌</button>
                    </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Enlaces de paginación -->
<div class="d-flex justify-content-end mt-3">
    {{ $ventas->links() }}
</div>
@endsection
