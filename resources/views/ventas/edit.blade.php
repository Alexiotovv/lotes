@extends('layouts.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endsection

@section('content')
<h3>Editar Venta #{{ $venta->id }}</h3>

<form action="{{ route('ventas.update', $venta) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-2">
        <!-- Cliente -->
        <div class="col-md-4">
            <label>Cliente *</label>
            <select name="cliente_id" class="form-select select2" required>
                <option value="">Seleccione</option>
                @foreach($clientes as $c)
                    <option value="{{ $c->id }}" {{ $venta->cliente_id == $c->id ? 'selected' : '' }}>
                        {{ $c->nombre_cliente }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Método de Pago -->
        <div class="col-md-4">
            <label>Tipo de Venta *</label>
            <select name="metodopago_id" class="form-select" required>
                <option value="">Seleccione</option>
                @foreach($metodos as $m)
                    <option value="{{ $m->id }}" {{ $venta->metodopago_id == $m->id ? 'selected' : '' }}>
                        {{ $m->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Fecha Pago -->
        <div class="col-md-4">
            <label>Fecha Pago *</label>
            <input type="date" name="fecha_pago" class="form-control" value="{{ $venta->fecha_pago->format('Y-m-d') }}" required>
        </div>
        
        <!-- Buscar Lote -->
        <div class="col-md-3">
            <label>Buscar Lote</label>
            <select id="loteSelect" class="form-select select2" name="lote_id" required>
                <option value="">Seleccione un lote</option>
                @foreach($lotes as $lote)
                    <option value="{{ $lote->id }}" 
                        data-aream2="{{ $lote->area_m2 }}"
                        data-preciom2="{{ $lote->precio_m2 }}"
                        data-precio="{{ $lote->precio_m2 * $lote->area_m2 }}"
                        data-desc="{{ $lote->codigo }} - {{ $lote->nombre }}"
                        {{ $venta->lote_id == $lote->id ? 'selected' : '' }}>
                        {{ $lote->codigo }} - {{ $lote->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- N° Cuotas e Inicial -->
        <div class="col-md-3">
            <label>N° Cuotas</label>
            <input type="number" name="numero_cuotas" class="form-control" min="1" value="{{ $venta->numero_cuotas }}" required>
        </div>
        <div class="col-md-3">
            <label>Inicial (S/)</label>
            <input type="number" step="0.01" name="inicial" class="form-control" value="{{ $venta->inicial }}" required>
        </div>
        
        <!-- Tasa de Interés -->
        <div class="col-md-3">
            <label>Tasa de Interés (TEA)</label>
            <select id="tasaSelect" class="form-select">
                <option value="0.00" {{ $venta->tasa_interes == 0.00 ? 'selected' : '' }}>0% (Sin interés)</option>
                <option value="0.12" {{ $venta->tasa_interes == 0.12 ? 'selected' : '' }}>12.00%</option>
                <option value="0.15" {{ $venta->tasa_interes == 0.15 ? 'selected' : '' }}>15.00%</option>
                <option value="0.18" {{ $venta->tasa_interes == 0.18 ? 'selected' : '' }}>18.00%</option>
            </select>
            <input type="hidden" name="tasa_interes" id="tasaInteresInput" value="{{ $venta->tasa_interes }}">
        </div>
        
        <!-- Campos derivados -->
        <div class="col-md-3">
            <label>Área m²</label>
            <input type="text" id="area_m2" class="form-control" readonly value="{{ $venta->lote->area_m2 ?? '' }}">
        </div>
        <div class="col-md-3">
            <label>Precio m²</label>
            <input type="text" id="precio_m2" class="form-control" readonly value="{{ $venta->lote->precio_m2 ?? '' }}">
        </div>
        <div class="col-md-3">
            <label>Precio Total</label>
            <input type="text" id="precio_lote" class="form-control" readonly 
                   value="{{ $venta->lote ? number_format($venta->lote->area_m2 * $venta->lote->precio_m2, 2, '.', ',') : '' }}">
        </div>
        <div class="col-md-3">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales (opcional)">{{ $venta->observaciones }}</textarea>
        </div>
        
        <div class="col-md-12 mt-3">
            <button type="submit" class="btn btn-outline-success btn-sm">💾 Actualizar Venta</button>
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary btn-sm">↩️ Volver</a>
        </div>
    </div>
    
    <!-- Mostrar tasa y cuota -->
    <div class="row mt-3 mb-3 p-3 bg-light rounded">
        <div class="col-md-6">
            <label class="form-label fw-bold">Tasa de interés:</label>
            <div id="tasaMostrada" class="fs-5">{{ number_format($venta->tasa_interes * 100, 2) }}%</div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Cuota mensual estimada:</label>
            <div id="cuotaMostrada" class="fs-5 text-success">S/ {{ number_format($venta->cuota, 2) }}</div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        // Función para calcular TEM desde TEA
        function calcularTEM(tea) {
            return Math.pow(1 + parseFloat(tea), 1/12) - 1;
        }

        // Función para calcular cuota
        function calcularCuota(monto, tem, nCuotas) {
            if (tem === 0) {
                return monto / nCuotas;
            }
            return (monto * tem * Math.pow(1 + tem, nCuotas)) / (Math.pow(1 + tem, nCuotas) - 1);
        }

        // Actualizar campo oculto de tasa
        $('#tasaSelect').on('change', function() {
            const tea = $(this).val();
            $('#tasaInteresInput').val(tea);
            $('#tasaMostrada').text((parseFloat(tea) * 100).toFixed(2) + '%');
            recalcularCuota();
        });

        // Mostrar precio del lote
        $('#loteSelect').on('change', function() {
            const opt = $(this).find(':selected');
            const areaM2 = opt.data('aream2');
            const precioM2 = opt.data('preciom2');
            
            if (areaM2 && precioM2) {
                const precioTotal = areaM2 * precioM2;
                $('#precio_lote').val(precioTotal.toLocaleString('es-PE', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                }));
                $('#area_m2').val(areaM2);
                $('#precio_m2').val(precioM2.toLocaleString('es-PE', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                }));
            } else {
                $('#precio_lote, #area_m2, #precio_m2').val('');
            }
            recalcularCuota();
        });

        // Recalcular al cambiar inicial o cuotas
        $('input[name="inicial"], input[name="numero_cuotas"]').on('input change', recalcularCuota);

        function recalcularCuota() {
            const precioStr = $('#precio_lote').val();
            // Limpiar el formato para obtener el número
            const precio = precioStr ? parseFloat(precioStr.replace(/,/g, '')) : 0;
            const inicial = parseFloat($('input[name="inicial"]').val()) || 0;
            const nCuotas = parseInt($('input[name="numero_cuotas"]').val()) || 0;
            const tea = parseFloat($('#tasaInteresInput').val()) || 0;

            const montoFinanciar = Math.max(0, precio - inicial);
            
            if (nCuotas > 0 && montoFinanciar > 0) {
                const tem = calcularTEM(tea);
                const cuota = calcularCuota(montoFinanciar, tem, nCuotas);
                
                $('#cuotaMostrada').text('S/ ' + cuota.toLocaleString('es-PE', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                }));
            } else {
                $('#cuotaMostrada').text('S/ 0.00');
            }
        }

        // Inicializar valores al cargar
        setTimeout(function() {
            $('#loteSelect').trigger('change');
            $('#tasaSelect').trigger('change');
        }, 100);
    });
    // $(document).ready(function() {
    //     $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

    //     // Actualizar campo oculto de tasa
    //     $('#tasaSelect').on('change', function() {
    //         const tea = $(this).val();
    //         $('#tasaInteresInput').val(tea);
    //         $('#tasaMostrada').text((parseFloat(tea) * 100).toFixed(2) + '%');
    //         recalcularCuota();
    //     });

    //     // Mostrar precio del lote
    //     $('#loteSelect').on('change', function() {
    //         const opt = $(this).find(':selected');
    //         const precio = opt.data('precio');
    //         if (precio) {
    //             $('#precio_lote').val(parseFloat(precio).toLocaleString('es-PE', { minimumFractionDigits: 2 }));
    //             $('#area_m2').val(opt.data('aream2'));
    //             $('#precio_m2').val(opt.data('preciom2'));
    //         } else {
    //             $('#precio_lote, #area_m2, #precio_m2').val('');
    //         }
    //         recalcularCuota();
    //     });

    //     // Recalcular al cambiar inicial o cuotas
    //     $('input[name="inicial"], input[name="numero_cuotas"]').on('input change', recalcularCuota);

    //     function recalcularCuota() {
    //         const precioStr = $('#precio_lote').val();
    //         const precio = precioStr ? parseFloat(precioStr.replace(/,/g, '')) : 0;
    //         const inicial = parseFloat($('input[name="inicial"]').val()) || 0;
    //         const nCuotas = parseInt($('input[name="numero_cuotas"]').val()) || 0;
    //         const tea = parseFloat($('#tasaInteresInput').val());

    //         const montoFinanciar = Math.max(0, precio - inicial);
    //         if (nCuotas > 0 && montoFinanciar > 0) {
    //             if (tea > 0) {
    //                 const tem = Math.pow(1 + tea, 1/12) - 1;
    //                 const cuota = (montoFinanciar * tem * Math.pow(1 + tem, nCuotas)) / (Math.pow(1 + tem, nCuotas) - 1);
    //                 $('#cuotaMostrada').text('S/ ' + cuota.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    //             } else {
    //                 const cuota = montoFinanciar / nCuotas;
    //                 $('#cuotaMostrada').text('S/ ' + cuota.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    //             }
    //         } else {
    //             $('#cuotaMostrada').text('S/ --');
    //         }
    //     }

    //     // Inicializar valores
    //     $('#tasaMostrada').text((parseFloat($('#tasaInteresInput').val()) * 100).toFixed(2) + '%');
    // });
</script>
@endsection