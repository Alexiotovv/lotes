<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MetodopagoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\EstadoLoteController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\MapImageController;
use App\Http\Controllers\TesoreriaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ConceptoController;

// === Autenticación ===
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// === Rutas protegidas ===
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Página del mapa
    Route::get('/mapa', [MapaController::class, 'index'])->name('mapa.index');


    //Mapa creación rápido
    Route::get('/mapa/createlote', [MapaController::class, 'createLote'])->name('lote.create');
    Route::post('/mapa/guardar-lotes', [MapaController::class, 'guardarLotes'])->name('mapa.guardar');

    //Venta
    Route::resource('ventas', VentaController::class);
    Route::get('ventas/{venta}/cronograma', [VentaController::class, 'cronograma'])->name('ventas.cronograma');
    
    //Pagos
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/{venta}/detalle', [PagoController::class, 'detalle']);
    Route::get('/pagos/{venta}/cobrar', [PagoController::class, 'cobrar']);
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');

    //Editar Imagen en Mapa
    Route::get('/map/edit', [MapImageController::class, 'index'])->name('map.edit');
    Route::post('/map/store', [MapImageController::class, 'store'])->name('map.store');
    // Route::post('/map/update/{id}', [MapImageController::class, 'updatePosition'])->name('map.update');
    Route::post('/map/{id}', [MapImageController::class, 'updateMapPosition'])->name('map.update');
    Route::post('/map_images/{id}/update-position', [MapImageController::class, 'updatePosition'])->name('map_images.updatePosition');

    //Créditos
    Route::get('/creditos', [CreditoController::class, 'index'])->name('creditos.index');
    Route::get('/creditos/{venta}/calendario', [CreditoController::class, 'calendario'])->name('creditos.calendario');
    Route::get('/creditos/{venta}/pagos', [CreditoController::class, 'pagos'])->name('creditos.pagos');


    //CRUD en el Mapa
    Route::post('/lotes/store', [LoteController::class, 'store'])->name('lotes.store');
    Route::put('/lotes/{lote}', [LoteController::class, 'update'])->name('lotes.update');
    Route::delete('/lotes/{lote}', [LoteController::class, 'destroy'])->name('lotes.destroy');
    
    // Página index CRUD de lotes
    Route::get('/lotes', [LoteController::class, 'indexView'])->name('lotes.indexView');

    // === API para CRUD AJAX ===
    Route::prefix('api')->group(function () {
        Route::get('/lotes', [LoteController::class, 'index'])->name('api.lotes.index');
        Route::post('/lotes', [LoteController::class, 'store'])->name('api.lotes.store');
        Route::put('/lotes/{lote}', [LoteController::class, 'update'])->name('api.lotes.update');
        Route::delete('/lotes/{lote}', [LoteController::class, 'destroy'])->name('api.lotes.destroy');
    });

    Route::resource('clientes', ClienteController::class);
    Route::resource('metodopagos', MetodopagoController::class);
    Route::resource('cotizaciones', CotizacionController::class);
    Route::resource('estado_lotes', EstadoLoteController::class);
    Route::get('/empresa', [EmpresaController::class, 'index'])->name('empresa.edit');
    Route::put('/empresa/{empresa}', [EmpresaController::class, 'update'])->name('empresa.update');
    
    
    // Módulo Tesorería
    Route::prefix('tesoreria')->name('tesoreria.')->group(function () {
        
        // Movimientos
        Route::get('/', [TesoreriaController::class, 'index'])->name('index');
        Route::get('/create', [TesoreriaController::class, 'create'])->name('create');
        Route::post('/', [TesoreriaController::class, 'store'])->name('store');
        
        // Cajas
        Route::prefix('cajas')->name('cajas.')->group(function () {
            Route::get('/', [CajaController::class, 'index'])->name('index');
            Route::get('/create', [CajaController::class, 'create'])->name('create');
            Route::post('/', [CajaController::class, 'store'])->name('store');
            Route::get('/{caja}/edit', [CajaController::class, 'edit'])->name('edit');
            Route::put('/{caja}', [CajaController::class, 'update'])->name('update');
            Route::post('/{caja}/toggle', [CajaController::class, 'toggle'])->name('toggle');
    });
    
        // Conceptos
        Route::prefix('conceptos')->name('conceptos.')->group(function () {
            Route::get('/', [ConceptoController::class, 'index'])->name('index');
            Route::get('/create', [ConceptoController::class, 'create'])->name('create');
            Route::post('/', [ConceptoController::class, 'store'])->name('store');
            Route::get('/{concepto}/edit', [ConceptoController::class, 'edit'])->name('edit');
            Route::put('/{concepto}', [ConceptoController::class, 'update'])->name('update');
            Route::post('/{concepto}/toggle', [ConceptoController::class, 'toggle'])->name('toggle');
        });
    });

    // Módulo Reportes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        // Reportes de Ventas
        Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/ventas/creditos-cliente', [ReporteController::class, 'creditosPorCliente'])->name('ventas.creditos.cliente');
        
        // Rutas para PDFs (puede implementar generación real más adelante)
        Route::get('/ventas/pdf/lista', [ReporteController::class, 'listaVentasPdf'])->name('ventas.pdf.lista');
        Route::get('/ventas/pdf/detalle', [ReporteController::class, 'detalleVentasPdf'])->name('ventas.pdf.detalle');
        Route::get('/ventas/pdf/consolidado', [ReporteController::class, 'consolidadoPdf'])->name('ventas.pdf.consolidado');
        Route::get('/ventas/pdf/cuotas-pendientes', [ReporteController::class, 'cuotasPendientesPdf'])->name('ventas.pdf.cuotas_pendientes');
        Route::get('/ventas/pdf/cuotas-mes', [ReporteController::class, 'cuotasMesPdf'])->name('ventas.pdf.cuotas_mes');
    });


    // Route::get('/cotizaciones/{id}/print', [CotizacionController::class, 'print'])->name('cotizaciones.print');
    //Solo renderiza el cronograma no guarda nada
    Route::get('/cotizaciones/{cotizacion}/cronograma', [CotizacionController::class, 'cronograma'])->name('cotizaciones.cronograma');

    // === Solo para administradores ===
    Route::prefix('admin')->as('admin.')->middleware('admin')->group(function () {
        //Rutas Cliente
        
        //RutaVentas
        Route::resource('ventas', VentaController::class);
        Route::post('ventas/{venta}/generar-cronograma', [VentaController::class, 'generarCronograma'])->name('ventas.generar-cronograma');

        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');

        Route::resource('users', UserController::class);
    });
});


// === Redirección raíz ===
Route::get('/', function () {
    return redirect()->route('login');
});
