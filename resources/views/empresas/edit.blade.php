@extends('layouts.app')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Información de la Empresa</h3>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('empresa.update', $empresa) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $empresa->nombre) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">RUC *</label>
                    <input type="text" name="ruc" class="form-control" value="{{ old('ruc', $empresa->ruc) }}" required maxlength="11">
                </div>

                <div class="col-3">
                    <label class="form-label">Dirección *</label>
                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $empresa->direccion) }}" required>
                </div>
                <div class="col-4">
                    <label class="form-label">Departamento *</label>
                    <input type="text" name="departamento" class="form-control" value="{{ old('departamento', $empresa->departamento) }}" required>
                </div>
                <div class="col-4">
                    <label class="form-label">Provincia *</label>
                    <input type="text" name="provincia" class="form-control" value="{{ old('provincia', $empresa->provincia) }}" required>
                </div>
                <div class="col-4">
                    <label class="form-label">Distrito *</label>
                    <input type="text" name="distrito" class="form-control" value="{{ old('distrito', $empresa->distrito) }}" required>
                </div>
                <div class="col-3"> 
                    <label class="form-label">Teléfono *</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $empresa->telefono) }}" required>
                </div>

                <div class="col-4">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $empresa->descripcion) }}</textarea>
                </div>

                <!-- Campo para subir logo -->
                <div class="col-4">
                    <label class="form-label">Logo de la Empresa</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if($empresa->logo)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo actual" style="height: 60px; object-fit: contain;">
                            <br>
                            <small>Logo actual (se reemplazará si sube uno nuevo)</small>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        💾 Actualizar Información
                    </button>
                    <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary btn-sm">↩️ Volver</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif
</script>
@endsection