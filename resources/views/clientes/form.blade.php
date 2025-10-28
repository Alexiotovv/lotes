<div class="row">
    <div class="col-md-4 mb-2">
        <label>DNI / RUC</label>
        <input type="text" name="dni_ruc" value="{{ old('dni_ruc', $cliente->dni_ruc ?? '') }}" class="form-control @error('dni_ruc') is-invalid @enderror">
        @error('dni_ruc') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8 mb-2">
        <label>Nombre del Cliente</label>
        <input type="text" name="nombre_cliente" value="{{ old('nombre_cliente', $cliente->nombre_cliente ?? '') }}" class="form-control @error('nombre_cliente') is-invalid @enderror">
        @error('nombre_cliente') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-2">
        <label>Género</label>
        <select name="genero" class="form-select">
            <option value="">-- Seleccione --</option>
            <option value="Masculino" {{ old('genero', $cliente->genero ?? '') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
            <option value="Femenino" {{ old('genero', $cliente->genero ?? '') == 'Femenino' ? 'selected' : '' }}>Femenino</option>
            <option value="Otro" {{ old('genero', $cliente->genero ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
    </div>

    <div class="col-md-8 mb-2">
        <label>Dirección</label>
        <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion ?? '') }}" class="form-control">
    </div>

    <div class="col-md-4 mb-2">
        <label>Departamento</label>
        <input type="text" name="departamento" value="{{ old('departamento', $cliente->departamento ?? '') }}" class="form-control">
    </div>

    <div class="col-md-4 mb-2">
        <label>Provincia</label>
        <input type="text" name="provincia" value="{{ old('provincia', $cliente->provincia ?? '') }}" class="form-control">
    </div>

    <div class="col-md-4 mb-2">
        <label>Distrito</label>
        <input type="text" name="distrito" value="{{ old('distrito', $cliente->distrito ?? '') }}" class="form-control">
    </div>

    <div class="col-md-4 mb-2">
        <label>Teléfono</label>
        <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono ?? '') }}" class="form-control">
    </div>
</div>
