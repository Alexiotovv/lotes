@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Reservas de Lotes</h3>
    <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-sm">➕ Nueva Reserva</a>
</div>
<!-- 🔍 Barra de búsqueda -->
<form method="GET" action="{{ route('reservas.index') }}" class="mb-4">
    <div class="row g-3">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text">🔍</span>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control form-control-lg" 
                    placeholder="Buscar por: Cliente, DNI, Lote (código/nombre), Fecha (AAAA-MM-DD) o ID"
                    value="{{ request('search') }}"
                >
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-outline-secondary w-100" type="submit">🔎 Buscar</button>
            @if(request('search'))
                <a href="{{ route('reservas.index') }}" class="btn btn-outline-danger w-100 ms-2">✕ Limpiar</a>
            @endif
        </div>
    </div>
</form>
@if(request('search'))
    <div class="alert alert-info mb-4">
        <strong>Búsqueda:</strong> "{{ request('search') }}"
        <a href="{{ route('reservas.index') }}" class="float-end text-decoration-none">✖️ Quitar filtro</a>
    </div>
@endif
<div class="table-responsive">
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

                    @if(auth()->user()->is_admin())
                        <a href="{{ route('reservas.edit', $r->id) }}" class="btn btn-warning btn-sm">
                            ✏️ Editar
                        </a>
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $r->id }}">
                            🗑️
                        </button>
                    @else
                        --
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $reservas->links() }}

<!-- Modal de Edición -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="" method="POST" enctype="multipart/form-data">
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


<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>


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

    // ✅ Manejar eliminación
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const url = `/reservas/${id}`;

        if (confirm('¿Eliminar reserva? Esta acción no se puede deshacer.')) {
            const form = $('#deleteForm');
            form.attr('action', url);
            form.submit();
        }
    });
</script>
@endsection