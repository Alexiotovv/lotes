@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>➕ Registrar Nueva Caja</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('tesoreria.cajas.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Seleccione</option>
                        <option value="efectivo" {{ old('tipo') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="banco" {{ old('tipo') == 'banco' ? 'selected' : '' }}>Banco</option>
                        <option value="digital" {{ old('tipo') == 'digital' ? 'selected' : '' }}>Digital</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Saldo Inicial</label>
                    <input type="number" step="0.01" name="saldo_inicial" class="form-control" value="{{ old('saldo_inicial', 0) }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">✅ Guardar Caja</button>
                    <a href="{{ route('tesoreria.cajas.index') }}" class="btn btn-secondary">↩️ Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection