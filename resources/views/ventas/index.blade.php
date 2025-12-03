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
                <h5 class="modal-title">Contratos de la Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="listaContratos"></div>
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
@endsection

@section('scripts')
    <script>
        // ✅ Función para mostrar cronograma en modal
        function mostrarCronograma(ventaId) {
            const contenedor = document.getElementById('detalleCronograma');
            contenedor.innerHTML = '<div class="text-center"><div class="spinner-border"></div> Cargando...</div>';

            // ✅ Actualizar enlace de impresión
            document.getElementById('btnImprimirCronograma').href = `/ventas/${ventaId}/cronograma`;

            fetch(`/ventas/${ventaId}/cronograma-detalle`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ventaIdCronograma').textContent = ventaId;

                    if (data.cronogramas.length === 0) {
                        contenedor.innerHTML = '<div class="alert alert-info">No hay cronogramas registrados para esta venta.</div>';
                        document.getElementById('btnEliminarCronograma').style.display = 'none';
                        return;
                    }

                    // Agrupar datos del cronograma
                    const totalCuotas = data.cronogramas.length;
                    const fechaInicio = data.cronogramas[0]?.fecha_pago || 'N/A';
                    const tienePagos = data.tiene_pagos;

                    let html = `
                        <div class="alert alert-info">
                            <strong>Resumen del Cronograma:</strong><br>
                            • Total de Cuotas: ${totalCuotas}<br>
                            • Fecha de Inicio: ${fechaInicio}<br>
                            • Venta ID: ${ventaId}
                        </div>
                    `;

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
            fetch(`/contratos?venta_id=${ventaId}`)
                .then(response => response.json())
                .then(contratos => {
                    const contenedor = document.getElementById('listaContratos');
                    contenedor.innerHTML = '';

                    if (contratos.length === 0) {
                        contenedor.innerHTML = '<div class="alert alert-info">No se han generado contratos para esta venta.</div>';
                        return;
                    }

                    let html = '<div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>ID</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';
                    contratos.forEach(c => {
                        const estado = c.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Anulado</span>';
                        html += `
                            <tr>
                                <td>${c.id}</td>
                                <td>${new Date(c.created_at).toLocaleString()}</td>
                                <td>${estado}</td>
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

                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('modalContratos'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los contratos.');
                });
        }


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