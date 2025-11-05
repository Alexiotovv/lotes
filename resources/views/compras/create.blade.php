@extends('layouts.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container-fluid">
    <h3>Registrar Compra</h3>

    <form action="{{ route('compras.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Caja *</label>
                <select name="caja_id" class="form-select select2" required>
                    <option value="">Seleccione</option>
                    @foreach($cajas as $caja)
                        <option value="{{ $caja->id }}">{{ $caja->nombre }} ({{ $caja->tipo }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Concepto *</label>
                <select name="concepto_id" class="form-select select2" required>
                    <option value="">Seleccione</option>
                    @foreach($conceptos as $concepto)
                        <option value="{{ $concepto->id }}">{{ $concepto->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Monto *</label>
                <div class="input-group">
                    <span class="input-group-text">S/</span>
                    <input type="number" step="0.01" name="monto" class="form-control" required>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Referencia (opcional)</label>
                <input type="text" name="referencia" class="form-control" placeholder="N° de operación, recibo, etc.">
            </div>

            <div class="col-12">
                <label class="form-label">Descripción (opcional)</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Comprobante (opcional)</label>
                <input type="file" name="comprobante" class="form-control" accept="image/*">
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">💾 Registrar Compra</button>
            <a href="{{ route('compras.index') }}" class="btn btn-secondary">↩️ Cancelar</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    });
</script>
@endsection