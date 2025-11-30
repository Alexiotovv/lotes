@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📄 Generar Contratos Agrupados</h2>
        <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary btn-sm">← Volver a Ventas</a>
    </div>

    <!-- Buscador de Cliente -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🔍 Buscar Cliente</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" id="searchCliente" class="form-control" 
                           placeholder="Ingrese DNI/RUC o nombre del cliente...">
                </div>
                <div class="col-md-4">
                    <button id="btnBuscar" class="btn btn-primary w-100">
                        🔍 Buscar Cliente
                    </button>
                </div>
            </div>
            <div id="resultadosBusqueda" class="mt-3"></div>
        </div>
    </div>

    <!-- Información del Cliente Seleccionado -->
    <div id="clienteInfo" class="card mb-4" style="display: none;">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">👤 Cliente Seleccionado</h5>
        </div>
        <div class="card-body">
            <div id="clienteDetalles"></div>
        </div>
    </div>

    <!-- 🔥 NUEVA SECCIÓN: CONTRATOS EXISTENTES -->
    <div id="contratosExistentesSection" class="card mb-4" style="display: none;">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">📋 Contratos Existentes</h5>
        </div>
        <div class="card-body">
            <div id="listaContratosExistentes"></div>
        </div>
    </div>

    <!-- Lista de Ventas del Cliente -->
    <div id="ventasSection" class="card mb-4" style="display: none;">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">📋 Ventas del Cliente</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button id="selectAll" class="btn btn-outline-primary btn-sm">✓ Seleccionar Todos</button>
                    <button id="deselectAll" class="btn btn-outline-secondary btn-sm">✗ Deseleccionar Todos</button>
                </div>
                <span id="contadorSeleccionados" class="badge bg-primary">0 seleccionados</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>Lote</th>
                            <th>Área (m²)</th>
                            <th>Precio Total (S/)</th>
                            <th>Método Pago</th>
                            <th>Cuotas</th>
                            <th>Cuota Mensual (S/)</th>
                            <th>Inicial (S/)</th>
                            <th>Fecha Pago</th>
                        </tr>
                    </thead>
                    <tbody id="tablaVentas"></tbody>
                </table>
            </div>

            <div class="mt-3 text-end">
                <button id="btnGenerarContrato" class="btn btn-success" disabled>
                    🖋️ Generar Contrato Agrupado
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let clienteSeleccionado = null;
let ventasSeleccionadas = new Set();

$(document).ready(function() {
    // Buscar cliente
    $('#btnBuscar').click(buscarCliente);
    $('#searchCliente').on('keypress', function(e) {
        if (e.which === 13) buscarCliente();
    });

    // Seleccionar/deseleccionar todos
    $('#checkAll').change(function() {
        const isChecked = $(this).is(':checked');
        $('.venta-checkbox').prop('checked', isChecked);
        actualizarSeleccion();
    });

    $('#selectAll').click(function() {
        $('.venta-checkbox').prop('checked', true);
        actualizarSeleccion();
    });

    $('#deselectAll').click(function() {
        $('.venta-checkbox').prop('checked', false);
        actualizarSeleccion();
    });
});

function buscarCliente() {
    const searchTerm = $('#searchCliente').val().trim();
    
    if (searchTerm.length < 3) {
        alert('Por favor ingrese al menos 3 caracteres para buscar.');
        return;
    }

    $('#resultadosBusqueda').html('<div class="text-center"><div class="spinner-border"></div> Buscando...</div>');

    console.log('🔍 Buscando cliente:', searchTerm);

    $.ajax({
        url: '{{ route("contratos.agrupados.buscar-cliente") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            search: searchTerm
        },
        success: function(response) {
            console.log('✅ Respuesta del servidor:', response);
            
            if (response && response.success && response.data) {
                const clientes = response.data;

                if (clientes.length === 0) {
                    $('#resultadosBusqueda').html('<div class="alert alert-warning">No se encontraron clientes con ventas activas.</div>');
                    return;
                }

                let html = '<div class="list-group">';
                clientes.forEach(cliente => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action" 
                        onclick="seleccionarCliente(
                            ${cliente.id}, 
                            '${cliente.nombre_cliente.replace(/'/g, "\\'")}', 
                            '${cliente.dni_ruc}', 
                            '${cliente.telefono ? cliente.telefono.replace(/'/g, "\\'") : 'N/A'}', 
                            '${cliente.direccion ? cliente.direccion.replace(/'/g, "\\'") : 'N/A'}', 
                            '${cliente.departamento ? cliente.departamento.replace(/'/g, "\\'") : 'N/A'}'
                        )">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${cliente.nombre_cliente}</h6>
                                <small>${cliente.dni_ruc}</small>
                            </div>
                            <p class="mb-1">Ventas activas: <span class="badge bg-primary">${cliente.ventas_count}</span></p>
                            <small>ID: ${cliente.id}</small>
                        </a>
                    `;
                });
                html += '</div>';

                $('#resultadosBusqueda').html(html);
            } else {
                console.log('❌ Respuesta no tiene la estructura esperada');
                $('#resultadosBusqueda').html(`
                    <div class="alert alert-warning">
                        Respuesta inesperada del servidor.<br>
                        ${response && response.message ? response.message : 'Intente nuevamente.'}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error en AJAX:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusText: xhr.statusText
            });
            
            let errorMessage = 'Error al buscar clientes. ';
            
            if (xhr.status === 422) {
                errorMessage += 'Error de validación.';
            } else if (xhr.status === 500) {
                errorMessage += 'Error interno del servidor.';
            } else if (xhr.status === 404) {
                errorMessage += 'Ruta no encontrada.';
            }
            
            $('#resultadosBusqueda').html(`<div class="alert alert-danger">${errorMessage}</div>`);
        }
    });
}

function seleccionarCliente(clienteId, nombre, dni, telefono, direccion, departamento) {
    console.log('👤 Cliente seleccionado:', clienteId);
    
    // Mostrar información del cliente
    mostrarInformacionCliente(clienteId, nombre, dni, telefono, direccion, departamento);
    
    // Cargar contratos existentes
    cargarContratosExistentes(clienteId);
    
    // Cargar ventas disponibles
    cargarVentasCliente(clienteId);
}

function cargarContratosExistentes(clienteId) {
    $.ajax({
        url: `/contratos-agrupados/contratos-cliente/${clienteId}`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.data && response.data.length > 0) {
                mostrarContratosExistentes(response.data, clienteId);
            } else {
                $('#contratosExistentesSection').hide();
            }
        },
        error: function() {
            $('#contratosExistentesSection').hide();
        }
    });
}

function cargarVentasCliente(clienteId) {
    $('#tablaVentas').html('<tr><td colspan="9" class="text-center"><div class="spinner-border"></div> Cargando ventas...</td></tr>');

    $.ajax({
        url: `/contratos-agrupados/ventas-cliente/${clienteId}`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const ventas = response.data;
                clienteSeleccionado = clienteId;
                ventasSeleccionadas.clear();
                
                if (ventas.length === 0) {
                    $('#tablaVentas').html('<tr><td colspan="9" class="text-center text-muted">No hay ventas disponibles.</td></tr>');
                    $('#ventasSection').hide();
                    return;
                }

                mostrarTablaVentas(ventas);
                $('#ventasSection').show();
                actualizarContador();
            }
        },
        error: function() {
            $('#tablaVentas').html('<tr><td colspan="9" class="text-center text-danger">Error al cargar ventas.</td></tr>');
        }
    });
}

function mostrarContratosExistentes(contratos, clienteId) {
    let html = `
        <div class="alert alert-info">
            <strong>📄 Contratos Agrupados existentes:</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-warning">
                    <tr>
                        <th>Fecha</th>
                        <th>Lotes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    contratos.forEach(contrato => {
        html += `
            <tr>
                <td>${contrato.fecha}</td>
                <td>${contrato.lotes}</td>
                <td>
                    <button class="btn btn-outline-primary btn-sm" 
                            onclick="reimprimirContrato(${clienteId}, '${contrato.ventas_ids}')">
                        🖨️ Reimprimir
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    $('#listaContratosExistentes').html(html);
    $('#contratosExistentesSection').show();
}

function reimprimirContrato(clienteId, ventasIds) {
    const url = `/contratos-agrupados/vista-previa?cliente_id=${clienteId}&ventas=${ventasIds}`;
    window.open(url, '_blank');
}



function verContrato(clienteId, ventasIds) {
    const url = `/contratos-agrupados/vista-previa?cliente_id=${clienteId}&ventas=${ventasIds}`;
    window.open(url, '_blank');
}

// Función para mostrar información del cliente con datos ya disponibles
function mostrarInformacionCliente(clienteId, nombre, dni, telefono, direccion, departamento) {
    $('#clienteDetalles').html(`
        <div class="row">
            <div class="col-md-6">
                <strong>Nombre:</strong> ${nombre || 'N/A'}<br>
                <strong>DNI/RUC:</strong> ${dni || 'N/A'}<br>
                <strong>Teléfono:</strong> ${telefono || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Dirección:</strong> ${direccion || 'N/A'}<br>
                <strong>Departamento:</strong> ${departamento || 'N/A'}<br>
                
            </div>
        </div>
    `);
    $('#clienteInfo').show();
}

// Función separada para mostrar la tabla de ventas
function mostrarTablaVentas(ventas) {
    let tablaHTML = '';
    
    ventas.forEach(venta => {
        // Validar que los datos existan
        const loteCodigo = venta.lote_codigo || 'N/A';
        const loteNombre = venta.lote_nombre || 'Sin nombre';
        const areaM2 = parseFloat(venta.area_m2 || 0).toFixed(2);
        const precioTotal = parseFloat(venta.precio_total || 0).toFixed(2);
        const metodoPago = venta.metodo_pago || 'N/A';
        const numeroCuotas = venta.numero_cuotas || 0;
        const cuota = parseFloat(venta.cuota || 0).toFixed(2);
        const inicial = parseFloat(venta.inicial || 0).toFixed(2);
        const fechaPago = venta.fecha_pago || 'N/A';

        tablaHTML += `
            <tr>
                <td>
                    <input type="checkbox" class="venta-checkbox" value="${venta.id}" 
                           onchange="actualizarSeleccion()">
                </td>
                <td>${loteCodigo} - ${loteNombre}</td>
                <td>${areaM2}</td>
                <td>S/ ${precioTotal}</td>
                <td>${metodoPago}</td>
                <td>${numeroCuotas}</td>
                <td>S/ ${cuota}</td>
                <td>S/ ${inicial}</td>
                <td>${fechaPago}</td>
            </tr>
        `;
    });

    $('#tablaVentas').html(tablaHTML);
}

function actualizarSeleccion() {
    ventasSeleccionadas.clear();
    $('.venta-checkbox:checked').each(function() {
        ventasSeleccionadas.add(parseInt($(this).val()));
    });
    
    actualizarContador();
    
    // Habilitar/deshabilitar botón de generar contrato
    $('#btnGenerarContrato').prop('disabled', ventasSeleccionadas.size === 0);
}

function actualizarContador() {
    const count = ventasSeleccionadas.size;
    $('#contadorSeleccionados').text(`${count} seleccionado(s)`);
    $('#checkAll').prop('checked', count === $('.venta-checkbox').length);
}

$('#btnGenerarContrato').click(function() {
    if (ventasSeleccionadas.size === 0) {
        alert('Por favor seleccione al menos una venta.');
        return;
    }

    if (!confirm(`¿Generar contrato agrupado para ${ventasSeleccionadas.size} lote(s)?`)) {
        return;
    }

    const ventasArray = Array.from(ventasSeleccionadas);

    $.ajax({
        url: '{{ route("contratos.agrupados.generar") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            cliente_id: clienteSeleccionado,
            ventas_seleccionadas: ventasArray
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                // Abrir el contrato en nueva pestaña
                window.open(response.url, '_blank');
                // Recargar la página para actualizar
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error al generar el contrato.';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {}
            alert(errorMessage);
        }
    });
});
</script>
@endsection