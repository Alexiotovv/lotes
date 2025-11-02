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
    <div class="col-md-6 mb-2">
    
    <label>Tipo de Venta</label><br>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="es_credito" id="es_credito_false" value="0" 
            {{ old('es_credito', $metodopago->es_credito ?? false) == false ? 'checked' : '' }}>
        <label class="form-check-label" for="es_credito_false">Contado</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="es_credito" id="es_credito_true" value="1" 
            {{ old('es_credito', $metodopago->es_credito ?? false) == true ? 'checked' : '' }}>
        <label class="form-check-label" for="es_credito_true">Crédito</label>
    </div>
</div>

    <div class="col-md-12 mb-2">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $metodopago->descripcion ?? '') }}</textarea>
    </div>
</div>
