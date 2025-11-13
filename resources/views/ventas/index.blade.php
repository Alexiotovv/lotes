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
                <td>{{ $v->fecha_pago }}</td>
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
                    @endif

                    @if($v->cronograma_generado)
                        <a href="{{ route('ventas.cronograma', $v) }}" target="_blank" class="btn btn-outline-info btn-sm">
                            🖨️ Cronograma
                        </a>
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
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-cambiar-estado" 
                                data-id="{{ $v->id }}"
                                data-cliente="{{ $v->cliente->nombre_cliente }}"
                                data-lote="{{ $v->lote->codigo }}"
                                data-estado="{{ $v->estado }}">
                            🔄 Estado
                        </button>
                        <form action="{{ route('ventas.destroy', $v->id) }}" method="POST" onsubmit="return confirm('¿Eliminar venta?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-light btn-sm">❌</button>
                        </form>

                        @if($v->contratos()->where('activo', true)->exists())
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarContratos({{ $v->id }})">
                                📄 Contrato
                            </button>
                        @else
                            <form action="{{ route('ventas.contrato.generar', $v) }}" method="POST" onsubmit="return confirm('¿Generar contrato?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    🖋️ Generar Contrato
                                </button>
                            </form>
                        @endif



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

@endsection

@section('scripts')
    <script>
        // Función para anular contrato (opcional)
        function anularContrato(contratoId) {
            if (!confirm('¿Está seguro de anular este contrato?')) {
                return;
            }

            fetch(`/contratos/${contratoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al anular el contrato');
                }
                return response.json();
            })
            .then(data => {
                // Recargar la lista de contratos
                mostrarContratos(data.venta_id);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al anular el contrato.');
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
                                    ${c.activo ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="anularContrato(${c.id})">Anular</button>` : ''}
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