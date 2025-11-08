@extends('layouts.app')

@section('content')
    <h3 class="mb-3">Estados de Lotes</h3>

    <a href="{{ route('estado_lotes.create') }}" class="btn btn-primary mb-3">+ Nuevo Estado</a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estado</th>
                    <th>Color</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estados as $estado)
                    <tr>
                        <td>{{ $estado->id }}</td>
                        <td>{{ $estado->estado }}</td>
                        <td>
                            <span style="background-color: {{ $estado->color }}; color:white; padding:4px 10px; border-radius:5px;">
                                {{ $estado->color }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('estado_lotes.edit', $estado) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('estado_lotes.destroy', $estado) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este estado?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
