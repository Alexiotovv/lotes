@extends('layouts.app')

@section('content')
    <h3 class="mb-4">Gestión de Lotes</h3>
    <a class="btn btn-primary mb-3" href="{{route('mapa.index')}}">
        + Ir a Mapa de Lotes
    </a>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Área (m²)</th>
                    <th>Precio (m²)</th>
                    <th>Latitud</th>
                    <th>Longitud</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lotes as $lote)
                    <tr>
                        <td>{{ $lote->id }}</td>
                        <td>{{ $lote->codigo }}</td>
                        <td>{{ $lote->nombre ?? '' }}</td>
                        <td>{{ $lote->area_m2 ?? '' }}</td>
                        <td>{{ $lote->precio_m2 ?? '' }}</td>
                        <td>{{ $lote->latitud ?? '' }}</td>
                        <td>{{ $lote->longitud ?? '' }}</td>
                        <td>{{ $lote->descripcion ?? '' }}</td>
                        <td>
                            {{ ucfirst($lote->estadoLote?->estado ?? 'Sin estado') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay lotes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection