@extends('layouts.app')

@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/select2-bootstrap.css')}}" rel="stylesheet" />
    <link href="{{asset('css/toastr.min.css')}}" rel="stylesheet">
@endsection

@section('content')
    <h3>Registrar Reserva</h3>
    <form action="{{ route('reservas.store') }}" method="POST">
        @csrf
        <input type="hidden" name="lote_id" value="{{ $lote->id ?? '' }}">

        <div class="row g-3">
            @if($lote)
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Lote seleccionado:</strong> {{ $lote->codigo }} - {{ $lote->nombre }}
                </div>
            </div>
            @endif

            <div class="col-md-6">
                <label>Cliente *</label>
                <select name="cliente_id" class="form-select select2" required>
                    <option value="">Seleccione</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre_cliente }}</option>
                    @endforeach
                </select>
            </div>

            @unless($lote)
            <div class="col-md-6">
                <label>Lote *</label>
                <select name="lote_id" class="form-select select2" required>
                    <option value="">Seleccione un lote disponible</option>
                    @foreach(\App\Models\Lote::where('estado_lote_id', 1)->get() as $l)
                        <option value="{{ $l->id }}">{{ $l->codigo }} - {{ $l->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endunless

            <div class="col-md-4">
                <label>Monto *</label>
                <input type="number" value="{{$monto_reserva}}" step="0.01" name="monto" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Fecha *</label>
                <input type="date" readonly name="fecha_reserva" class="form-control" value="{{ old('fecha_reserva', date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-4">
                <label>Caja *</label>
                <select name="caja_id" class="form-select" required>
                    @foreach($cajas as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->tipo }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control"></textarea>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">💾 Guardar Reserva</button>
            <a href="{{ route('reservas.index') }}" class="btn btn-secondary">↩️ Cancelar</a>
        </div>
    </form>
@endsection

@section('scripts')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/toastr.min.js')}}"></script>
    <script src="{{ asset('js/select2-focus.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        });
    </script>
@endsection