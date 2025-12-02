@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📄 Reimprimir Cronogramas Agrupados</h2>
        <a href="{{ route('cronogramas-agrupados.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Volver a Generar Cronogramas
        </a>
    </div>

    <!-- 🔍 Buscador de Cliente -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🔍 Buscar Cliente con Cronogramas</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('cronogramas-agrupados.impresiones') }}">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Ingrese DNI/RUC o nombre del cliente..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            🔍 Buscar Cliente
                        </button>
                        @if(request('search'))
                            <a href="{{ route('cronogramas-agrupados.impresiones') }}" class="btn btn-outline-danger">
                                ✕ Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('search'))
    <div class="alert alert-info mb-4">
        <strong>Búsqueda:</strong> "{{ request('search') }}"
        <a href="{{ route('cronogramas-agrupados.impresiones') }}" class="float-end text-decoration-none">✖️ Quitar filtro</a>
    </div>
    @endif

    <!-- Lista de Clientes -->
    @if($clientes->count() > 0)
        <div class="row">
            @foreach($clientes as $cliente)
                @php
                    // Obtener grupos únicos de cronogramas para este cliente
                    $grupos = $cliente->gruposCronogramas();
                @endphp
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">👤 {{ $cliente->nombre_cliente }}</h6>
                            <span class="badge bg-light text-dark">
                                {{ $grupos->count() }} grupo(s)
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>DNI/RUC:</strong><br>
                                    {{ $cliente->dni_ruc }}
                                </div>
                                <div class="col-6">
                                    <strong>Teléfono:</strong><br>
                                    {{ $cliente->telefono ?? 'N/A' }}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Dirección:</strong><br>
                                {{ $cliente->direccion ?? 'N/A' }}
                            </div>
                            
                            @if($grupos->count() > 0)
                                <button type="button" 
                                        class="btn btn-outline-primary btn-sm w-100"
                                        onclick="verCronogramasCliente({{ $cliente->id }}, '{{ $cliente->nombre_cliente }}')">
                                    📋 Ver Cronogramas ({{ $grupos->count() }})
                                </button>
                            @else
                                <div class="alert alert-warning text-center py-2">
                                    <small>No tiene cronogramas agrupados</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            {{ $clientes->links() }}
        </div>
        
    @else
        <div class="alert alert-warning text-center">
            @if(request('search'))
                No se encontraron clientes con cronogramas agrupados para "{{ request('search') }}"
            @else
                No hay clientes con cronogramas agrupados registrados.
            @endif
        </div>
    @endif
</div>

<!-- Modal para ver cronogramas del cliente -->
<div class="modal fade" id="modalCronogramasCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalCronogramasTitle">Cronogramas del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="listaCronogramas" class="text-center">
                    <div class="spinner-border"></div> Cargando cronogramas...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let clienteActualId = null;
    let clienteActualNombre = '';

    function verCronogramasCliente(clienteId, clienteNombre) {
        clienteActualId = clienteId;
        clienteActualNombre = clienteNombre;
        
        $('#modalCronogramasTitle').text(`Cronogramas de ${clienteNombre}`);
        $('#listaCronogramas').html('<div class="text-center"><div class="spinner-border"></div> Cargando cronogramas...</div>');
        
        fetch(`/cronogramas-agrupados/cliente/${clienteId}/grupos`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    mostrarCronogramas(data.data);
                } else {
                    $('#listaCronogramas').html(`
                        <div class="alert alert-info">
                            Este cliente no tiene cronogramas agrupados registrados.
                        </div>
                    `);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                $('#listaCronogramas').html(`
                    <div class="alert alert-danger">
                        Error al cargar los cronogramas.
                    </div>
                `);
            });
        
        new bootstrap.Modal(document.getElementById('modalCronogramasCliente')).show();
    }

    function mostrarCronogramas(cronogramas) {
        let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha Generación</th>
                            <th>N° Cuotas</th>
                            <th>Cuota Mensual</th>
                            <th>Total Financiado</th>
                            <th>Lotes</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        cronogramas.forEach(crono => {
            // Determinar color del badge según estado
            let badgeColor = 'warning';
            let estadoText = 'PENDIENTE';
            
            if (crono.estado === 'pagado') {
                badgeColor = 'success';
                estadoText = 'PAGADO';
            } else if (crono.estado === 'vencido') {
                badgeColor = 'danger';
                estadoText = 'VENCIDO';
            } else if (crono.estado === 'parcial') {
                badgeColor = 'info';
                estadoText = 'PARCIAL';
            }
            
            html += `
                <tr>
                    <td>${crono.fecha_generacion}</td>
                    <td>${crono.nro_cuotas}</td>
                    <td>S/ ${parseFloat(crono.cuota_mensual_total).toFixed(2)}</td>
                    <td>S/ ${parseFloat(crono.total_financiado).toFixed(2)}</td>
                    <td>${crono.lotes}</td>
                    <td>
                        <span class="badge bg-${badgeColor}">${estadoText}</span>
                    </td>
                    <td>
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm"
                                onclick="reimprimirCronograma('${crono.grupo_id}')">
                            🖨️ Ver/Imprimir
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        $('#listaCronogramas').html(html);
    }

    function reimprimirCronograma(grupoId) {
        // Cerrar el modal primero
        bootstrap.Modal.getInstance(document.getElementById('modalCronogramasCliente')).hide();
        
        // Abrir en nueva pestaña la vista del cronograma
        const url = `/cronogramas-agrupados/grupo/${grupoId}`;
        window.open(url, '_blank');
    }
</script>
@endsection