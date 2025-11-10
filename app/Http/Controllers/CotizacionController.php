<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Cliente;
use App\Models\MetodoPago;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\Tasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Cotizacion::with(['cliente', 'metodopago', 'lote']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cotizaciones.id', 'like', "%{$search}%")
                ->orWhereHas('cliente', function ($q2) use ($search) {
                    $q2->where('nombre_cliente', 'like', "%{$search}%");
                })
                ->orWhereHas('lote', function ($q2) use ($search) {
                    $q2->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%");
                })
                ->orWhereDate('fecha_pago', $search); // para coincidencia exacta de fecha
            });
        }

        $cotizaciones = $query->latest()->paginate(20);

        return view('cotizaciones.index', compact('cotizaciones', 'search'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $metodos = MetodoPago::all();
        $lotes = Lote::where('estado_lote_id', [1, 2])->get();
        $tasas = Tasa::orderBy('monto')->get();
        return view('cotizaciones.create', compact('clientes', 'metodos', 'lotes','tasas'));
    }

    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'metodopago_id' => 'required|exists:metodopagos,id',
            'tasa_interes' => 'required|numeric|min:0|max:1', // ✅ Dos puntos
            'fecha_pago' => 'required|date',
            'numero_cuotas' => 'required|integer|min:1',      // ✅ Dos puntos
            'inicial' => 'required|numeric|min:0',            // ✅ Dos puntos
            'detalles' => 'required|array|min:1',
            'detalles.*.lote_id' => ['required', Rule::exists('lotes', 'id')],
            'detalles.*.observaciones' => 'nullable|string|max:250',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['detalles'] as $detalle) {
                $lote = Lote::findOrFail($detalle['lote_id']);
                $precioTotal = $lote->area_m2 * $lote->precio_m2;
                $inicial = $validated['inicial'];
                $montoFinanciar = max(0, $precioTotal - $inicial);
                $nCuotas = $validated['numero_cuotas'];
                $tasaInteres = $validated['tasa_interes'];

                // Calcular cuota
                $cuota = 0;
                if ($montoFinanciar > 0 && $nCuotas > 0) {
                    if ($tasaInteres > 0) {
                        $tem = pow(1 + $tasaInteres, 1/12) - 1;
                        $cuota = ($montoFinanciar * $tem * pow(1 + $tem, $nCuotas)) / (pow(1 + $tem, $nCuotas) - 1);
                    } else {
                        $cuota = $montoFinanciar / $nCuotas;
                    }
                }
                
                Cotizacion::create([
                    'user_id' => auth()->id(),
                    'cliente_id' => $validated['cliente_id'],
                    'lote_id' => $detalle['lote_id'],
                    'metodopago_id' => $validated['metodopago_id'],
                    'fecha_pago' => $validated['fecha_pago'],
                    'numero_cuotas' => $nCuotas,
                    'inicial' => $inicial,
                    'monto_financiar' => round($montoFinanciar, 2),
                    'tasa_interes' => $tasaInteres,
                    'cuota' => round($cuota, 2),
                    'observaciones' => $detalle['observaciones'] ?? null,
                ]);
            
            }
        });

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización registrada correctamente');
    }


    public function cronograma(Cotizacion $cotizacion)
    {
        $rows = [];
        $saldo = $cotizacion->monto_financiar;
        $tem = $cotizacion->tasa_interes > 0 
            ? pow(1 + $cotizacion->tasa_interes, 1/12) - 1 
            : 0;
        $cuota = $cotizacion->cuota;
        $fechaPago = Carbon::parse($cotizacion->fecha_pago);
        
        // Inicializar totales
        $totalInteres = 0;
        $totalAmortizacion = 0;

        for ($i = 1; $i <= $cotizacion->numero_cuotas; $i++) {
            $interes = $saldo * $tem;
            $amortizacion = $cuota - $interes;
            
            // Asegurar que no se amortice más del saldo restante
            if ($amortizacion > $saldo) {
                $amortizacion = $saldo;
                $cuota = $interes + $amortizacion;
            }

            $rows[] = [
                'nro' => $i,
                'fecha_pago' => $fechaPago->copy()->addMonths($i - 1)->format('d/m/Y'),
                'saldo' => number_format($saldo, 2, '.', ','),
                'interes' => number_format($interes, 2, '.', ','),
                'amortizacion' => number_format($amortizacion, 2, '.', ','),
                'cuota' => number_format($cuota, 2, '.', ','),
            ];

            $totalInteres += $interes;
            $totalAmortizacion += $amortizacion;
            $saldo = max(0, $saldo - $amortizacion);
        }

        // Calcular totales finales
        $totalInteresFormateado = number_format($totalInteres, 2, '.', ',');
        $totalAmortizacionFormateado = number_format($totalAmortizacion, 2, '.', ',');
        $totalAPagar = $cotizacion->inicial + $totalInteres + $totalAmortizacion;
        $totalAPagarFormateado = number_format($totalAPagar, 2, '.', ',');

        // Pasar totales a la vista
        $empresa = Empresa::first();
        return view('cotizaciones.cronograma', compact(
            'cotizacion', 
            'rows', 
            'empresa',
            'totalInteresFormateado',
            'totalAmortizacionFormateado',
            'totalAPagarFormateado'
        ));
    }


    
    // public function print($id)
    // {
    //     $cotizacion = Cotizacion::with(['cliente', 'lote', 'metodopago'])->findOrFail($id);

    //     $saldo = $cotizacion->monto_financiar;
    //     $tasa = $cotizacion->tasa_interes / 100; // Tasa anual
    //     $n = $cotizacion->numero_cuotas;
    //     $cuota = $cotizacion->cuota;
        
    //     $rows = [];
    //     for ($i = 1; $i <= $n; $i++) {
    //         $interes = round($saldo * ($tasa / 12), 2);
    //         $amortizacion = round($cuota - $interes, 2);
    //         $saldo = round($saldo - $amortizacion, 2);
            
    //         $rows[] = [
    //             'nro' => $i,
    //             'fecha_pago' => now()->addMonths($i)->format('d-m-Y'),
    //             'saldo' => number_format(max($saldo, 0), 2, '.', ','),
    //             'interes' => number_format($interes, 2, '.', ','),
    //             'amortizacion' => number_format($amortizacion, 2, '.', ','),
    //             'cuota' => number_format($cuota, 2, '.', ','),
    //         ];
    //     }

    //     return view('cotizaciones.print', compact('cotizacion', 'rows'));
    // }

    // public function destroy(Cotizacion $cotizacione)
    // {
    //     $cotizacione->delete();
    //     return redirect()->route('cotizaciones.index')->with('success', 'Cotización eliminada');
    // }
    public function destroy($id)
    {
        $venta = Cotizacion::findOrFail($id);

        // Verifica si existen cronogramas asociados
        // if ($venta->cronogramas()->exists()) {
        //     return redirect()->route('cotizaciones.index')
        //         ->with('error', 'No se puede eliminar la cotizacion porque tiene datos asociados.');
        // }

        // Si no hay relaciones, elimina
        $venta->delete();

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización eliminada correctamente.');
    }

    
}
