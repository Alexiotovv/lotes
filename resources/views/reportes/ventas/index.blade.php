@extends('layouts.app')

@section('content')

    
        <!-- Contenido principal -->
        <div class="col-md-12">
            <!-- Encabezado del reporte -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📋 Reporte de Ventas - {{$empresa->nombre}}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reportes.ventas') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                            </div>
                            {{-- <div class="col-md-3">
                                <label class="form-label">Filtro</label>
                                <select name="filtro" class="form-select">
                                    <option value="todos" {{ request('filtro', 'todos') == 'todos' ? 'selected' : '' }}>TODOS...</option>
                                    <option value="vendidos" {{ request('filtro') == 'vendidos' ? 'selected' : '' }}>Vendidos</option>
                                    <option value="reservados" {{ request('filtro') == 'reservados' ? 'selected' : '' }}>Reservados</option>
                                    <option value="disponibles" {{ request('filtro') == 'disponibles' ? 'selected' : '' }}>Disponibles</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-secondary w-100">🔍 Filtrar</button>
                                @if(request()->query())
                                    <a href="{{ route('reportes.ventas') }}" class="btn btn-outline-danger mt-2 w-100">✕ Limpiar</a>
                                @endif
                            </div> --}}
                        </div>
                    </form>
                </div>
            </div>

            <!-- Botones de exportación -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3">
                        <a href="#" class="btn btn-success btn-imprimir">
                            🖨️ Lista Ventas (Imprimir)
                        </a>
                        {{-- <a href="{{ route('reportes.ventas.pdf.detalle') }}" class="btn btn-success">
                            📊 Detalle Ventas PDF
                        </a> --}}
                        <a href="#" class="btn btn-success btn-consolidado">
                            📈 Consolidado PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reporte de créditos por cliente -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">💰 Reporte de Créditos por Cliente - {{$empresa->nombre}}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" id="buscarClienteDni" class="form-control" placeholder="Ingresar DNI del cliente...">
                        </div>
                        <div class="col-md-6">
                            <button id="btnCreditosCliente" class="btn btn-primary w-100">
                                🔍 Buscar Créditos por DNI
                            </button>
                        </div>
                    </div>
                    <!-- Resultado de búsqueda -->
                    <div id="resultadoBusqueda" class="mt-3" style="display:none;">
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-4 me-2"></i>
                            <div>
                                <strong id="nombreCliente"></strong> 
                                {{-- <button id="btnImprimirCliente" class="btn btn-sm btn-outline-success ms-2">
                                    🖨️ Imprimir Créditos
                                </button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Otros Reportes -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📄 Otros Reportes - {{$empresa->nombre}}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3">
                        <a href="{{ route('reportes.ventas.pdf.creditos_por_cobrar') }}" class="btn btn-warning" target="_blank">
                            📋 Lista Créditos por Cobrar
                        </a>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalSeleccionMes">
                            📅 Cuotas del Mes
                        </button>
                    </div>
                </div>
            </div>
        </div>




    <!-- Modal para imprimir créditos del cliente -->
    <div class="modal fade" id="modalCreditosCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Créditos del Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoImpresion">
                        <div class="text-center mb-4">
                            <h4>{{$empresa->nombre}}</h4>
                            <h5>Reporte de Créditos por Cliente</h5>
                            <p id="tituloCliente"></p>
                            <p>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</p>
                        </div>
                        <div id="detalleCreditos"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="imprimirModal()">🖨️ Imprimir</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para seleccionar año y mes -->
    <div class="modal fade" id="modalSeleccionMes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Mes y Año</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="anioSeleccionado" class="form-label">Año</label>
                            <select id="anioSeleccionado" class="form-select">
                                <!-- Las opciones se llenarán dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="mesSeleccionado" class="form-label">Mes</label>
                            <select id="mesSeleccionado" class="form-select">
                                <option value="01">Enero</option>
                                <option value="02">Febrero</option>
                                <option value="03">Marzo</option>
                                <option value="04">Abril</option>
                                <option value="05">Mayo</option>
                                <option value="06">Junio</option>
                                <option value="07">Julio</option>
                                <option value="08">Agosto</option>
                                <option value="09">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnGenerarReporte">Generar Reporte</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
<script>
    // ✅ Manejar impresión consolidada con fechas
    document.querySelector('.btn-consolidado').addEventListener('click', function(e) {
        e.preventDefault();
        
        const fechaDesde = document.querySelector('input[name="fecha_desde"]').value;
        const fechaHasta = document.querySelector('input[name="fecha_hasta"]').value;
        
        // Validar fechas
        if (!fechaDesde || !fechaHasta) {
            alert('⚠️ Debe seleccionar un rango de fechas completo.');
            return;
        }
        
        if (new Date(fechaDesde) > new Date(fechaHasta)) {
            alert('⚠️ La Fecha Inicio no puede ser mayor que la Fecha Fin.');
            return;
        }
        
        // Construir URL
        let url = '{{ route("reportes.ventas.pdf.consolidado") }}';
        const params = new URLSearchParams();
        params.append('fecha_desde', fechaDesde);
        params.append('fecha_hasta', fechaHasta);
        
        window.open(url + '?' + params.toString(), '_blank');
    });

    // ✅ Validar rango de fechas antes de imprimir
    document.querySelector('.btn-imprimir').addEventListener('click', function(e) {
        e.preventDefault();
        
        const fechaDesde = document.querySelector('input[name="fecha_desde"]').value;
        const fechaHasta = document.querySelector('input[name="fecha_hasta"]').value;
        
        // Validar que ambas fechas estén seleccionadas
        if (!fechaDesde || !fechaHasta) {
            alert('⚠️ Debe seleccionar un rango de fechas completo (Fecha Inicio y Fecha Fin).');
            return;
        }
        
        // Validar que la fecha de inicio no sea mayor que la de fin
        if (new Date(fechaDesde) > new Date(fechaHasta)) {
            alert('⚠️ La Fecha Inicio no puede ser mayor que la Fecha Fin.');
            return;
        }
        
        // Construir URL con parámetros
        let url = '{{ route("reportes.ventas.pdf.lista") }}';
        const params = new URLSearchParams();
        params.append('fecha_desde', fechaDesde);
        params.append('fecha_hasta', fechaHasta);
        
        window.open(url + '?' + params.toString(), '_blank');
    });




    document.addEventListener('DOMContentLoaded', function() {
        // Buscar cliente por DNI
        document.getElementById('btnCreditosCliente').addEventListener('click', () => {
            const dni = document.getElementById('buscarClienteDni').value.trim();
            if (dni) {
                fetch(`{{ route('reportes.ventas.creditos.cliente.dni') }}?dni=${encodeURIComponent(dni)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.cliente) {
                            document.getElementById('nombreCliente').textContent = `${data.cliente.nombre_cliente} (${data.cliente.dni_ruc})`;
                            document.getElementById('resultadoBusqueda').style.display = 'block';
                            
                            // Mostrar modal con los créditos
                            document.getElementById('tituloCliente').textContent = `${data.cliente.nombre_cliente} - DNI: ${data.cliente.dni_ruc}`;
                            document.getElementById('detalleCreditos').innerHTML = generarHtmlCreditos(data.creditos);
                            
                            const modal = new bootstrap.Modal(document.getElementById('modalCreditosCliente'));
                            modal.show();
                        } else {
                            alert('Cliente no encontrado.');
                            document.getElementById('resultadoBusqueda').style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al buscar el cliente.');
                    });
            } else {
                alert('Ingrese un DNI de cliente.');
            }
        });

        // Validación de fechas
        const fechaDesde = document.querySelector('input[name="fecha_desde"]');
        const fechaHasta = document.querySelector('input[name="fecha_hasta"]');

        fechaDesde.addEventListener('change', () => {
            if (fechaHasta.value && fechaDesde.value > fechaHasta.value) {
                alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
                fechaDesde.value = '';
            }
        });

        fechaHasta.addEventListener('change', () => {
            if (fechaDesde.value && fechaHasta.value < fechaDesde.value) {
                alert('La fecha de fin no puede ser menor que la fecha de inicio.');
                fechaHasta.value = '';
            }
        });
    });

    // Generar HTML de créditos para impresión
    function generarHtmlCreditos(creditos) {
        if (!creditos || creditos.length === 0) {
            return '<div class="alert alert-info">No hay créditos registrados para este cliente.</div>';
        }

        let html = '<div class="table-responsive">';
        html += '<table class="table table-bordered table-sm">';
        html += '<thead class="table-light"><tr><th>Lote</th><th>Fecha Venta</th><th>Total Venta (S/)</th><th>Cuota (S/)</th><th>N° Cuotas</th><th>Estado</th><th>Detalles</th></tr></thead>';
        html += '<tbody>';

        creditos.forEach(credito => {
            // ✅ Convertir a número antes de usar toFixed
            const totalVenta = parseFloat(credito.total_venta) || 0;
            const cuota = parseFloat(credito.cuota) || 0;
            
            html += `<tr>
                <td>${credito.lote.codigo} - ${credito.lote.nombre}</td>
                <td>${credito.fecha_pago}</td>
                <td>${totalVenta.toFixed(2)}</td>
                <td>${cuota.toFixed(2)}</td>
                <td>${credito.numero_cuotas}</td>
                <td>${credito.estado}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="mostrarDetallesCronograma(${credito.id})">Ver Detalles</button>
                </td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        return html;
    }

    // Mostrar detalles de cronograma en un submodal
    function mostrarDetallesCronograma(ventaId) {

        fetch(`/reportes/ventas/${ventaId}/detalles-credito`)
            .then(response => {
                // ✅ Verificar el estado HTTP
                if (!response.ok) {
                    // ✅ Obtener el cuerpo de la respuesta para ver el error
                    return response.text().then(text => {
                        console.error('Error HTTP:', response.status, response.statusText);
                        console.error('Cuerpo de respuesta:', text);
                        throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                let html = '<div class="table-responsive"><table class="table table-bordered table-sm">';
                html += '<thead class="table-light"><tr><th>N° Cuota</th><th>Fecha Pago</th><th>Cuota (S/)</th><th>Pagado (S/)</th><th>Saldo (S/)</th><th>Estado</th></tr></thead>';
                html += '<tbody>';

                data.cronogramas.forEach(crono => {
                    html += `<tr>
                        <td>${crono.nro_cuota}</td>
                        <td>${crono.fecha_pago}</td>
                        <td>${crono.cuota.toFixed(2)}</td>
                        <td>${crono.pagado.toFixed(2)}</td>
                        <td>${crono.saldo.toFixed(2)}</td>
                        <td>${crono.estado}</td>
                    </tr>`;
                });

                html += '</tbody></table></div>';
                
                // Mostrar en el modal principal
                document.getElementById('detalleCreditos').innerHTML = html;
            })
            .catch(error => {
                console.error('Error completo:', error);
                alert('Error al cargar los detalles del crédito: ' + error.message);
            });
    }

    // Función para imprimir
    function imprimirModal() {
        const contenido = document.getElementById('contenidoImpresion').innerHTML;
        const ventana = window.open('', '_blank');
        ventana.document.write(`
            <html>
                <head>
                    <title>Créditos del Cliente</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .text-center { text-align: center; }
                    </style>
                </head>
                <body>
                    ${contenido}
                </body>
            </html>
        `);
        ventana.document.close();
        ventana.print();
    }

    // Lógica para el modal de selección de mes/año
    document.addEventListener('DOMContentLoaded', function() {
        const anioSelect = document.getElementById('anioSeleccionado');
        const mesSelect = document.getElementById('mesSeleccionado');
        const btnGenerar = document.getElementById('btnGenerarReporte');

        // Cargar años disponibles desde las ventas
        fetch('{{ route("reportes.ventas.anios.disponibles") }}')
            .then(response => response.json())
            .then(anios => {
                aniosSelect.innerHTML = ''; // Limpiar opciones anteriores
                anios.forEach(anio => {
                    const option = document.createElement('option');
                    option.value = anio;
                    option.textContent = anio;
                    anioSelect.appendChild(option);
                });

                // Establecer el año actual por defecto
                const anioActual = new Date().getFullYear();
                if (anios.includes(anioActual)) {
                    anioSelect.value = anioActual;
                } else if (anios.length > 0) {
                    anioSelect.value = anios[0]; // Si no está el actual, usar el primero
                }
            })
            .catch(error => {
                console.error('Error al cargar años:', error);
                // Si falla, usar años por defecto como fallback
                const anioActual = new Date().getFullYear();
                anioSelect.innerHTML = `<option value="${anioActual}">${anioActual}</option>`;
            });

        // Manejar clic en "Generar Reporte"
        btnGenerar.addEventListener('click', function() {
            const anio = anioSelect.value;
            const mes = mesSelect.value;

            if (!anio || !mes) {
                alert('Por favor, seleccione un año y un mes.');
                return;
            }

            // Construir URL con parámetros
            let url = '{{ route("reportes.ventas.pdf.cuotas_mes") }}';
            const params = new URLSearchParams();
            params.append('anio', anio);
            params.append('mes', mes);

            // Abrir en nueva pestaña
            window.open(url + '?' + params.toString(), '_blank');

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionMes'));
            modal.hide();
        });

        // Manejar clic en "📅 Cuotas del Presente Mes" para preseleccionar mes actual
        // Buscar el botón original (antes de reemplazarlo) o asignarle un ID
        // Si cambió el botón a un <button>, cámbielo a <a> o agregue un ID al <button> y cámbielo aquí
        // Por ejemplo, si el botón original era el que abre el modal:
        // Manejar clic en "📅 Cuotas del Mes" para preseleccionar mes/año actual
        document.querySelector('[data-bs-target="#modalSeleccionMes"]').addEventListener('click', function() {
            const fechaActual = new Date();
            const mesActual = String(fechaActual.getMonth() + 1).padStart(2, '0'); // 01-12
            const anioActual = fechaActual.getFullYear();

            // Esperar a que el modal se abra y los años se carguen
            setTimeout(() => {
                // Solo preseleccionar si el año actual está disponible en el select
                if (anioSelect.querySelector(`option[value="${anioActual}"]`)) {
                    anioSelect.value = anioActual;
                }
                mesSelect.value = mesActual; // ✅ Selecciona el mes actual
            }, 100); // Pequeño delay para asegurar que el DOM esté listo
        });
    });

</script>
@endsection