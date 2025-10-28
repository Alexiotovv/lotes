@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>➕ Registrar Nuevo Movimiento</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('tesoreria.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Caja *</label>
                    <select name="caja_id" class="form-select" required>
                        <option value="">Seleccione</option>
                        @foreach($cajas as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->tipo }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Concepto *</label>
                    <select name="concepto_id" class="form-select" required>
                        <option value="">Seleccione</option>
                        @foreach($conceptos->groupBy('tipo') as $tipo => $grupo)
                            <optgroup label="{{ ucfirst($tipo) }}">
                                @foreach($grupo as $concepto)
                                    <option value="{{ $concepto->id }}">{{ $concepto->nombre }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Venta (solo para ingresos)</label>
                    <select name="venta_id" class="form-select">
                        <option value="">No aplica</option>
                        @foreach($ventas as $v)
                            <option value="{{ $v->id }}">
                                {{ $v->cliente->nombre_cliente }} - Venta #{{ $v->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Monto *</label>
                    <input type="number" step="0.01" name="monto" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha *</label>
                    <input type="date" name="fecha" class="form-control" value="{{ today()->toDateString() }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Referencia</label>
                    <input type="text" name="referencia" class="form-control" placeholder="N° operación, recibo, etc.">
                </div>
                <div class="col-12">
                    <label class="form-label">Comprobante (voucher)</label>
                    <input type="file" name="comprobante" class="form-control" accept="image/*">
                    <small class="text-muted">Formatos: JPG, PNG (máx. 2MB)</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">✅ Registrar Movimiento</button>
                    <a href="{{ route('tesoreria.index') }}" class="btn btn-secondary">↩️ Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection