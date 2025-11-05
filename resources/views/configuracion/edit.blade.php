@extends('layouts.app')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<style>
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    .section-title {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        color: #495057;
    }
    .config-item {
        background: #f8f9fa;
        padding: 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .btn-save {
        min-width: 100px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="section-title">
        <h3>⚙️ Configuración General</h3>
    </div>

    <!-- Monto de Reserva -->
    <div class="config-item">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 me-3">
                <h5>Monto de Reserva por Defecto</h5>
                <p class="text-muted mb-2">Monto inicial sugerido al registrar una reserva de lote.</p>
                <div class="input-group">
                    <span class="input-group-text">S/</span>
                    <input 
                        type="number" 
                        step="0.01" 
                        id="monto_reserva_default"
                        class="form-control"
                        value="{{ $config->monto_reserva_default }}"
                        min="0"
                    >
                </div>
            </div>
            <button 
                type="button" 
                class="btn btn-outline-success btn-save"
                onclick="guardarConfig('monto_reserva_default')"
            >
                💾 Guardar
            </button>
        </div>
    </div>

    <!-- Registrar Lote como Compra -->
    <div class="config-item">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 me-3">
                <h5>Registrar Lote como Compra</h5>
                <p class="text-muted mb-2">
                    Al crear un lote, se generará automáticamente un movimiento de egreso en tesorería.
                </p>
                <div class="form-check form-switch">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="registrar_lote_compra"
                        {{ $config->registrar_lote_compra ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="registrar_lote_compra">
                        {{ $config->registrar_lote_compra ? 'Activado' : 'Desactivado' }}
                    </label>
                </div>
            </div>
            <button 
                type="button" 
                class="btn btn-outline-success btn-save"
                onclick="guardarConfig('registrar_lote_compra')"
            >
                💾 Guardar
            </button>
        </div>
    </div>
    <!-- Monto de Compra de Lote -->
    <div class="config-item">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 me-3">
                <h5>Monto de Compra por Lote</h5>
                <p class="text-muted mb-2">Monto que se registrará en tesorería al crear un lote (solo si está activada la opción).</p>
                <div class="input-group">
                    <span class="input-group-text">S/</span>
                    <input 
                        type="number" 
                        step="0.01" 
                        id="monto_compra_lote"
                        class="form-control"
                        value="{{ $config->monto_compra_lote }}"
                        min="0"
                    >
                </div>
            </div>
            <button 
                type="button" 
                class="btn btn-outline-success btn-save"
                onclick="guardarConfig('monto_compra_lote')"
            >
                💾 Guardar
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    // ✅ Obtener el token directamente desde Blade
    const csrfToken = '{{ csrf_token() }}';

    function guardarConfig(campo) {
        let valor;

        if (campo === 'monto_reserva_default') {
            valor = $('#monto_reserva_default').val();
            if (valor === '' || parseFloat(valor) < 0) {
                toastr.error('Ingrese un monto válido.');
                return;
            }
        } else if (campo === 'registrar_lote_compra') {
            valor = $('#registrar_lote_compra').is(':checked');
        } 
        // ✅ ¡Agregue este bloque!
        else if (campo === 'monto_compra_lote') {
            valor = $('#monto_compra_lote').val();
            if (valor === '' || parseFloat(valor) < 0) {
                toastr.error('Ingrese un monto de compra válido.');
                return;
            }
        }

        $.ajax({
            url: "{{ route('configuracion.update') }}",
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                campo: campo,
                valor: valor
            },
            success: function(response) {
                toastr.success(response.message || 'Configuración guardada.');
                if (campo === 'registrar_lote_compra') {
                    const label = valor ? 'Activado' : 'Desactivado';
                    $('label[for="registrar_lote_compra"]').text(label);
                }
            },
            error: function(xhr) {
                let msg = 'Error al guardar.';
                if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors)[0][0];
                }
                toastr.error(msg);
            }
        });
    }
</script>
@endsection