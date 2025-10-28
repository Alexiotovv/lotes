@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>✏️ Editar Caja: {{ $caja->nombre }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('tesoreria.cajas.update', $caja) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $caja->nombre) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Seleccione</option>
                        <option value="efectivo" {{ old('tipo', $caja->tipo) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="banco" {{ old('tipo', $caja->tipo) == 'banco' ? 'selected' : '' }}>Banco</option>
                        <option value="digital" {{ old('tipo', $caja->tipo) == 'digital' ? 'selected' : '' }}>Digital</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Saldo Inicial</label>
                    <input type="number" step="0.01" name="saldo_inicial" class="form-control" value="{{ old('saldo_inicial', $caja->saldo_inicial) }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">✅ Actualizar Caja</button>
                    <a href="{{ route('tesoreria.cajas.index') }}" class="btn btn-secondary">↩️ Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection