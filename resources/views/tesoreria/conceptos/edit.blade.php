@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>✏️ Editar Concepto: {{ $concepto->nombre }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('tesoreria.conceptos.update', $concepto) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $concepto->nombre) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Seleccione</option>
                        <option value="ingreso" {{ old('tipo', $concepto->tipo) == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                        <option value="egreso" {{ old('tipo', $concepto->tipo) == 'egreso' ? 'selected' : '' }}>Egreso</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Categoría</label>
                    <input type="text" name="categoria" class="form-control" value="{{ old('categoria', $concepto->categoria) }}" placeholder="Ej: Ventas, Servicios, Personal">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">✅ Actualizar Concepto</button>
                    <a href="{{ route('tesoreria.conceptos.index') }}" class="btn btn-secondary">↩️ Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection