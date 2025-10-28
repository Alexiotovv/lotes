<div class="row">
    <div class="col-md-6 mb-2">
        <label>Nombre del Método</label>
        <input type="text" name="nombre" value="{{ old('nombre', $metodopago->nombre ?? '') }}" class="form-control @error('nombre') is-invalid @enderror">
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-2">
        <label>Activo</label><br>
        <input type="checkbox" name="activo" value="1" {{ old('activo', $metodopago->activo ?? false) ? 'checked' : '' }}>
        <span>Disponible</span>
    </div>

    <div class="col-md-12 mb-2">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $metodopago->descripcion ?? '') }}</textarea>
    </div>
</div>
