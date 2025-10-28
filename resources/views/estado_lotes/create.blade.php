@extends('layouts.app')

@section('content')
    <h3 class="mb-3">Nuevo Estado de Lote</h3>

    <form action="{{ route('estado_lotes.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Nombre del Estado</label>
                <input type="text" name="estado" value="{{ old('estado') }}" class="form-control @error('estado') is-invalid @enderror">
                @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-2">
                <label>Color</label>
                <input type="color" 
                    name="color" 
                    value="{{ old('color', $modelo->color ?? '#000000') }}" 
                    class="form-control form-control-color @error('color') is-invalid @enderror" 
                    title="Elige un color">
                @error('color') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

        </div>

        <button type="submit" class="btn btn-success mt-3">Guardar</button>
        <a href="{{ route('estado_lotes.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
@endsection
