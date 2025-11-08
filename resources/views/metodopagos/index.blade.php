@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Listado Tipos de Venta</h3>
    <a href="{{ route('metodopagos.create') }}" class="btn btn-light btn-sm">➕ Nuevo Método</a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped" id="metodopagosTable">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Tipo de Venta</th>
                <th>Activo</th>
                <th width="140">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($metodopagos as $metodo)
            <tr>
                <td>{{ $metodo->nombre }}</td>
                <td>{{ $metodo->descripcion }}</td>
                <td>
                    @if($metodo->es_credito)
                        <span class="badge bg-success">Crédito</span>
                    @else
                        <span class="badge bg-secondary">Contado</span>
                    @endif
                </td>
                <td>{{ $metodo->activo ? '✅ Sí' : '❌ No' }}</td>
                <td>
                    <a href="{{ route('metodopagos.edit', $metodo) }}" class="btn btn-sm btn-light btn-sm">✏️ Editar</a>
                    <form action="{{ route('metodopagos.destroy', $metodo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar método de pago?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-light btn-sm">❌ Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $('#metodopagosTable').DataTable({
        "pagingType": "simple_numbers",
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros en total)",
            "search": "Buscar:",
            "paginate": {
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });
</script>
@endsection
