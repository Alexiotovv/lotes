<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ConfiguracionGeneralController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\TasaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\MapImageController;
use App\Http\Controllers\ContratoAgrupadoController;
// === Autenticación ===
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// === Rutas comunes (todos los usuarios autenticados) ===
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// === Rutas solo para VENDEDORES y admines ===
Route::middleware(['auth', 'vendedor'])->group(function () {

    //Reservas
    Route::resource('reservas', ReservaController::class)->except(['show']);
    // Route::get('/reservas/{reserva}/edit', [ReservaController::class, 'edit'])->name('reservas.edit.ajax'); // para modal
    
    // Créditos
    Route::get('/creditos', [\App\Http\Controllers\CreditoController::class, 'index'])->name('creditos.index');
    Route::get('/creditos/{venta}/calendario', [\App\Http\Controllers\CreditoController::class, 'calendario'])->name('creditos.calendario');
    Route::get('/creditos/{venta}/pagos', [\App\Http\Controllers\CreditoController::class, 'pagos'])->name('creditos.pagos');
    
    // Vista de Lotes
    Route::get('/mapa/ver-lotes', [MapaController::class, 'verLotes'])->name('mapa.ver.lotes');

    // Clientes: solo crear
    Route::get('/clientes/index', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');

    // Cotizaciones: solo listar
    Route::get('/cotizaciones', [CotizacionController::class, 'index'])->name('cotizaciones.index');
    Route::get('/cotizaciones/create', [CotizacionController::class, 'create'])->name('cotizaciones.create');
    Route::post('/cotizaciones/store', [CotizacionController::class, 'store'])->name('cotizaciones.store');
    Route::delete('/cotizaciones/{id}', [CotizacionController::class, 'destroy'])->name('cotizaciones.destroy');


    // Ventas
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas/store', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}/edit', [VentaController::class, 'edit'])->name('ventas.edit');
    Route::put('/ventas/{venta}', [VentaController::class, 'update'])->name('ventas.update');
    // Cronograma de cotización (necesario para ver el PDF)
    Route::get('/cotizaciones/{cotizacion}/cronograma', [CotizacionController::class, 'cronograma'])->name('cotizaciones.cronograma');


    // Pagos
    Route::get('/pagos', [\App\Http\Controllers\PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/{venta}/detalle', [\App\Http\Controllers\PagoController::class, 'detalle']);
    Route::get('/pagos/{venta}/cobrar', [\App\Http\Controllers\PagoController::class, 'cobrar']);
    Route::post('/pagos', [\App\Http\Controllers\PagoController::class, 'store'])->name('pagos.store');

    //GenerarCronograma
    Route::post('ventas/{venta}/generar-cronograma', [VentaController::class, 'generarCronograma'])->name('ventas.generar-cronograma');

    // Detalle del cronograma en modal
    Route::get('/ventas/{venta}/cronograma-detalle', [VentaController::class, 'detalleCronograma'])->name('ventas.cronograma.detalle');

    // Eliminar cronograma
    Route::delete('/ventas/{venta}/eliminar-cronograma', [VentaController::class, 'eliminarCronograma'])->name('ventas.eliminar.cronograma');

    //Ventas-GenerarCronograma
    Route::get('ventas/{venta}/cronograma', [VentaController::class, 'cronograma'])->name('ventas.cronograma');

    //Imagenes superpuestas
    Route::post('/mapa/imagen-superpuesta/guardar', [MapImageController::class, 'guardarImagenSuperpuesta'])->name('imagen.superpuesta.guardar');
    Route::delete('/mapa/imagen-superpuesta/{id}', [MapImageController::class, 'eliminarImagenSuperpuesta'])->name('imagen.superpuesta.eliminar');
    Route::put('/mapa/imagen-superpuesta/actualizar/{id}', [MapImageController::class, 'actualizarImagenSuperpuesta'])->name('imagen.superpuesta.actualizar');


});

// === Rutas solo para ADMINISTRADORES ===
Route::middleware(['auth', 'admin'])->group(function () {
    // Rutas para contratos agrupados
    Route::get('/contratos-agrupados', [ContratoAgrupadoController::class, 'index'])->name('contratos.agrupados.index');
    Route::post('/contratos-agrupados/buscar-cliente', [ContratoAgrupadoController::class, 'buscarCliente'])->name('contratos.agrupados.buscar-cliente');
    Route::get('/contratos-agrupados/ventas-cliente/{clienteId}', [ContratoAgrupadoController::class, 'getVentasCliente'])->name('contratos.agrupados.ventas-cliente');
    Route::post('/contratos-agrupados/generar', [ContratoAgrupadoController::class, 'generarContrato'])->name('contratos.agrupados.generar');
    Route::get('/contratos-agrupados/vista-previa', [ContratoAgrupadoController::class, 'vistaPrevia'])->name('contratos.agrupados.vista-previa');
    Route::get('/contratos-agrupados/contratos-cliente/{clienteId}', [ContratoAgrupadoController::class, 'getContratosCliente']);

    //Imagen Posicionada
    Route::get('/mapa/editar', [MapImageController::class, 'index'])->name('map.edit');
    Route::post('/mapa/actualizar-posicion', [MapImageController::class, 'actualizarPosicion'])->name('mapa.actualizar.posicion');

    //Contratos
    Route::get('/contratos', [ContratoController::class, 'index'])->name('contratos.index');
    Route::get('/contratos/{contrato}', [ContratoController::class, 'ver'])->name('contratos.ver');
    Route::post('/ventas/{venta}/contrato/generar', [ContratoController::class, 'generar'])->name('ventas.contrato.generar');
    Route::delete('/contratos/{contrato}', [ContratoController::class, 'destroy'])->name('contratos.destroy');
    Route::delete('/contratos/{contrato}/eliminar-permanente', [ContratoController::class, 'eliminarPermanente'])->name('contratos.eliminar.permanente');

    Route::put('/ventas/{venta}/cambiar-estado', [VentaController::class, 'cambiarEstado'])->name('ventas.cambiar-estado');

    Route::resource('tasas', TasaController::class);

    Route::prefix('compras')->name('compras.')->group(function () {
        Route::get('/', [CompraController::class, 'index'])->name('index');
        Route::get('/create', [CompraController::class, 'create'])->name('create');
        Route::post('/', [CompraController::class, 'store'])->name('store');
    });

    Route::get('/configuracion', [ConfiguracionGeneralController::class, 'edit'])->name('configuracion.edit');
    Route::put('/configuracion', [ConfiguracionGeneralController::class, 'update'])->name('configuracion.update');


    // Página del mapa
    Route::get('/mapa', [MapaController::class, 'index'])->name('mapa.index');
    Route::get('/mapa/createlote', [MapaController::class, 'createLote'])->name('lote.create');
    Route::post('/mapa/guardar-lotes', [MapaController::class, 'guardarLotes'])->name('mapa.guardar');

    // Venta (CRUD completo)
    Route::resource('ventas', VentaController::class)->except(['create','index','store','edit','update']);
    


    // Editar Imagen en Mapa
    Route::get('/map/edit', [\App\Http\Controllers\MapImageController::class, 'index'])->name('map.edit');
    Route::post('/map/store', [\App\Http\Controllers\MapImageController::class, 'store'])->name('map.store');
    Route::post('/map/{id}', [\App\Http\Controllers\MapImageController::class, 'updateMapPosition'])->name('map.update');
    Route::post('/map_images/{id}/update-position', [\App\Http\Controllers\MapImageController::class, 'updatePosition'])->name('map_images.updatePosition');


    // CRUD en el Mapa
    Route::post('/lotes/store', [\App\Http\Controllers\LoteController::class, 'store'])->name('lotes.store');
    Route::put('/lotes/{lote}', [\App\Http\Controllers\LoteController::class, 'update'])->name('lotes.update');
    Route::delete('/lotes/{lote}', [\App\Http\Controllers\LoteController::class, 'destroy'])->name('lotes.destroy');
    Route::get('/lotes', [\App\Http\Controllers\LoteController::class, 'indexView'])->name('lotes.indexView');

    // API para CRUD AJAX
    Route::prefix('api')->group(function () {
        Route::get('/lotes', [\App\Http\Controllers\LoteController::class, 'index'])->name('api.lotes.index');
        Route::post('/lotes', [\App\Http\Controllers\LoteController::class, 'store'])->name('api.lotes.store');
        Route::put('/lotes/{lote}', [\App\Http\Controllers\LoteController::class, 'update'])->name('api.lotes.update');
        Route::delete('/lotes/{lote}', [\App\Http\Controllers\LoteController::class, 'destroy'])->name('api.lotes.destroy');
    });

    // Clientes (CRUD completo)
    Route::resource('clientes', ClienteController::class)->except(['create', 'store']);

    // Otros recursos
    Route::resource('metodopagos', \App\Http\Controllers\MetodopagoController::class);
    Route::resource('cotizaciones', CotizacionController::class)->except(['index','create','store']);
    Route::resource('estado_lotes', \App\Http\Controllers\EstadoLoteController::class);
    Route::get('/empresa', [\App\Http\Controllers\EmpresaController::class, 'index'])->name('empresa.edit');
    Route::put('/empresa/{empresa}', [\App\Http\Controllers\EmpresaController::class, 'update'])->name('empresa.update');

    // Tesorería
    Route::prefix('tesoreria')->name('tesoreria.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TesoreriaController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\TesoreriaController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\TesoreriaController::class, 'store'])->name('store');

        Route::prefix('cajas')->name('cajas.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CajaController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CajaController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CajaController::class, 'store'])->name('store');
            Route::get('/{caja}/edit', [\App\Http\Controllers\CajaController::class, 'edit'])->name('edit');
            Route::put('/{caja}', [\App\Http\Controllers\CajaController::class, 'update'])->name('update');
            Route::post('/{caja}/toggle', [\App\Http\Controllers\CajaController::class, 'toggle'])->name('toggle');
        });

        Route::prefix('conceptos')->name('conceptos.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ConceptoController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\ConceptoController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\ConceptoController::class, 'store'])->name('store');
            Route::get('/{concepto}/edit', [\App\Http\Controllers\ConceptoController::class, 'edit'])->name('edit');
            Route::put('/{concepto}', [\App\Http\Controllers\ConceptoController::class, 'update'])->name('update');
            Route::post('/{concepto}/toggle', [\App\Http\Controllers\ConceptoController::class, 'toggle'])->name('toggle');
        });
    });

    // Reportes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/ventas/creditos/cliente/dni', [ReporteController::class, 'creditosClientePorDni'])->name('ventas.creditos.cliente.dni');
        Route::get('/ventas/{venta}/detalles-credito', [ReporteController::class, 'detallesCredito'])->name('ventas.detalles.credito');
        Route::get('/ventas/creditos-por-cobrar', [ReporteController::class, 'creditosPorCobrarPDF'])->name('ventas.pdf.creditos_por_cobrar');
        
        Route::get('/ventas/anios-disponibles', [ReporteController::class, 'aniosDisponibles'])->name('ventas.anios.disponibles');
        
        Route::get('/ventas', [\App\Http\Controllers\ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/ventas/creditos-cliente', [\App\Http\Controllers\ReporteController::class, 'creditosPorCliente'])->name('ventas.creditos.cliente');
        Route::get('/ventas/pdf/lista', [\App\Http\Controllers\ReporteController::class, 'listaVentasPdf'])->name('ventas.pdf.lista');
        Route::get('/ventas/pdf/detalle', [\App\Http\Controllers\ReporteController::class, 'detalleVentasPdf'])->name('ventas.pdf.detalle');
        Route::get('/ventas/pdf/consolidado', [\App\Http\Controllers\ReporteController::class, 'consolidadoPdf'])->name('ventas.pdf.consolidado');
        Route::get('/ventas/pdf/cuotas-pendientes', [\App\Http\Controllers\ReporteController::class, 'cuotasPendientesPdf'])->name('ventas.pdf.cuotas_pendientes');
        Route::get('/ventas/pdf/cuotas-mes', [\App\Http\Controllers\ReporteController::class, 'cuotasMesPdf'])->name('ventas.pdf.cuotas_mes');
    });

    // Admin
    Route::prefix('admin')->as('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::get('reportes', [\App\Http\Controllers\ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/exportar', [\App\Http\Controllers\ReporteController::class, 'exportar'])->name('reportes.exportar');
    });
});

// === Redirección raíz ===
Route::get('/', function () {
    return redirect()->route('login');
});
