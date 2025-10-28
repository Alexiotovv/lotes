@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>💰 Créditos por Cliente: "{{ $nombre }}"</h5>
                    <a href="{{ route('reportes.ventas') }}" class="btn btn-sm btn-outline-secondary mt-2">
                        ↩️ Volver a Reportes
                    </a>
                </div>
                <div class="card-body">
                    @if($ventas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Venta</th>
                                        <th>Cliente</th>
                                        <th>Lote</th>
                                        <th>Fecha Venta</th>
                                        <th>Inicial</th>
                                        <th>Monto Financiar</th>
                                        <th>Cuota</th>
                                        <th>N° Cuotas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ventas as $v)
                                    <tr>
                                        <td>{{ $v->id }}</td>
                                        <td>{{ $v->cliente->nombre_cliente }}</td>
                                        <td>{{ $v->lote->codigo }} - {{ $v->lote->nombre }}</td>
                                        <td>{{ $v->created_at->format('d/m/Y') }}</td>
                                        <td>S/ {{ number_format($v->inicial, 2) }}</td>
                                        <td>S/ {{ number_format($v->monto_financiar, 2) }}</td>
                                        <td>S/ {{ number_format($v->cuota, 2) }}</td>
                                        <td>{{ $v->numero_cuotas }}</td>
                                        <td>
                                            <a href="{{ route('creditos.pagos', $v) }}" class="btn btn-sm btn-info">Ver Pagos</a>
                                            <a href="{{ route('creditos.calendario', $v) }}" class="btn btn-sm btn-primary" target="_blank">Calendario</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $ventas->links() }}
                    @else
                        <div class="alert alert-info">
                            No se encontraron créditos para el cliente "{{ $nombre }}".
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection