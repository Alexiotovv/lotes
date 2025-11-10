@extends('layouts.app')

@section('css')
<link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
<link href="{{asset('css/select2-bootstrap.css')}}" rel="stylesheet" />
<link href="{{asset('css/toastr.min.css')}}" rel="stylesheet">

{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"> --}}
@endsection

@section('content')
    <h3>Registrar Venta</h3>
    {{-- @include('cotizaciones.form') --}}
    <form action="{{ route('ventas.store') }}" method="POST">
        @csrf
        <div class="row g-2">
        <!-- Cliente -->
            <div class="col-md-4">
                <label>Cliente *</label>
                <select name="cliente_id" class="form-select select2" required>
                    <option value="">Seleccione</option>
                    @foreach($clientes as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre_cliente }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Método de Pago (como relación) -->
            <div class="col-md-4">
                <label>Tipo de Venta *</label>
                <select name="metodopago_id" class="form-select" required>
                    <option value="">Seleccione</option>
                    @foreach($metodos as $m)
                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Fecha Pago -->
            <div class="col-md-4">
                <label>Fecha Pago *</label>
                <input type="date" name="fecha_pago" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            
            
            {{-- <div class="col-md-3">
                <label>Buscar Lote</label>
                <select id="loteSelect" class="form-select select2">
                    <option value="">Seleccione un lote</option>
                    @foreach($lotes as $lote)
                    <option value="{{ $lote->id }}" 
                        data-aream2="{{ $lote->area_m2 }}"
                        data-preciom2="{{ $lote->precio_m2 }}"
                        data-precio="{{ $lote->precio_m2 * $lote->area_m2 }}"
                        data-desc="{{ $lote->codigo }} - {{ $lote->nombre }}">
                        {{ $lote->codigo }} - {{ $lote->nombre }}
                    </option>
                    @endforeach
                </select>
            </div> --}}
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <label>Buscar Lote</label>
                    <!-- Badge dinámico que se actualizará con JS -->
                    <span id="badgeEstadoLote" class="badge ms-2" style="display:none;">&nbsp;</span>
                </div>

                <select id="loteSelect" class="form-select select2">
                    <option value="">Seleccione un lote</option>
                    @foreach($lotes as $lote)
                        @php
                            $estado = $lote->estadoLote->estado ?? 'sin estado';
                            $esReservado = $estado === 'reservado';
                            $clienteNombre = $esReservado ? ($lote->venta?->cliente?->nombre_cliente ?? 'Cliente no encontrado') : null;
                            $claseBadge = $estado === 'disponible' ? 'bg-success' : ($estado === 'reservado' ? 'bg-warning text-dark' : 'bg-secondary');
                        @endphp
                        <option value="{{ $lote->id }}"
                            data-aream2="{{ $lote->area_m2 }}"
                            data-preciom2="{{ $lote->precio_m2 }}"
                            data-precio="{{ $lote->precio_m2 * $lote->area_m2 }}"
                            data-desc="{{ $lote->codigo }} - {{ $lote->nombre }}"
                            data-estado="{{ $estado }}"
                            data-cliente="{{ $clienteNombre ?? '' }}"
                            data-clase-badge="{{ $claseBadge }}">
                            {{ $lote->codigo }} - {{ $lote->nombre }}
                            <span class="badge {{ $claseBadge }} ms-2">{{ $estado }}</span>
                            @if($esReservado)
                                <span class="text-muted ms-1">(Reservado a: {{ $clienteNombre }})</span>
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- N° Cuotas e Inicial -->
            <div class="col-md-3">
                <label>N° Cuotas</label>
                <input type="number" name="numero_cuotas" class="form-control" min="0" value="0"> <!-- min="0" -->
            </div>
            <div class="col-md-3">
                <label>Inicial (S/)</label>
                <input type="number" step="0.01" name="inicial" class="form-control" value="0" required>
            </div>
            
            <!-- Selector de tasa + campo oculto para enviar valor -->
            <div class="col-md-3">
                <label>Tasa de Interés (TEA)</label>
                <select id="tasaSelect" class="form-select">
                    @foreach($tasas as $tasa)
                        <option value="{{ $tasa->monto }}">{{ number_format($tasa->monto * 100, 2) }}% - {{ $tasa->nombre }}</option>
                    @endforeach
                </select>
                <!-- Campo oculto para enviar al backend -->
                <input type="hidden" name="tasa_interes" id="tasaInteresInput" value="{{ $tasas->first()?->monto ?? 0.00 }}">
            </div>
            
            <!-- Campos derivados del lote -->
            <div class="col-md-3">
                <label>Área m²</label>
                <input type="text" id="area_m2" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label>Precio m²</label>
                <input type="text" id="precio_m2" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label>Precio Total</label>
                <input type="text" id="precio_lote" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label>Observaciones</label>
                <textarea id="observacionesLote" class="form-control" rows="2" placeholder="Notas adicionales (opcional)"></textarea>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="btnAgregar" class="btn btn-primary btn-sm w-100">➕ Agregar</button>
            </div>
        </div>
        
        <!-- Mostrar tasa y cuota -->
        <div class="row mt-3 mb-3 p-3 bg-light rounded">
            <div class="col-md-6">
                <label class="form-label fw-bold">Tasa de interés:</label>
                <div id="tasaMostrada" class="fs-5">12.00%</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Cuota mensual estimada:</label>
                <div id="cuotaMostrada" class="fs-5 text-success">S/ --</div>
            </div>
        </div>
        <hr>
        
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Lote</th>
                    <th>Precio (S/)</th>
                    <th>Cliente</th>
                    <th>Método Pago</th>
                    <th>Fecha Pago</th>
                    <th>N° Cuotas</th>
                    <th>Inicial (S/)</th>
                    <th>TasaInterés</th>
                    <th>Obs.</th> 
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="detalleTabla">
                <tr><td colspan="8" class="text-center text-muted">Sin lotes agregados</td></tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-outline-success btn-sm mt-3" id="btnGuardar" disabled>💾 Guardar</button>
        <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary btn-sm mt-3">↩️ Volver</a>
    </form>
@endsection

@section('scripts')
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}
    <script src="{{asset('js/select2.min.js')}}"></script> 
    <script src="{{asset('js/toastr.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
            
            // Actualizar campo oculto de tasa al cambiar el select
            $('#tasaSelect').on('change', function() {
                const tea = $(this).val(); // "0.12", "0.15", etc.
                $('#tasaInteresInput').val(tea); // Asigna al hidden
                
                // Obtener el valor por defecto del select (el primer option)
                const tasaDefault = $('#tasaSelect').val();
                $('#tasaMostrada').text((parseFloat(tasaDefault) * 100).toFixed(2) + '%');
                
                recalcularCuota();
            });

        
            // Mostrar badge de estado al lado del label al cambiar el lote
            $('#loteSelect').on('change', function() {
                const opt = $(this).find(':selected');
                const estado = opt.data('estado');
                const cliente = opt.data('cliente');
                const claseBadge = opt.data('clase-badge');
                const badge = $('#badgeEstadoLote');

                if (estado) {
                    let texto = estado.toUpperCase();
                    if (estado === 'reservado' && cliente) {
                        texto += ` - ${cliente}`;
                    }

                    badge.removeClass().addClass(`badge ${claseBadge} ms-2`).text(texto).show();
                } else {
                    badge.hide();
                }

                // Lógica existente para mostrar precio
                const precio = opt.data('precio');
                if (precio) {
                    $('#precio_lote').val(parseFloat(precio).toLocaleString('es-PE', { minimumFractionDigits: 2 }));
                    $('#area_m2').val(opt.data('aream2'));
                    $('#precio_m2').val(opt.data('preciom2'));
                } else {
                    $('#precio_lote, #area_m2, #precio_m2').val('');
                }
                recalcularCuota();
            });

            // Recalcular al cambiar inicial o cuotas
            $('input[name="inicial"], input[name="numero_cuotas"]').on('input change', recalcularCuota);

            function recalcularCuota() {
                const precioStr = $('#precio_lote').val();
                const precio = precioStr ? parseFloat(precioStr.replace(/,/g, '')) : 0;
                const inicial = parseFloat($('input[name="inicial"]').val()) || 0;
                const nCuotas = parseInt($('input[name="numero_cuotas"]').val()) || 0;
                const tea = parseFloat($('#tasaInteresInput').val());

                const montoFinanciar = Math.max(0, precio - inicial);
                if (nCuotas > 0 && montoFinanciar > 0) {
                    if (tea > 0) {
                        const tem = Math.pow(1 + tea, 1/12) - 1;
                        const cuota = (montoFinanciar * tem * Math.pow(1 + tem, nCuotas)) / (Math.pow(1 + tem, nCuotas) - 1);
                        $('#cuotaMostrada').text('S/ ' + cuota.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    } else {
                        const cuota = montoFinanciar / nCuotas;
                        $('#cuotaMostrada').text('S/ ' + cuota.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    }
                } else {
                    $('#cuotaMostrada').text('S/ --');
                }
            }

            // === Agregar a la tabla ===
            let detalles = [];

            $('#btnAgregar').on('click', function() {
                const loteId = $('#loteSelect').val();
                if (!loteId) { toastr.warning('Seleccione un lote.'); return; }
                if (detalles.some(d => d.lote_id == loteId)) { toastr.warning('Lote ya agregado.'); return; }

                const clienteId = $('select[name="cliente_id"]').val();
                if (!clienteId) { toastr.warning('Seleccione un cliente.'); return; }

                const metodoPagoId = $('select[name="metodopago_id"]').val();
                if (!metodoPagoId) { toastr.warning('Seleccione Tipo de Venta.'); return; }

                const fechaPago = $('input[name="fecha_pago"]').val();
                const nCuotas = $('input[name="numero_cuotas"]').val();
                const inicial = parseFloat($('input[name="inicial"]').val()) || 0;
                const tea = parseFloat($('#tasaInteresInput').val());
                const observaciones = $('#observacionesLote').val().trim();

                const precioStr = $('#precio_lote').val();
                const precio = precioStr ? parseFloat(precioStr.replace(/,/g, '')) : 0;
                const montoFinanciar = Math.max(0, precio - inicial);

                // Permitir 0 cuotas (venta al contado)
                const numCuotas = parseInt(nCuotas);
                if (nCuotas === '' || (numCuotas < 0)) {
                    toastr.warning('N° de cuotas no válido.');
                    return;
                }

                // Calcular cuota
                let cuota = 0;
                if (numCuotas > 0 && montoFinanciar > 0) {
                    if (tea > 0) {
                        const tem = Math.pow(1 + tea, 1/12) - 1;
                        cuota = (montoFinanciar * tem * Math.pow(1 + tem, nCuotas)) / (Math.pow(1 + tem, nCuotas) - 1);
                    } else {
                        cuota = montoFinanciar / nCuotas;
                    }
                }
                // Si numCuotas === 0, cuota permanece 0 (venta al contado)

                detalles.push({
                    lote_id: loteId,
                    descripcion: $('#loteSelect option:selected').text(),
                    precio: precio,
                    cliente_id: clienteId,
                    cliente_nombre: $('select[name="cliente_id"] option:selected').text(),
                    metodopago_id: metodoPagoId,
                    metodopago_nombre: $('select[name="metodopago_id"] option:selected').text(),
                    fecha_pago: fechaPago,
                    numero_cuotas: nCuotas,
                    inicial: inicial,
                    tasa_interes: tea,
                    monto_financiar: montoFinanciar,
                    cuota: cuota,
                    observaciones: observaciones // ← ¡Nuevo!
                });

                renderTabla();
                $('#loteSelect').val('').trigger('change');
                $('#precio_lote, #area_m2, #precio_m2').val('');
                
            });

            function renderTabla() {
                const tbody = $('#detalleTabla');
                if (detalles.length === 0) {
                    tbody.html('<tr><td colspan="9" class="text-center text-muted">Sin lotes agregados</td></tr>');
                    $('#btnGuardar').prop('disabled', true);
                    return;
                }

                let filas = '';
                detalles.forEach((d, i) => {
                    const obs = d.observaciones ? d.observaciones : '—';
                    filas += `
                    <tr>
                        <td>${d.descripcion}<input type="hidden" name="detalles[${i}][lote_id]" value="${d.lote_id}"></td>
                        <td>S/ ${d.precio.toLocaleString('es-PE', { minimumFractionDigits: 2 })}</td>
                        <td>${d.cliente_nombre}</td>
                        <td>${d.metodopago_nombre}</td>
                        <td>${d.fecha_pago}</td>
                        <td>${d.numero_cuotas}</td>
                        <td>S/ ${d.inicial.toFixed(2)}</td>
                        <td>${(d.tasa_interes * 100).toFixed(2)}%</td>
                        <td title="${obs}">${obs.length > 20 ? obs.substring(0, 20) + '…' : obs}</td> <!-- Muestra resumen -->
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarDetalle(${i})">🗑️</button>
                        </td>
                        <!-- Campo oculto para enviar observaciones -->
                        <input type="hidden" name="detalles[${i}][observaciones]" value="${d.observaciones || ''}">
                    </tr>`;
                });

                tbody.html(filas);
                $('#btnGuardar').prop('disabled', false);
            }

            window.eliminarDetalle = function(i) {
                detalles.splice(i, 1);
                renderTabla();
            };

            // Inicializar valores
            $('#tasaMostrada').text('12.00%');
        });
    </script>

    <script src="{{ asset('js/select2-focus.js') }}"></script>

    <script>
        // Escuchar cambios en campos relevantes
        $('select[name="tasa_id"], input[name="inicial"], input[name="numero_cuotas"]').on('input change', recalcularCuota);
        $('#loteSelect').on('change', recalcularCuota);
    </script>

@endsection