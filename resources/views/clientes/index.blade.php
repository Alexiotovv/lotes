@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Listado de Clientes</h3>
        <a href="{{ route('clientes.create') }}" class="btn btn-light btn-sm">➕ Nuevo Cliente</a>
    </div>
    
    <table class="table table-bordered table-striped" id="clientesTable">
        <thead>
            <tr>
                <th>DNI/RUC</th>
                <th>Nombre</th>
                <th>Género</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th width="140">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr>
                <td>{{ $cliente->dni_ruc }}</td>
                <td>{{ $cliente->nombre_cliente }}</td>
                <td>{{ $cliente->genero }}</td>
                <td>{{ $cliente->direccion }}</td>
                <td>{{ $cliente->telefono }}</td>
                <td>
                    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-light btn-sm">✏️ Editar</a>
                    <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar cliente?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-light btn-sm">❌Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('scripts')
        <!-- DataTables con Bootstrap 5 -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        
        <!-- DataTables con Bootstrap 5 -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $('#clientesTable').DataTable({
                "pagingType": "simple_numbers", // estilo de paginación
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