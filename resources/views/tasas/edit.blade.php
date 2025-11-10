@extends('layouts.app')

@section('content')
<h3>✏️ Editar Tasa de Interés</h3>

<form action="{{ route('tasas.update', $tasa) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $tasa->nombre) }}" required>
            @error('nombre') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Monto (%) *</label>
            <input type="number" step="0.0001" name="monto" class="form-control" value="{{ old('monto', $tasa->monto) }}" required>
            @error('monto') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success">💾 Actualizar Tasa</button>
        <a href="{{ route('tasas.index') }}" class="btn btn-secondary">↩️ Volver</a>
    </div>
</form>
@endsection