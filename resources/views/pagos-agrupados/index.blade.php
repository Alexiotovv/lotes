@extends('layouts.app')

@section('styles')
<style>
    .venta-item {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    .venta-header {
        background: #f8f9fa;
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #e0e0e0;
    }
    .venta-content {
        padding: 15px;
        background: white;
    }
    .cuota-item {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
        background: #fafafa;
    }
    .cuota-item.selected {
        border-color: #007bff;
        background: #e3f2fd;
    }
    .total-resumen {
        background: #e8f5e8;
        border: 2px solid #28a745;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }
    #listaVentas {
        max-height: 600px;
        overflow-y: auto;
    }
    #listaVentas::-webkit-scrollbar {
        width: 8px;
    }
    #listaVentas::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    #listaVentas::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <h4>💰 Pagos Agrupados por Cliente</h4>

    <!-- 🔍 Barra de búsqueda -->
    <form method="GET" action="{{ route('pagos-agrupados.index') }}" class="mb-4">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text">🔍</span>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Buscar cliente por nombre o DNI/RUC"
                        value="{{ request('search') }}"
                    >
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100" type="submit">🔎 Buscar</button>
                @if(request('search'))
                    <a href="{{ route('pagos-agrupados.index') }}" class="btn btn-outline-danger w-100 ms-2">✕ Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    @if(request('search'))
    <div class="alert alert-info mb-4">
        <strong>Búsqueda:</strong> "{{ request('search') }}"
        <a href="{{ route('pagos-agrupados.index') }}" class="float-end text-decoration-none">✖️ Quitar filtro</a>
    </div>
    @endif

    <div class="row">
        <!-- Lista de Clientes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Clientes con Pagos Pendientes</h5>
                </div>
                <div class="card-body">
                    @if($clientes->count() > 0)
                        <div class="list-group" id="listaClientes">
                            @foreach($clientes as $cliente)
                                @php
                                    $totalPendiente = 0;
                                    foreach($cliente->ventas as $venta) {
                                        foreach($venta->cronogramas as $cronograma) {
                                            if(in_array($cronograma->estado, ['pendiente', 'vencido'])) {
                                                $pagado = $cronograma->pagos->sum('monto_pagado');
                                                $totalPendiente += max(0, $cronograma->cuota - $pagado);
                                            }
                                        }
                                    }
                                @endphp
                                <button type="button" 
                                        class="list-group-item list-group-item-action cliente-item"
                                        data-cliente-id="{{ $cliente->id }}"
                                        data-cliente-nombre="{{ $cliente->nombre_cliente }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $cliente->nombre_cliente }}</strong>
                                            <br>
                                            <small class="text-muted">DNI/RUC: {{ $cliente->dni_ruc }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-warning">S/ {{ number_format($totalPendiente, 2) }}</span>
                                            <br>
                                            <small class="text-muted">{{ $cliente->ventas->count() }} lote(s)</small>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            No se encontraron clientes con pagos pendientes.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ventas del Cliente Seleccionado -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Ventas del Cliente</h5>
                </div>
                <div class="card-body">
                    <div id="sinClienteSeleccionado" class="text-center py-4">
                        <i class="fas fa-users text-muted fs-1 mb-2"></i>
                        <p class="text-muted">Seleccione un cliente para ver sus ventas y cuotas pendientes.</p>
                    </div>

                    <div id="clienteSeleccionado" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 id="nombreCliente" class="mb-0"></h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodasCuotas()">
                                ✅ Seleccionar Todas
                            </button>
                        </div>
                        
                        <div id="listaVentas" class="mb-3"></div>
                        
                        <!-- Resumen de Pagos -->
                        <div id="resumenPagos" class="total-resumen" style="display: none;">
                            <h6 class="mb-3">Resumen de Pagos Seleccionados</h6>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Total a Pagar:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span id="totalPagar" class="fw-bold fs-5 text-success">S/ 0.00</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small>Cuotas seleccionadas:</small>
                                </div>
                                <div class="col-6 text-end">
                                    <small id="cuotasSeleccionadas" class="text-muted">0</small>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para abrir modal de pago -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-success btn-lg" id="btnPagarAgrupado" style="display: none;" onclick="abrirModalPago()">
                                💰 Realizar Pago Agrupado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registrar Pago Agrupado -->
<div class="modal fade" id="modalPagoAgrupado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Registrar Pago Agrupado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPagoAgrupado" method="POST" action="{{ route('pagos-agrupados.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="cliente_id" id="cliente_id_modal">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Caja de destino:</label>
                            <select name="caja_id" class="form-select" required>
                                <option value="">Seleccione una caja</option>
                                @foreach($cajas as $caja)
                                    <option value="{{ $caja->id }}">
                                        {{ $caja->nombre }} 
                                        @if($caja->tipo === 'banco') 
                                            (Banco)
                                        @elseif($caja->tipo === 'digital')
                                            (Digital)
                                        @else
                                            (Efectivo)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de pago:</label>
                            <input type="date" name="fecha_pago" class="form-control" value="{{ today()->toDateString() }}" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Método de pago:</label>
                            <input type="text" name="metodo_pago" class="form-control" placeholder="Efectivo, Transferencia, etc.">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Referencia:</label>
                            <input type="text" name="referencia" class="form-control" placeholder="N° operación, recibo, etc.">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Comprobante (voucher):</label>
                            <input type="file" name="voucher" class="form-control" accept="image/*">
                            <small class="text-muted">Formatos: JPG, PNG, GIF (máx. 4MB)</small>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Observación:</label>
                            <textarea name="observacion" class="form-control" rows="3" placeholder="Notas adicionales del pago agrupado..."></textarea>
                        </div>
                        
                        <!-- Resumen final -->
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6>Resumen del Pago:</h6>
                                <div id="resumenModal"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">✅ Registrar Pagos Agrupados</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let clienteSeleccionado = null;
    let ventasCliente = [];
    let cuotasSeleccionadas = new Map();

    // Seleccionar cliente
    document.querySelectorAll('.cliente-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const clienteId = this.dataset.clienteId;
            const clienteNombre = this.dataset.clienteNombre;
            
            // Resetear selección anterior
            document.querySelectorAll('.cliente-item').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
            
            clienteSeleccionado = clienteId;
            cargarVentasCliente(clienteId, clienteNombre);
        });
    });

    // Cargar ventas del cliente
    function cargarVentasCliente(clienteId, clienteNombre) {
        document.getElementById('sinClienteSeleccionado').style.display = 'none';
        document.getElementById('clienteSeleccionado').style.display = 'block';
        document.getElementById('nombreCliente').textContent = clienteNombre;
        document.getElementById('listaVentas').innerHTML = '<div class="text-center"><div class="spinner-border"></div><p>Cargando ventas...</p></div>';
        
        // Resetear selecciones anteriores
        cuotasSeleccionadas.clear();
        actualizarResumen();
        
        fetch(`/pagos-agrupados/cliente/${clienteId}/ventas`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => {
            if (!r.ok) {
                throw new Error(`HTTP error! status: ${r.status}`);
            }
            return r.json();
        })
        .then(ventas => {
            console.log('Ventas recibidas:', ventas);
            ventasCliente = ventas;
            mostrarVentas(ventas);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('listaVentas').innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error al cargar las ventas</h6>
                    <small>${error.message}</small>
                    <br>
                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="cargarVentasCliente(${clienteId}, '${clienteNombre}')">
                        Reintentar
                    </button>
                </div>
            `;
        });
    }

    // Mostrar ventas en la interfaz (solo primera cuota pendiente)
    function mostrarVentas(ventas) {
        let html = '';
        
        if (!ventas || ventas.length === 0) {
            html = '<div class="alert alert-info">No hay cuotas pendientes para este cliente.</div>';
            document.getElementById('listaVentas').innerHTML = html;
            actualizarResumen();
            return;
        }
        
        ventas.forEach(venta => {
            if (venta.cuota_pendiente) {
                const cuota = venta.cuota_pendiente;
                const cuotaTotal = parseFloat(cuota.cuota_total || 0);
                const pagado = parseFloat(cuota.pagado || 0);
                const pendiente = parseFloat(cuota.pendiente || 0);
                
                html += `
                    <div class="venta-item">
                        <div class="venta-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Lote: ${venta.lote_codigo} - ${venta.lote_nombre}</strong>
                                    <br>
                                    <small class="text-muted">Venta #${venta.id}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-info">Cuota #${cuota.nro_cuota || 'N/A'}</span>
                                    <br>
                                    <small class="text-muted">${cuota.fecha_pago || 'Sin fecha'}</small>
                                </div>
                            </div>
                        </div>
                        <div class="venta-content">
                            <div class="cuota-item" id="cuota-${cuota.id}">
                                <div class="form-check">
                                    <input class="form-check-input cuota-checkbox" 
                                        type="checkbox" 
                                        value="${cuota.id}"
                                        data-venta-id="${venta.id}"
                                        data-monto="${pendiente}"
                                        data-cuota-info="Cuota #${cuota.nro_cuota || 'N/A'} - ${cuota.fecha_pago || 'Sin fecha'}"
                                        onchange="toggleCuota(this)">
                                    <label class="form-check-label w-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Próximo Pago: Cuota #${cuota.nro_cuota || 'N/A'}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    Vence: ${cuota.fecha_pago || 'Sin fecha'}
                                                    <span class="badge bg-${cuota.estado === 'vencido' ? 'danger' : 'warning'} ms-1">
                                                        ${(cuota.estado || 'pendiente').toUpperCase()}
                                                    </span>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    Restan ${cuota.cuotas_pendientes_restantes || 0} cuota(s) pendiente(s)
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div><small>Total cuota:</small> S/ ${cuotaTotal.toFixed(2)}</div>
                                                <div><small>Pagado:</small> S/ ${pagado.toFixed(2)}</div>
                                                <div><strong><small>Pendiente:</small> S/ ${pendiente.toFixed(2)}</strong></div>
                                                ${cuota.cuotas_pendientes_restantes > 0 ? `
                                                    <div><small class="text-muted">+ ${cuota.cuotas_pendientes_restantes} cuota(s) pendiente(s)</small></div>
                                                ` : ''}
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> 
                                                Se mostrará la siguiente cuota pendiente después de pagar esta.
                                            </small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <!-- Información adicional de la venta -->
                            <div class="mt-2 p-2 bg-light rounded">
                                <div class="row">
                                    <div class="col-6">
                                        <small><strong>Total deuda total:</strong></small>
                                        <br>
                                        <small>S/ ${parseFloat(venta.total_pendiente || 0).toFixed(2)}</small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small><strong>Total venta:</strong></small>
                                        <br>
                                        <small>S/ ${parseFloat(venta.total_venta || 0).toFixed(2)}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
        
        document.getElementById('listaVentas').innerHTML = html || '<div class="alert alert-info">No hay cuotas pendientes para este cliente.</div>';
        actualizarResumen();
    }

    // Seleccionar todas las cuotas (ahora solo las primeras de cada venta)
    function seleccionarTodasCuotas() {
        document.querySelectorAll('.cuota-checkbox').forEach(checkbox => {
            if (!checkbox.checked) {
                checkbox.checked = true;
                toggleCuota(checkbox);
            }
        });
    }

    // Actualizar resumen y mostrar/ocultar botón
    function actualizarResumen() {
        const total = Array.from(cuotasSeleccionadas.values()).reduce((sum, cuota) => sum + cuota.monto, 0);
        const count = cuotasSeleccionadas.size;
        
        console.log('Actualizando resumen:', { total, count, cuotasSeleccionadas: Array.from(cuotasSeleccionadas) });
        
        const btnPagar = document.getElementById('btnPagarAgrupado');
        const resumenPagos = document.getElementById('resumenPagos');
        const totalPagar = document.getElementById('totalPagar');
        const cuotasSeleccionadasSpan = document.getElementById('cuotasSeleccionadas');
        
        if (count > 0) {
            // Mostrar elementos
            if (resumenPagos) resumenPagos.style.display = 'block';
            if (btnPagar) btnPagar.style.display = 'block';
            if (totalPagar) totalPagar.textContent = `S/ ${total.toFixed(2)}`;
            if (cuotasSeleccionadasSpan) cuotasSeleccionadasSpan.textContent = count;
            
            console.log('Mostrando botón de pago');
        } else {
            // Ocultar elementos
            if (resumenPagos) resumenPagos.style.display = 'none';
            if (btnPagar) btnPagar.style.display = 'none';
            
            console.log('Ocultando botón de pago');
        }
        
    }

    // Al seleccionar/deseleccionar una cuota
    function toggleCuota(checkbox) {
        const cuotaId = checkbox.value;
        const cuotaItem = document.getElementById(`cuota-${cuotaId}`);
        
        if (checkbox.checked) {
            cuotasSeleccionadas.set(cuotaId, {
                cronograma_id: cuotaId,
                monto: parseFloat(checkbox.dataset.monto),
                info: checkbox.dataset.cuotaInfo
            });
            if (cuotaItem) cuotaItem.classList.add('selected');
            console.log('Cuota seleccionada:', cuotaId);
        } else {
            cuotasSeleccionadas.delete(cuotaId);
            if (cuotaItem) cuotaItem.classList.remove('selected');
            console.log('Cuota deseleccionada:', cuotaId);
        }
        
        actualizarResumen(); // ← Asegúrate que se llama aquí
    }


    // Abrir modal de pago
    function abrirModalPago() {
        if (cuotasSeleccionadas.size === 0) {
            alert('Seleccione al menos una cuota para pagar.');
            return;
        }
        
        document.getElementById('cliente_id_modal').value = clienteSeleccionado;
        
        // Actualizar resumen en modal
        const total = Array.from(cuotasSeleccionadas.values()).reduce((sum, cuota) => sum + cuota.monto, 0);
        let resumenHtml = `<p><strong>Total a pagar: S/ ${total.toFixed(2)}</strong></p>`;
        resumenHtml += `<p>Cuotas seleccionadas (${cuotasSeleccionadas.size}):</p><ul>`;
        
        cuotasSeleccionadas.forEach(cuota => {
            resumenHtml += `<li>${cuota.info} - S/ ${cuota.monto.toFixed(2)}</li>`;
        });
        
        resumenHtml += '</ul>';
        document.getElementById('resumenModal').innerHTML = resumenHtml;
        
        new bootstrap.Modal(document.getElementById('modalPagoAgrupado')).show();
    }

    // Enviar formulario de pago agrupado
    document.getElementById('formPagoAgrupado').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Procesando...';
        
        // Crear FormData
        const formData = new FormData(this);
        
        // Convertir cuotas seleccionadas a array
        const pagosArray = Array.from(cuotasSeleccionadas.values());
        
        console.log('Pagos a enviar:', pagosArray);
        console.log('FormData antes de agregar pagos:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ', pair[1]);
        }
        
        // IMPORTANTE: Agregar pagos como string JSON
        formData.append('pagos', JSON.stringify(pagosArray));
        
        console.log('FormData después de agregar pagos:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ', pair[1]);
        }
        
        // Enviar datos
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
                // NO agregar 'Content-Type': 'application/json' cuando usas FormData
            }
        })
        .then(async r => {
            console.log('Respuesta status:', r.status);
            console.log('Respuesta headers:', r.headers);
            
            if (!r.ok) {
                // Para error 422, obtener detalles
                if (r.status === 422) {
                    try {
                        const errorData = await r.json();
                        console.error('Error 422 detalles:', errorData);
                        
                        let errorMessage = 'Error de validación:\n';
                        if (errorData.errors) {
                            Object.keys(errorData.errors).forEach(key => {
                                errorData.errors[key].forEach(msg => {
                                    errorMessage += `• ${msg}\n`;
                                });
                            });
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                        
                        throw new Error(errorMessage);
                    } catch (parseError) {
                        throw new Error(`Error ${r.status}: No se pudo procesar la respuesta del servidor`);
                    }
                }
                throw new Error(`HTTP error! status: ${r.status}`);
            }
            return r.json();
        })
        .then(data => {
            console.log('Respuesta exitosa:', data);
            if (data.success) {
                let message = '✅ ' + data.message;
                
                if (data.next_cuotas && data.next_cuotas.length > 0) {
                    message += '\n\n📅 Próximas cuotas disponibles para pago:';
                    data.next_cuotas.forEach(cuota => {
                        message += `\n• Venta #${cuota.venta_id} (${cuota.lote_codigo}): Cuota #${cuota.proxima_cuota_nro} - ${cuota.proxima_cuota_fecha}`;
                    });
                }
                
                if (confirm(message + '\n\n¿Recargar página para ver las nuevas cuotas pendientes?')) {
                    location.reload();
                }
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert('❌ ' + error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
</script>
@endsection