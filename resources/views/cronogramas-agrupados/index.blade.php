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
        border-bottom: 1px solid #e0e0e0;
    }
    .venta-content {
        padding: 15px;
        background: white;
    }
    .form-group-agrupado {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .resumen-total {
        background: #e8f5e8;
        border: 2px solid #28a745;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📅 Cronogramas Agrupados</h4>
        <div class="btn-group">
            <a href="{{ route('cronogramas-agrupados.impresiones') }}" class="btn btn-outline-info">
                🖨️ Ver/Reimprimir Cronogramas
            </a>
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">
                ← Volver a Ventas
            </a>
        </div>
    </div>

    <!-- 🔍 Barra de búsqueda -->
    <form method="GET" action="{{ route('cronogramas-agrupados.index') }}" class="mb-4">
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
                    <a href="{{ route('cronogramas-agrupados.index') }}" class="btn btn-outline-danger w-100 ms-2">✕ Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    @if(request('search'))
    <div class="alert alert-info mb-4">
        <strong>Búsqueda:</strong> "{{ request('search') }}"
        <a href="{{ route('cronogramas-agrupados.index') }}" class="float-end text-decoration-none">✖️ Quitar filtro</a>
    </div>
    @endif

    <div class="row">
        <!-- Lista de Clientes con ventas pendientes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Clientes con Ventas Pendientes de Cronograma</h5>
                </div>
                <div class="card-body">
                    @if($clientes->count() > 0)
                        <div class="list-group" id="listaClientes">
                            @foreach($clientes as $cliente)
                                @php
                                    $ventasPendientes = $cliente->ventas->where('cronograma_generado', false)->count();
                                    $totalPendiente = $cliente->ventas->where('cronograma_generado', false)->sum('monto_financiar');
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
                                            <span class="badge bg-warning">{{ $ventasPendientes }} venta(s)</span>
                                            <br>
                                            <small class="text-muted">S/ {{ number_format($totalPendiente, 2) }} pendiente</small>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            No se encontraron clientes con ventas pendientes de cronograma.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ventas del Cliente Seleccionado -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Ventas para Cronograma Agrupado</h5>
                </div>
                <div class="card-body">
                    <div id="sinClienteSeleccionado" class="text-center py-4">
                        <i class="fas fa-users text-muted fs-1 mb-2"></i>
                        <p class="text-muted">Seleccione un cliente para ver sus ventas pendientes de cronograma.</p>
                    </div>

                    <div id="clienteSeleccionado" style="display: none;">
                        <div class="mb-3">
                            <h6 id="nombreCliente" class="mb-0"></h6>
                        </div>
                        
                        <div id="listaVentas" class="mb-3"></div>
                        
                        <!-- Formulario para parámetros del cronograma -->
                        <div id="formularioCronograma" class="form-group-agrupado" style="display: none;">
                            <h6 class="mb-3">Parámetros del Cronograma Agrupado</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Número de cuotas:</label>
                                    <div class="input-group">
                                        <input type="number" id="numeroCuotas" class="form-control" min="1" max="360" readonly>
                                        <span class="input-group-text">🔒</span>
                                    </div>
                                    <small class="text-muted">Obtenido de la venta seleccionada</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Fecha primer pago:</label>
                                    <div class="input-group">
                                        <input type="date" id="fechaPrimerPago" class="form-control" readonly>
                                        <span class="input-group-text">🔒</span>
                                    </div>
                                    <small class="text-muted">Obtenido de la venta seleccionada</small>
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">Tasa de interés anual (TEA):</label>
                                    <div class="input-group">
                                        <input type="number" id="tasaInteres" class="form-control" step="0.01" min="0" max="100" readonly>
                                        <span class="input-group-text">%</span>
                                        <span class="input-group-text">🔒</span>
                                    </div>
                                    <small class="text-muted">Obtenido de la venta seleccionada</small>
                                </div>
                            </div>
                        </div>
                            
                            <!-- Resumen -->
                            <div id="resumenCronograma" class="resumen-total mt-3" style="display: none;">
                                <h6>Resumen del Cronograma Agrupado</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small>Total a financiar:</small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small id="totalFinanciar">S/ 0.00</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small>N° de cuotas:</small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small id="cuotasResumen">0</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small>Cuota estimada:</small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small id="cuotaEstimada">S/ 0.00</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botón para generar -->
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-success btn-lg" id="btnGenerarCronograma" onclick="generarCronograma()">
                                    📅 Generar Cronograma Agrupado
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let clienteSeleccionado = null;
    let ventasSeleccionadas = new Map();

    // Seleccionar cliente
    document.querySelectorAll('.cliente-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const clienteId = this.dataset.clienteId;
            const clienteNombre = this.dataset.clienteNombre;
            
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
        
        ventasSeleccionadas.clear();
        
        fetch(`/cronogramas-agrupados/cliente/${clienteId}/ventas`)
            .then(r => r.json())
            .then(ventas => {
                mostrarVentas(ventas);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('listaVentas').innerHTML = '<div class="alert alert-danger">Error al cargar las ventas</div>';
            });
    }

    // Mostrar ventas
    function mostrarVentas(ventas) {
        let html = '';
        
        if (ventas.length === 0) {
            html = '<div class="alert alert-info">No hay ventas pendientes de cronograma para este cliente.</div>';
            document.getElementById('listaVentas').innerHTML = html;
            document.getElementById('formularioCronograma').style.display = 'none';
            return;
        }
        
        ventas.forEach(venta => {
            html += `
                <div class="venta-item">
                    <div class="venta-header">
                        <div class="form-check">
                            <input class="form-check-input venta-checkbox" 
                                type="checkbox" 
                                value="${venta.id}"
                                data-monto="${venta.saldo_pendiente}"
                                data-cuota="${venta.cuota_actual}"
                                data-numero-cuotas="${venta.numero_cuotas}"
                                data-tasa-interes="${venta.tasa_interes}"
                                data-fecha-pago="${venta.fecha_primer_pago}"
                                onchange="toggleVenta(this)">
                            <label class="form-check-label w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Venta #${venta.id} - Lote: ${venta.lote_codigo}</strong>
                                        <br>
                                        <small class="text-muted">${venta.lote_nombre}</small>
                                        <br>
                                        <small class="text-muted">
                                            Cuotas: ${venta.numero_cuotas} | 
                                            TEA: ${(venta.tasa_interes * 100).toFixed(2)}% | 
                                            Inicio: ${venta.fecha_primer_pago}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div><small>Total: S/ ${parseFloat(venta.precio_total).toFixed(2)}</small></div>
                                        <div><small>Inicial: S/ ${parseFloat(venta.inicial).toFixed(2)}</small></div>
                                        <div><strong><small>Pendiente: S/ ${parseFloat(venta.saldo_pendiente).toFixed(2)}</small></strong></div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('listaVentas').innerHTML = html;
    }

    // Toggle selección de venta
    function toggleVenta(checkbox) {
        const ventaId = checkbox.value;
        
        if (checkbox.checked) {
            ventasSeleccionadas.set(ventaId, {
                id: ventaId,
                monto: parseFloat(checkbox.dataset.monto),
                cuota_actual: parseFloat(checkbox.dataset.cuota),
                numero_cuotas: parseInt(checkbox.dataset.numeroCuotas),
                tasa_interes: parseFloat(checkbox.dataset.tasaInteres),
                fecha_primer_pago: checkbox.dataset.fechaPago
            });
        } else {
            ventasSeleccionadas.delete(ventaId);
        }
        
        actualizarResumen();
    }

    // Actualizar resumen
    function actualizarResumen() {
        const total = Array.from(ventasSeleccionadas.values()).reduce((sum, venta) => sum + venta.monto, 0);
        const count = ventasSeleccionadas.size;
        
        if (count > 0) {
            // Actualizar formulario con datos de la primera venta
            actualizarFormulario();
            
            // Mostrar resumen
            document.getElementById('resumenCronograma').style.display = 'block';
            document.getElementById('totalFinanciar').textContent = `S/ ${total.toFixed(2)}`;
            document.getElementById('cuotasResumen').textContent = count;
            
            // Calcular cuota estimada
            calcularCuotaEstimada();
        } else {
            document.getElementById('formularioCronograma').style.display = 'none';
            document.getElementById('resumenCronograma').style.display = 'none';
        }
    }

    // Calcular cuota estimada
    function calcularCuotaEstimada() {
        const total = Array.from(ventasSeleccionadas.values()).reduce((sum, venta) => sum + venta.monto, 0);
        const nCuotas = parseInt(document.getElementById('numeroCuotas').value) || 1;
        const tasa = parseFloat(document.getElementById('tasaInteres').value) / 100 || 0;
        
        let cuota = 0;
        if (total > 0 && nCuotas > 0) {
            if (tasa > 0) {
                const tem = Math.pow(1 + tasa, 1/12) - 1;
                cuota = (total * tem * Math.pow(1 + tem, nCuotas)) / (Math.pow(1 + tem, nCuotas) - 1);
            } else {
                cuota = total / nCuotas;
            }
            cuota = Math.ceil(cuota); // Redondear hacia arriba
        }
        
        document.getElementById('cuotaEstimada').textContent = `S/ ${cuota.toFixed(2)}`;
    }


    // Generar cronograma agrupado
    function generarCronograma() {
        if (ventasSeleccionadas.size === 0) {
            alert('Seleccione al menos una venta para generar el cronograma.');
            return;
        }
        
        const ventasArray = Array.from(ventasSeleccionadas.values()).map(v => ({ id: v.id }));
        const numeroCuotas = document.getElementById('numeroCuotas').value;
        const fechaPrimerPago = document.getElementById('fechaPrimerPago').value;
        const tasaInteres = document.getElementById('tasaInteres').value;
        
        if (!confirm(`¿Generar cronograma agrupado para ${ventasSeleccionadas.size} ventas con ${numeroCuotas} cuotas?`)) {
            return;
        }
        
        const btn = document.getElementById('btnGenerarCronograma');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Generando...';
        
        fetch('/cronogramas-agrupados/generar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                cliente_id: clienteSeleccionado,
                ventas: ventasArray,
                numero_cuotas: numeroCuotas,
                fecha_primer_pago: fechaPrimerPago,
                tasa_interes: tasaInteres / 100
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                // Abrir el cronograma agrupado por su grupo_id
                window.open(`/cronogramas-agrupados/grupo/${data.grupo_id}`, '_blank');
                // Recargar la página para actualizar
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al generar el cronograma');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
    // Actualizar formulario con datos de la primera venta seleccionada
    function actualizarFormulario() {
        if (ventasSeleccionadas.size === 0) {
            // Si no hay ventas seleccionadas, ocultar formulario
            document.getElementById('formularioCronograma').style.display = 'none';
            return;
        }
        
        // Obtener la primera venta seleccionada
        const primeraVenta = Array.from(ventasSeleccionadas.values())[0];
        
        // Rellenar los campos con los datos de la primera venta
        document.getElementById('numeroCuotas').value = primeraVenta.numero_cuotas || '';
        document.getElementById('fechaPrimerPago').value = primeraVenta.fecha_primer_pago || '';
        document.getElementById('tasaInteres').value = (primeraVenta.tasa_interes * 100) || 0;
        
        // Mostrar formulario
        document.getElementById('formularioCronograma').style.display = 'block';
    }
</script>
@endsection