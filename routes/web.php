<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReservaController;

// === Autenticación ===
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// === Rutas comunes (todos los usuarios autenticados) ===
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// === Rutas solo para VENDEDORES ===
Route::middleware(['auth', 'vendedor'])->group(function () {
    //Reservas
    Route::resource('reservas', ReservaController::class)->except(['show']);
    Route::get('/reservas/{reserva}/edit', [ReservaController::class, 'edit'])->name('reservas.edit.ajax'); // para modal
    
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
});

// === Rutas solo para ADMINISTRADORES ===
Route::middleware(['auth', 'admin'])->group(function () {
    // Aquí va TODO lo demás: lotes, pagos, tesorería, reportes, etc.
    // Copie aquí todas las rutas que actualmente están bajo el grupo 'auth'
    // EXCEPTO las 4 que ya asignamos al vendedor.

    // Página del mapa
    Route::get('/mapa', [MapaController::class, 'index'])->name('mapa.index');
    Route::get('/mapa/createlote', [MapaController::class, 'createLote'])->name('lote.create');
    Route::post('/mapa/guardar-lotes', [MapaController::class, 'guardarLotes'])->name('mapa.guardar');

    // Venta (CRUD completo)
    Route::resource('ventas', VentaController::class)->except(['create','index','store','edit','update']);
    Route::get('ventas/{venta}/cronograma', [VentaController::class, 'cronograma'])->name('ventas.cronograma');


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
