@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📋 Tasas de Interés</h3>
    <a href="{{ route('tasas.create') }}" class="btn btn-primary btn-sm">➕ Nueva Tasa</a>
</div>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Monto (%)</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tasas as $tasa)
        <tr>
            <td>{{ $tasa->id }}</td>
            <td>{{ $tasa->nombre }}</td>
            <td><span class="badge bg-info">{{ number_format($tasa->monto * 100, 2) }}%</span></td>
            <td>
                <a href="{{ route('tasas.edit', $tasa) }}" class="btn btn-sm btn-warning">✏️</a>
                <form action="{{ route('tasas.destroy', $tasa) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar tasa?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">No hay tasas registradas.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $tasas->links() }}
@endsection