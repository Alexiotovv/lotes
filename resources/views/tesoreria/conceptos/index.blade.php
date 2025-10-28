@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Conceptos</h3>
    <a href="{{ route('tesoreria.conceptos.create') }}" class="btn btn-primary btn-sm">➕ Nuevo Concepto</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conceptos as $c)
                    <tr>
                        <td>{{ $c->nombre }}</td>
                        <td>
                            @if($c->tipo === 'ingreso')
                                <span class="badge bg-success">Ingreso</span>
                            @else
                                <span class="badge bg-danger">Egreso</span>
                            @endif
                        </td>
                        <td>{{ $c->categoria ?? '—' }}</td>
                        <td>
                            @if($c->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tesoreria.conceptos.edit', $c) }}" class="btn btn-sm btn-outline-warning">✏️ Editar</a>
                            @if($c->activo)
                                <form action="{{ route('tesoreria.conceptos.toggle', $c) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('¿Desactivar este concepto?')">
                                        🚫 Desactivar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('tesoreria.conceptos.toggle', $c) }}" method="POST" class="d-inline">
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
                        <td colspan="5" class="text-center text-muted">No hay conceptos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection