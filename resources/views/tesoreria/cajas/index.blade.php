@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>🏦 Cajas</h3>
    <a href="{{ route('tesoreria.cajas.create') }}" class="btn btn-primary btn-sm">➕ Nueva Caja</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Saldo Inicial</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cajas as $c)
                    <tr>
                        <td>{{ $c->nombre }}</td>
                        <td>
                            @if($c->tipo === 'efectivo')
                                <span class="badge bg-success">Efectivo</span>
                            @elseif($c->tipo === 'banco')
                                <span class="badge bg-primary">Banco</span>
                            @else
                                <span class="badge bg-info">Digital</span>
                            @endif
                        </td>
                        <td>S/ {{ number_format($c->saldo_inicial, 2) }}</td>
                        <td>
                            @if($c->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tesoreria.cajas.edit', $c) }}" class="btn btn-sm btn-outline-warning">✏️ Editar</a>
                            @if($c->activo)
                                <form action="{{ route('tesoreria.cajas.toggle', $c) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('¿Desactivar esta caja?')">
                                        🚫 Desactivar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('tesoreria.cajas.toggle', $c) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        ✅ Activar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay cajas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection