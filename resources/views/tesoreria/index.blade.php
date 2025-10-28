@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>💼 Tesorería</h3>
    <a href="{{ route('tesoreria.create') }}" class="btn btn-primary btn-sm">➕ Nuevo Movimiento</a>
</div>

<!-- Filtros -->
<form method="GET" class="mb-4">
    <div class="row g-2">
        <div class="col-md-2">
            <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}" placeholder="Desde">
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}" placeholder="Hasta">
        </div>
        <div class="col-md-2">
            <select name="caja_id" class="form-select">
                <option value="">Todas las cajas</option>
                @foreach($cajas as $caja)
                    <option value="{{ $caja->id }}" {{ request('caja_id') == $caja->id ? 'selected' : '' }}>
                        {{ $caja->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="ingreso" {{ request('tipo') == 'ingreso' ? 'selected' : '' }}>Ingresos</option>
                <option value="egreso" {{ request('tipo') == 'egreso' ? 'selected' : '' }}>Egresos</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-secondary btn-sm">🔍 Filtrar</button>
            @if(request()->query())
                <a href="{{ route('tesoreria.index') }}" class="btn btn-outline-danger btn-sm">✕ Limpiar</a>
            @endif
        </div>
    </div>
</form>

<!-- Tabla de movimientos -->
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Fecha</th>
                <th>Caja</th>
                <th>Concepto</th>
                <th>Cliente/Venta</th>
                <th>Referencia</th>
                <th>Monto</th>
                <th>Comprobante</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $m)
            <tr>
                <td>{{ $m->fecha->format('d/m/Y') }}</td>
                <td>{{ $m->caja->nombre }}</td>
                <td>
                    <span class="badge bg-{{ $m->tipo == 'ingreso' ? 'success' : 'danger' }}">
                        {{ $m->concepto->nombre }}
                    </span>
                </td>
                <td>
                    @if($m->venta)
                        {{ $m->venta->cliente->nombre_cliente }}<br>
                        <small>Venta #{{ $m->venta->id }} - Lote: {{$m->venta->lote->codigo}}</small>
                    @else
                        —
                    @endif
                </td>
                <td>{{ $m->referencia ?? '—' }}</td>
                <td class="{{ $m->tipo == 'ingreso' ? 'text-success' : 'text-danger' }}">
                    {{ $m->tipo == 'ingreso' ? '+' : '-' }} S/ {{ number_format($m->monto, 2) }}
                </td>
                <td>
                    @if($m->comprobante)
                        <a href="{{ asset('storage/' . $m->comprobante) }}" target="_blank" class="btn btn-sm btn-outline-primary">🖼️</a>
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">No hay movimientos registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $movimientos->links() }}
@endsection