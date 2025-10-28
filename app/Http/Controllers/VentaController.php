<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cronograma;
use App\Models\Cliente;
use App\Models\MetodoPago;
use App\Models\Lote;
use App\Models\Tasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Empresa;
class VentaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Venta::with(['cliente', 'lote', 'metodopago']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('cliente', fn($q2) => $q2->where('nombre_cliente', 'like', "%{$search}%"))
                  ->orWhereHas('lote', fn($q2) => $q2->where('codigo', 'like', "%{$search}%"));
            });
        }

        $ventas = $query->latest()->paginate(10); 
        return view('ventas.index', compact('ventas', 'search'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $metodos = MetodoPago::all();
        $lotes = Lote::where('estado_lote_id', 1)->get();
        $tasas = Tasa::all();
        return view('ventas.create', compact('clientes', 'metodos', 'lotes','tasas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'metodopago_id' => 'required|exists:metodopagos,id',
            'tasa_interes' => 'required|numeric|min:0|max:1',
            'fecha_pago' => 'required|date',
            'numero_cuotas' => 'required|integer|min:1',
            'inicial' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
            'detalles.*.lote_id' => 'required|exists:lotes,id',
        ]);

        DB::transaction(function() use ($validated) {
            foreach ($validated['detalles'] as $detalle) {
                $lote = Lote::findOrFail($detalle['lote_id']);
                $precioTotal = $lote->area_m2 * $lote->precio_m2;
                $inicial = $validated['inicial'];
                $montoFinanciar = max(0, $precioTotal - $inicial);
                $nCuotas = $validated['numero_cuotas'];
                $tasaInteres = $validated['tasa_interes'];

                // Cálculo de cuota
                $cuota = 0;
                if ($montoFinanciar > 0 && $nCuotas > 0) {
                    if ($tasaInteres > 0) {
                        $tem = pow(1 + $tasaInteres, 1/12) - 1;
                        $cuota = ($montoFinanciar * $tem * pow(1 + $tem, $nCuotas)) / (pow(1 + $tem, $nCuotas) - 1);
                    } else {
                        $cuota = $montoFinanciar / $nCuotas;
                    }
                }

                // Guardar venta
                $venta = Venta::create([
                    'user_id' => auth()->id(),
                    'cliente_id' => $validated['cliente_id'],
                    'lote_id' => $detalle['lote_id'],
                    'metodopago_id' => $validated['metodopago_id'],//tipo_venta
                    'fecha_pago' => $validated['fecha_pago'],
                    'numero_cuotas' => $nCuotas,
                    'inicial' => $inicial,
                    'monto_financiar' => round($montoFinanciar, 2),
                    'tasa_interes' => $tasaInteres,
                    'cuota' => round($cuota, 2),
                    'observaciones' => $detalle['observaciones'] ?? null,
                ]);

                // Generar cronograma automáticamente
                $saldo = $montoFinanciar;
                $tem = $tasaInteres > 0 ? pow(1 + $tasaInteres, 1/12) - 1 : 0;
                $fechaPago = Carbon::parse($validated['fecha_pago']);

                for ($i = 1; $i <= $nCuotas; $i++) {
                    $interes = $saldo * $tem;
                    $amortizacion = $cuota - $interes;
                    if ($amortizacion > $saldo) {
                        $amortizacion = $saldo;
                        $cuota = $interes + $amortizacion;
                    }

                    Cronograma::create([
                        'venta_id' => $venta->id,
                        'nro_cuota' => $i,
                        'fecha_pago' => $fechaPago->copy()->addMonths($i - 1),
                        'saldo' => round($saldo, 2),
                        'interes' => round($interes, 2),
                        'amortizacion' => round($amortizacion, 2),
                        'cuota' => round($cuota, 2),
                        'estado' => 'pendiente',
                    ]);

                    $saldo = max(0, $saldo - $amortizacion);
                }

                // Cambiar estado del lote a vendido
                $lote->update(['estado_lote_id' => 3]);
            }

            // ✅ Registrar movimiento de inicial en Caja Principal (solo si > 0)
            if ($inicial > 0) {
                // Obtener la Caja Principal (asumiendo que su nombre es "Caja Principal")
                $cajaPrincipal = \App\Models\Caja::where('nombre', 'Caja Principal')->first();
                
                // Si no existe, usar la primera caja activa
                if (!$cajaPrincipal) {
                    $cajaPrincipal = \App\Models\Caja::where('activo', true)->first();
                }

                if ($cajaPrincipal) {
                    \App\Http\Controllers\TesoreriaController::registrarIngresoVenta(
                        ventaId: $venta->id,
                        cajaId: $cajaPrincipal->id,
                        monto: $inicial,
                        fecha: $validated['fecha_pago'],
                        conceptoId: \App\Models\Concepto::where('nombre', 'Pago inicial')->value('id') 
                                ?? \App\Models\Concepto::where('tipo', 'ingreso')->first()->id,
                        referencia: 'Inicial Venta #' . $venta->id
                    );
                }
            }

        });

        return redirect()->route('ventas.index')->with('success', 'Venta registrada y cronograma generado.');
    }


    public function cronograma(Venta $venta)
    {
        // Trae los registros del cronograma asociados a esta venta
        $rows = Cronograma::where('venta_id', $venta->id)
            ->orderBy('nro_cuota')
            ->get();

        // Obtener totales
        $totalInteres = $rows->sum('interes');
        $totalAmortizacion = $rows->sum('amortizacion');
        $totalCuota = $rows->sum('cuota');
        $totalAPagar = $venta->inicial + $totalCuota;

        // Formatear totales
        $totalInteresFormateado = number_format($totalInteres, 2, '.', ',');
        $totalAmortizacionFormateado = number_format($totalAmortizacion, 2, '.', ',');
        $totalAPagarFormateado = number_format($totalAPagar, 2, '.', ',');

        // Información de empresa
        $empresa = Empresa::first();

        return view('ventas.cronograma', compact(
            'venta',
            'rows',
            'empresa',
            'totalInteresFormateado',
            'totalAmortizacionFormateado',
            'totalAPagarFormateado'
        ));
    }

}
