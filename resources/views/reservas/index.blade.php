@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Reservas de Lotes</h3>
    <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-sm">➕ Nueva Reserva</a>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Lote</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Caja</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reservas as $r)
        <tr>
            <td>{{ $r->id }}</td>
            <td>{{ $r->cliente->nombre_cliente }}</td>
            <td>{{ $r->lote->codigo }} - {{ $r->lote->nombre }}</td>
            <td>S/ {{ number_format($r->monto, 2) }}</td>
            <td>{{ $r->fecha_reserva }}</td>
            <td>{{ $r->caja->nombre }}</td>
            <td>
                <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="{{ $r->id }}">
                    ✏️ Editar
                </button>
                <form action="{{ route('reservas.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar reserva?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $reservas->links() }}

<!-- Modal de Edición -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="reserva_id">
                    <div class="mb-3">
                        <label>Cliente</label>
                        <select id="edit_cliente_id" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label>Monto</label>
                        <input type="number" step="0.01" id="edit_monto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" id="edit_fecha_reserva" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Caja</label>
                        <select id="edit_caja_id" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label>Observaciones</label>
                        <textarea id="edit_observaciones" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.edit-btn').on('click', function() {
        const id = $(this).data('id');
        $.get(`/reservas/${id}/edit`, function(data) {
            const r = data.reserva;
            $('#reserva_id').val(r.id);
            $('#edit_cliente_id').empty();
            $('#edit_caja_id').empty();

            data.clientes.forEach(c => {
                $('#edit_cliente_id').append(`<option value="${c.id}" ${c.id == r.cliente_id ? 'selected' : ''}>${c.nombre_cliente}</option>`);
            });
            data.cajas.forEach(c => {
                $('#edit_caja_id').append(`<option value="${c.id}" ${c.id == r.caja_id ? 'selected' : ''}>${c.nombre}</option>`);
            });

            $('#edit_monto').val(r.monto);
            $('#edit_fecha_reserva').val(r.fecha_reserva);
            $('#edit_observaciones').val(r.observaciones || '');

            $('#editForm').attr('action', `/reservas/${r.id}`);
            $('#editModal').modal('show');
        });
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const url = $(this).attr('action');
        const datos = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT',
            cliente_id: $('#edit_cliente_id').val(),
            caja_id: $('#edit_caja_id').val(),
            monto: $('#edit_monto').val(),
            fecha_reserva: $('#edit_fecha_reserva').val(),
            observaciones: $('#edit_observaciones').val(),
        };

        $.post(url, datos, function() {
            location.reload();
        }).fail(function() {
            alert('Error al actualizar.');
        });
    });
});
</script>
@endsection