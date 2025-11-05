@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Registro de Compras</h3>
    <a href="{{ route('compras.create') }}" class="btn btn-primary btn-sm">➕ Nueva Compra</a>
</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Concepto</th>
            <th>Caja</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($compras as $m)
        <tr>
            <td>{{ $m->id }}</td>
            <td>{{ $m->concepto->nombre }}</td>
            <td>{{ $m->caja->nombre }}</td>
            <td class="text-success">S/ {{ number_format($m->monto, 2) }}</td>
            <td>{{ $m->fecha->format('d/m/Y') }}</td>
            <td>{{ $m->user->name }}</td>
            <td>
                @if($m->comprobante)
                    <a href="{{ asset('storage/' . $m->comprobante) }}" target="_blank" class="btn btn-sm btn-outline-info">📄 Voucher</a>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">No hay compras registradas.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $compras->links() }}
@endsection