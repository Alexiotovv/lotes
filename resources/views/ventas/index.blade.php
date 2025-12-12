@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Listado de Ventas</h3>
    <a href="{{ route('ventas.create') }}" class="btn btn-light btn-sm">➕ Nueva Venta</a>
</div>

<!-- 🔍 Barra de búsqueda -->
<form method="GET" action="{{ route('ventas.index') }}" class="mb-4">
    <div class="input-group">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            placeholder="Buscar por: ID venta, nombre cliente, código/nombre lote o fecha registro (YYYY-MM-DD)"
            value="{{ request('search') }}"
        >
        <button class="btn btn-outline-secondary" type="submit">🔍 Buscar</button>
        @if(request('search'))
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-danger">✕ Limpiar</a>
        @endif
    </div>
</form>
@if(request('search'))
    <div class="alert alert-info mb-4">
        <strong>Búsqueda:</strong> "{{ request('search') }}"
        <a href="{{ route('ventas.index') }}" class="float-end text-decoration-none">✖️ Quitar filtro</a>
    </div>
@endif
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Id</th>
                <th>DNI/RUC</th>
                <th>Cliente</th>
                <th>Cod.Lote</th>
                <th>Tipo de Venta</th>
                <th>Fecha Pago</th>
                <th>Fecha Registro</th>
                <th>Inicial (S/)</th>
                <th>Interés (%)</th>
                <th>Cuota (S/)</th>
                <th>Total (S/)</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $v)
                {{-- Verificar si el cronograma está agrupado --}}
                @php
                    // Obtener si el cronograma está agrupado
                    $cronogramas = $v->cronogramas;
                    $esAgrupado = false;
                    $grupoId = null;
                    
                    if ($cronogramas->isNotEmpty()) {
                        foreach ($cronogramas as $crono) {
                            if (!empty($crono->grupo_id)) {
                                $esAgrupado = true;
                                $grupoId = $crono->grupo_id;
                                break;
                            }
                        }
                    }
                @endphp
            <tr>
                <td>{{ $v->id }}</td>
                <td>{{ $v->cliente->dni_ruc }}</td>
                <td>{{ $v->cliente->nombre_cliente }}</td>
                <td>{{$v->lote->codigo}}</td>
                <td>{{ $v->metodopago->nombre }}</td>
                <td>{{ $v->fecha_pago->format('Y-m-d') }}</td>
                <td>{{ $v->created_at }}</td>
                <td>{{ number_format($v->inicial, 2) }}</td>
                <td>{{ $v->tasa_interes * 100 }}</td>
                <td>S/ {{ number_format($v->cuota, 2) }}</td>
                <td>S/ {{ number_format($v->lote?->area_m2 * $v->lote?->precio_m2, 2, '.', ',') }}</td>
                <td>
                    @switch($v->estado)
                        @case('finalizado')
                        @case('contado')
                            <span class="badge bg-secondary text-white">{{$v->estado}}</span>
                            @break
                        @case('vigente')
                            <span class="badge bg-success text-white">{{$v->estado}}</span>
                            @break
                        @default
                            <span class="badge bg-warning text-dark">{{$v->estado}}</span>
                    @endswitch
                </td>
                <td>
                    
                    @if(!$v->cronograma_generado)
                        <a href="{{ route('ventas.edit', $v) }}" class="btn btn-outline-primary btn-sm">✏️ Editar</a>
                        <br>
                    @endif

                @if($v->cronograma_generado)
                    @if($esAgrupado)
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="" disabled>
                            📋 C.Agrupado
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="mostrarCronograma({{ $v->id }})">
                            📋 Ver Cronograma
                        </button>
                    @endif
                @elseif($v->metodopago && $v->metodopago->es_credito)
                    {{-- ✅ Solo mostrar "Generar Cronograma" si es venta al crédito --}}
                    <form action="{{ route('ventas.generar-cronograma', $v) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de generar el cronograma? Esta acción no se puede deshacer.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            📊 Generar Cronograma
                        </button>
                    </form>
                @endif

                    @if(Auth::user()->is_admin || Auth::user()->role === 'admin')
                    <br>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-cambiar-estado" 
                                data-id="{{ $v->id }}"
                                data-cliente="{{ $v->cliente->nombre_cliente }}"
                                data-lote="{{ $v->lote->codigo }}"
                                data-estado="{{ $v->estado }}">
                            🔄 Estado
                        </button>
                        <br>
                        

                        @if($v->contratos()->where('activo', true)->exists())
                            @if($esAgrupado)
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="" disabled>
                                    📄 C. Agrupado
                                </button>
                            @else

                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarContratos({{ $v->id }})">
                                    📄 Contrato
                                </button>
                            @endif
                            <br>
                        @else
                            <form action="{{ route('ventas.contrato.generar', $v) }}" method="POST" onsubmit="return confirm('¿Generar contrato?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    🖋️ Generar Contrato
                                </button>
                                <br>
                            </form>
                        @endif

                        <form action="{{ route('ventas.destroy', $v->id) }}" method="POST" onsubmit="return confirm('¿Eliminar venta?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <br>
                            <button class="btn btn-light btn-sm">❌</button>
                        </form>

                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- Enlaces de paginación -->
<div class="d-flex justify-content-end mt-3">
    {{ $ventas->links() }}
</div>


<!-- Modal único para cambiar estado -->
<div class="modal fade" id="modalCambioEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Cambiar Estado de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p><strong>ID:</strong> <span id="ventaIdModal"></span></p>
                <p><strong>Cliente:</strong> <span id="clienteModal"></span></p>
                <p><strong>Lote:</strong> <span id="loteModal"></span></p>
                <hr>
                <form id="formCambioEstado" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <!-- Vigente -->
                    <div class="mb-2">
                        <label class="d-block mb-1">🟢 Vigente</label>
                        <input type="radio" name="estado" value="vigente" 
                               class="btn-check"
                               id="vigente_modal"
                               autocomplete="off">
                        <label class="btn btn-outline-success btn-sm w-100" for="vigente_modal">
                            Activar Vigencia
                        </label>
                    </div>

                    <!-- Finalizado -->
                    <div class="mb-2">
                        <label class="d-block mb-1">⚪ Finalizado</label>
                        <input type="radio" name="estado" value="finalizado" 
                               class="btn-check"
                               id="finalizado_modal"
                               autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm w-100" for="finalizado_modal">
                            Marcar como Finalizado
                        </label>
                    </div>

                    <!-- Desistido -->
                    <div class="mb-2">
                        <label class="d-block mb-1">🔴 Desistido</label>
                        <input type="radio" name="estado" value="desistido" 
                               class="btn-check"
                               id="desistido_modal"
                               autocomplete="off">
                        <label class="btn btn-outline-danger btn-sm w-100" for="desistido_modal">
                            Marcar como Desistido
                        </label>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-sm w-100">✅ Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar contratos -->
<div class="modal fade" id="modalContratos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contratos de la Venta #<span id="ventaIdContratos"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="listaContratos">
                    <div class="text-center">
                        <div class="spinner-border"></div> Cargando...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-info" id="btnAgregarPropietario" style="display:none;">
                    👥 Agregar Propietario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar propietarios adicionales -->
<div class="modal fade" id="modalAgregarPropietario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Agregar Propietario Adicional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarPropietario">
                    <div class="mb-3">
                        <label class="form-label">Buscar cliente por DNI/RUC o nombre:</label>
                        <div class="input-group">
                            <input type="text" id="buscarCliente" class="form-control" 
                                   placeholder="Ingrese DNI/RUC o nombre..."
                                   onkeyup="buscarClientes(this.value)">
                            <span class="input-group-text">🔍</span>
                        </div>
                        <div id="resultadosBusqueda" class="mt-2" style="max-height: 200px; overflow-y: auto; display: none;">
                            <!-- Resultados de búsqueda aparecerán aquí -->
                        </div>
                    </div>
                    
                    <div id="clienteSeleccionadoInfo" class="alert alert-success" style="display: none;">
                        <strong>Cliente seleccionado:</strong>
                        <div id="clienteInfo"></div>
                        <input type="hidden" id="cliente_id">
                    </div>
                    
                    <div class="alert alert-warning">
                        <small><i class="fas fa-info-circle"></i> El cliente será agregado como propietario adicional de esta venta.</small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-info w-100" id="btnGuardarPropietario" disabled>
                            ✅ Agregar Propietario
                        </button>
                    </div>
                </form>
                
                <!-- Lista de propietarios actuales -->
                <div id="listaPropietariosActuales" class="mt-4" style="display: none;">
                    <h6>Propietarios actuales:</h6>
                    <div id="propietariosLista"></div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal para mostrar cronograma -->
<div class="modal fade" id="modalCronograma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cronograma de la Venta #<span id="ventaIdCronograma"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalleCronograma">
                    <div class="text-center">
                        <div class="spinner-border"></div> Cargando...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning" id="btnCambiarFecha" style="display:none;">
                    📅 Cambiar Fecha Inicio
                </button>
                <a href="#" id="btnImprimirCronograma" target="_blank" class="btn btn-outline-primary">🖨️ Imprimir</a>
                <button type="button" class="btn btn-danger" 
                        id="btnEliminarCronograma" 
                        style="display:none;" 
                        data-csrf="{{ csrf_token() }}"
                        onclick="eliminarCronograma(this.getAttribute('data-venta-id'), this.getAttribute('data-csrf'))">
                    🗑️ Eliminar Cronograma
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar fecha de inicio -->
<div class="modal fade" id="modalCambiarFecha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Cambiar Fecha de Inicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCambiarFecha">
                    <div class="mb-3">
                        <label class="form-label">Nueva fecha del primer pago:</label>
                        <input type="date" id="nuevaFechaInicio" class="form-control" required>
                        <small class="text-muted">Todas las cuotas se ajustarán manteniendo el intervalo mensual.</small>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-warning w-100">📅 Actualizar Fechas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
    <script>
        // ✅ Función para mostrar cronograma en modal
        function mostrarCronograma(ventaId) {
            window.ventaIdActual = ventaId; // Guardar para usar en otras funciones
            
            const contenedor = document.getElementById('detalleCronograma');
            contenedor.innerHTML = '<div class="text-center"><div class="spinner-border"></div> Cargando...</div>';
            
            // Ocultar botones inicialmente
            document.getElementById('btnCambiarFecha').style.display = 'none';
            document.getElementById('btnEliminarCronograma').style.display = 'none';

            // ✅ Actualizar enlace de impresión
            document.getElementById('btnImprimirCronograma').href = `/ventas/${ventaId}/cronograma`;

            fetch(`/ventas/${ventaId}/cronograma-detalle`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ventaIdCronograma').textContent = ventaId;

                    if (data.cronogramas.length === 0) {
                        contenedor.innerHTML = '<div class="alert alert-info">No hay cronogramas registrados para esta venta.</div>';
                        return;
                    }

                    // Agrupar datos del cronograma
                    const totalCuotas = data.cronogramas.length;
                    const fechaInicio = data.cronogramas[0]?.fecha_pago || 'N/A';
                    const tienePagos = data.tiene_pagos;
                    const esAgrupado = data.es_agrupado;

                    let html = `
                        <div class="alert ${esAgrupado ? 'alert-warning' : 'alert-info'}">
                            <strong>${esAgrupado ? '📋 CRONOGRAMA AGRUPADO' : 'Resumen del Cronograma:'}</strong><br>
                            • Total de Cuotas: ${totalCuotas}<br>
                            • Fecha de Inicio: ${fechaInicio}<br>
                            • Venta ID: ${ventaId}
                    `;
                    
                    if (esAgrupado) {
                        html += `<br>• Grupo ID: ${data.grupo_id}<br>• <em>Este cronograma forma parte de un grupo agrupado</em>`;
                    }
                    
                    html += `</div>`;

                    // Mostrar botón de cambiar fecha solo si NO es agrupado
                    if (!esAgrupado) {
                        document.getElementById('btnCambiarFecha').style.display = 'inline-block';
                        // Asignar la fecha actual como valor por defecto
                        document.getElementById('nuevaFechaInicio').value = convertirFechaFormatoInput(fechaInicio);
                    }

                    // Tabla de cronograma
                    html += `
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha Pago</th>
                                        <th>Cuota (S/)</th>
                                        <th>Saldo (S/)</th>
                                        <th>Interés (S/)</th>
                                        <th>Amortización (S/)</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    data.cronogramas.forEach(c => {
                        html += `
                            <tr>
                                <td>${c.nro_cuota}</td>
                                <td>${c.fecha_pago}</td>
                                <td>${parseFloat(c.cuota).toFixed(2)}</td>
                                <td>${parseFloat(c.saldo).toFixed(2)}</td>
                                <td>${parseFloat(c.interes).toFixed(2)}</td>
                                <td>${parseFloat(c.amortizacion).toFixed(2)}</td>
                                <td>
                                    <span class="badge ${
                                        c.estado === 'pagado' ? 'bg-success' :
                                        c.estado === 'vencido' ? 'bg-danger' : 'bg-warning'
                                    }">${c.estado}</span>
                                </td>
                            </tr>
                        `;
                    });

                    html += '</tbody></table></div>';

                    contenedor.innerHTML = html;

                        // ✅ Mostrar botón de eliminar solo si no tiene pagos
                        const btnEliminar = document.getElementById('btnEliminarCronograma');
                        if (data.tiene_pagos) {
                            btnEliminar.style.display = 'none';
                            contenedor.innerHTML += '<div class="alert alert-warning mt-3">⚠️ Este cronograma tiene pagos registrados y no puede eliminarse.</div>';
                        } else {
                            btnEliminar.style.display = 'inline-block';
                            // ✅ Asignar atributos de datos
                            btnEliminar.setAttribute('data-venta-id', ventaId);
                            btnEliminar.setAttribute('data-csrf', '{{ csrf_token() }}');
                            // ✅ Asignar evento onclick
                            btnEliminar.onclick = () => eliminarCronograma(ventaId, '{{ csrf_token() }}');
                        }

                        // Mostrar modal
                        const modal = new bootstrap.Modal(document.getElementById('modalCronograma'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        contenedor.innerHTML = '<div class="alert alert-danger">Error al cargar el cronograma.</div>';
                    });
        }

        // Función auxiliar para convertir fecha (dd/mm/yyyy) a formato input (yyyy-mm-dd)
        function convertirFechaFormatoInput(fechaString) {
            if (!fechaString) return '';
            const parts = fechaString.split('/');
            if (parts.length === 3) {
                return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
            return fechaString;
        }



        // ✅ Función para eliminar cronograma
        let ventaIdAEliminar = null; // Variable global temporal

        function eliminarCronograma(ventaId, csrfToken) {
            if (!ventaId || !csrfToken) {
                console.error('Falta ID de venta o token CSRF');
                alert('Error: Datos incompletos para eliminar el cronograma.');
                return;
            }

            if (!confirm('¿Está seguro de eliminar este cronograma? Esta acción no se puede deshacer.')) {
                return;
            }

            fetch(`/ventas/${ventaId}/eliminar-cronograma`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // ✅ Usar el token recibido
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Cerrar modal y recargar tabla
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCronograma'));
                    modal.hide();
                    location.reload();
                } else {
                    alert(data.message || 'Error desconocido.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Error al eliminar el cronograma.');
            });
        }


        // Función para anular contrato (opcional)
        function anularContrato(contratoId, csrfToken) {
            if (!confirm('¿Está seguro de anular este contrato?')) {
                return;
            }

            fetch(`/contratos/${contratoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // ✅ Usar el token recibido
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Error al anular el contrato'); });
                }
                return response.json();
            })
            .then(data => {
                // Recargar la lista de contratos
                mostrarContratos(data.venta_id);
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Error al anular el contrato.');
            });
        }

        function eliminarContratoPermanente(contratoId, csrfToken) {
            if (!confirm('¿Está seguro de eliminar este contrato permanentemente? Esta acción no se puede deshacer.')) {
                return;
            }

            fetch(`/contratos/${contratoId}/eliminar-permanente`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Error al eliminar el contrato'); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Recargar la lista de contratos
                    mostrarContratos(data.venta_id);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Error al eliminar el contrato.');
            });
        }


        // Función para mostrar contratos
        function mostrarContratos(ventaId) {
            window.ventaContratoActual = ventaId; // Guardar para usar en otras funciones
            document.getElementById('ventaIdContratos').textContent = ventaId;
            
            fetch(`/contratos?venta_id=${ventaId}`)
                .then(response => response.json())
                .then(contratos => {
                    const contenedor = document.getElementById('listaContratos');
                    contenedor.innerHTML = '';

                    if (contratos.length === 0) {
                        contenedor.innerHTML = '<div class="alert alert-info">No se han generado contratos para esta venta.</div>';
                        document.getElementById('btnAgregarPropietario').style.display = 'none';
                        return;
                    }

                    let html = '<div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>ID</th><th>Fecha</th><th>Estado</th><th>Propietarios</th><th>Acciones</th></tr></thead><tbody>';
                    
                    contratos.forEach(c => {
                        const estado = c.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Anulado</span>';
                        
                        // Obtener propietarios para este contrato
                        html += `
                            <tr>
                                <td>${c.id}</td>
                                <td>${new Date(c.created_at).toLocaleString()}</td>
                                <td>${estado}</td>
                                <td id="propietarios-${c.id}">
                                    <div class="spinner-border spinner-border-sm"></div>
                                </td>
                                <td>
                                    <a href="${c.url}" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                                    ${c.activo ? `<button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="anularContrato(${c.id}, '{{ csrf_token() }}')">
                                        Anular
                                    </button>` : `
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                title="Eliminar permanentemente"
                                                onclick="eliminarContratoPermanente(${c.id}, '{{ csrf_token() }}')">
                                            ✕ Eliminar
                                        </button>
                                    `}
                                </td>
                            </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    contenedor.innerHTML = html;

                    // Mostrar botón para agregar propietario
                    document.getElementById('btnAgregarPropietario').style.display = 'inline-block';
                    
                    // Cargar propietarios para cada contrato
                    contratos.forEach(c => {
                        cargarPropietariosContrato(c.id, ventaId);
                    });

                    // Cargar propietarios actuales para el modal
                    cargarPropietariosActuales(ventaId);

                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('modalContratos'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los contratos.');
                });
        }


        // Función para cargar propietarios de un contrato
        function cargarPropietariosContrato(contratoId, ventaId) {
            fetch(`/ventas/${ventaId}/propietarios-adicionales`)
                .then(response => response.json())
                .then(data => {
                    const celda = document.getElementById(`propietarios-${contratoId}`);
                    let html = '';
                    
                    if (data.length === 0) {
                        html = '<span class="badge bg-secondary">No hay propietarios</span>';
                    } else {
                        data.forEach(prop => {
                            const badgeColor = prop.es_titular ? 'primary' : 'info';
                            const badgeText = prop.es_titular ? 'Propietario1' : 'Propietario2';
                            
                            html += `
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <small>${prop.nombre_cliente}</small><br>
                                        <small class="text-muted">${prop.dni_ruc}</small>
                                    </div>
                                    <span class="badge bg-${badgeColor}">${badgeText}</span>
                                </div>`;
                        });
                    }
                    
                    celda.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    const celda = document.getElementById(`propietarios-${contratoId}`);
                    celda.innerHTML = '<span class="badge bg-warning">Error al cargar</span>';
                });
        }

        // Función para cargar propietarios actuales para el modal
        function cargarPropietariosActuales(ventaId) {
            fetch(`/ventas/${ventaId}/propietarios-adicionales`)
                .then(response => response.json())
                .then(data => {
                    const contenedor = document.getElementById('propietariosLista');
                    
                    if (data.length === 0) {
                        contenedor.innerHTML = '<div class="alert alert-info">No hay propietarios registrados.</div>';
                    } else {
                        let html = '<div class="list-group">';
                        data.forEach(prop => {
                            const esEliminable = !prop.es_titular; // Solo se puede eliminar propietarios adicionales
                            
                            html += `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${prop.nombre_cliente}</strong>
                                        <span class="badge ${prop.es_titular ? 'bg-primary' : 'bg-info'} ms-2">
                                            ${prop.es_titular ? 'Titular' : 'Adicional'}
                                        </span><br>
                                        <small>DNI/RUC: ${prop.dni_ruc}</small>
                                    </div>
                                    ${esEliminable ? 
                                        `<button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="eliminarPropietario(${prop.id}, '{{ csrf_token() }}')">
                                            ✕
                                        </button>` : 
                                        '<span class="badge bg-secondary">Principal</span>'
                                    }
                                </div>`;
                        });
                        html += '</div>';
                        contenedor.innerHTML = html;
                        document.getElementById('listaPropietariosActuales').style.display = 'block';
                    }
                });
        }

        // Manejar clic en botón "Cambiar Fecha Inicio"
        document.getElementById('btnCambiarFecha').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalCambiarFecha'));
            modal.show();
        });

        // Manejar envío del formulario de cambio de fecha
        document.getElementById('formCambiarFecha').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const ventaId = window.ventaIdActual;
            const nuevaFecha = document.getElementById('nuevaFechaInicio').value;
            
            if (!nuevaFecha) {
                alert('Por favor seleccione una fecha válida.');
                return;
            }
            
            if (!confirm(`¿Cambiar la fecha de inicio del cronograma a ${nuevaFecha}?\n\nTodas las fechas de las cuotas se ajustarán automáticamente en orden mensual.`)) {
                return;
            }
            
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Actualizando...';
            
            fetch(`/ventas/${ventaId}/actualizar-fecha-cronograma`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    nueva_fecha: nuevaFecha
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Cerrar ambos modales
                    bootstrap.Modal.getInstance(document.getElementById('modalCambiarFecha')).hide();
                    bootstrap.Modal.getInstance(document.getElementById('modalCronograma')).hide();
                    // Recargar la página para ver los cambios
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al actualizar las fechas: ' + (error.message || 'Error desconocido'));
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });


        // Función para buscar clientes (modificada)
        function buscarClientes(termino) {
            if (termino.length < 2) {
                document.getElementById('resultadosBusqueda').style.display = 'none';
                return;
            }
            
            fetch(`/clientes/buscar?q=${encodeURIComponent(termino)}`)
                .then(response => response.json())
                .then(clientes => {
                    const resultados = document.getElementById('resultadosBusqueda');
                    
                    if (clientes.length === 0) {
                        resultados.innerHTML = '<div class="alert alert-warning">No se encontraron clientes.</div>';
                        resultados.style.display = 'block';
                        return;
                    }
                    
                    // Filtrar para excluir al cliente titular actual
                    let html = '<div class="list-group">';
                    clientes.forEach(cliente => {
                        html += `
                            <button type="button" class="list-group-item list-group-item-action" 
                                    onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre_cliente.replace(/'/g, "\\'")}', '${cliente.dni_ruc.replace(/'/g, "\\'")}')">
                                <strong>${cliente.nombre_cliente}</strong><br>
                                <small>DNI/RUC: ${cliente.dni_ruc}</small>
                            </button>`;
                    });
                    html += '</div>';
                    
                    resultados.innerHTML = html;
                    resultados.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultados.innerHTML = '<div class="alert alert-danger">Error al buscar clientes.</div>';
                    resultados.style.display = 'block';
                });
        }

        // Función para seleccionar un cliente
        function seleccionarCliente(id, nombre, dni) {
            document.getElementById('cliente_id').value = id;
            document.getElementById('clienteInfo').innerHTML = `
                ${nombre} (DNI/RUC: ${dni})
            `;
            document.getElementById('clienteSeleccionadoInfo').style.display = 'block';
            document.getElementById('resultadosBusqueda').style.display = 'none';
            document.getElementById('btnGuardarPropietario').disabled = false;
        }

        // Manejar envío del formulario para agregar propietario
        document.getElementById('formAgregarPropietario').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const ventaId = window.ventaContratoActual;
            const clienteId = document.getElementById('cliente_id').value;
            
            if (!clienteId) {
                alert('Por favor seleccione un cliente.');
                return;
            }
            
            const btn = document.getElementById('btnGuardarPropietario');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Agregando...';
            
            fetch(`/ventas/${ventaId}/agregar-propietario`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cliente_id: clienteId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Limpiar formulario
                    document.getElementById('buscarCliente').value = '';
                    document.getElementById('clienteSeleccionadoInfo').style.display = 'none';
                    document.getElementById('btnGuardarPropietario').disabled = true;
                    
                    // Recargar listas
                    cargarPropietariosActuales(ventaId);
                    
                    // Recargar la lista de contratos
                    mostrarContratos(ventaId);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al agregar propietario.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });

        // Función para eliminar propietario
        function eliminarPropietario(propietarioId, csrfToken) {
            if (!confirm('¿Está seguro de eliminar este propietario adicional?')) {
                return;
            }
            
            fetch(`/propietarios-adicionales/${propietarioId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Recargar listas
                    cargarPropietariosActuales(window.ventaContratoActual);
                    mostrarContratos(window.ventaContratoActual);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al eliminar propietario.');
            });
        }

        // Manejar clic en botón "Agregar Propietario"
        document.getElementById('btnAgregarPropietario').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalAgregarPropietario'));
            modal.show();
        });




    </script>
    <script>
        // Manejar clic en botones de cambio de estado
        $(document).on('click', '.btn-cambiar-estado', function() {
            const id = $(this).data('id');
            const cliente = $(this).data('cliente');
            const lote = $(this).data('lote');
            const estadoActual = $(this).data('estado');
            
            // Actualizar contenido del modal
            $('#ventaIdModal').text(id);
            $('#clienteModal').text(cliente);
            $('#loteModal').text(lote);
            
            // Actualizar radio buttons según estado actual
            $('input[name="estado"]').prop('checked', false);
            $(`input[name="estado"][value="${estadoActual}"]`).prop('checked', true);
            
            // Actualizar acción del formulario
            $('#formCambioEstado').attr('action', `/ventas/${id}/cambiar-estado`);
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('modalCambioEstado'));
            modal.show();
        });
    </script>

    
@endsection