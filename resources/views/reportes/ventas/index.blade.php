@extends('layouts.app')

@section('content')

    
        <!-- Contenido principal -->
        <div class="col-md-12">
            <!-- Encabezado del reporte -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📋 Reporte de Ventas - LOTIZACIÓN LOS CEDROS DE SAN JUAN</h5>
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
                    <h6 class="mb-0">💰 Reporte de Créditos por Cliente - LOTIZACIÓN LOS CEDROS DE SAN JUAN</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" id="buscarCliente" class="form-control" placeholder="Buscar cliente...">
                        </div>
                        <div class="col-md-4">
                            <button id="btnCreditosCliente" class="btn btn-primary w-100">
                                🔍 Créditos x Cliente
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Otros Reportes -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">📄 Otros Reportes - LOTIZACIÓN LOS CEDROS DE SAN JUAN</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3">
                        <a href="{{ route('reportes.ventas.pdf.cuotas_pendientes') }}" class="btn btn-warning">
                            📋 Lista Créditos por Cobrar
                        </a>
                        <a href="{{ route('reportes.ventas.pdf.cuotas_mes') }}" class="btn btn-info">
                            📅 Cuotas del Presente Mes
                        </a>
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
    // Buscar cliente
    document.getElementById('btnCreditosCliente').addEventListener('click', () => {
        const nombre = document.getElementById('buscarCliente').value.trim();
        if (nombre) {
            window.location.href = `{{ route('reportes.ventas.creditos.cliente') }}?nombre=${encodeURIComponent(nombre)}`;
        } else {
            alert('Ingrese un nombre de cliente.');
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
</script>
@endsection