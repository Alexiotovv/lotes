<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Lote;
use App\Models\Cliente;
use App\Models\Caja;
use App\Models\Movimiento;
use App\Models\Concepto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $query = Reserva::with(['cliente', 'lote', 'caja', 'user']);

        // Búsqueda por cliente, lote o fecha
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                // Buscar por cliente: nombre o dni_ruc
                $q->whereHas('cliente', function ($q2) use ($search) {
                    $q2->where('nombre_cliente', 'LIKE', "%$search%")
                    ->orWhere('dni_ruc', 'LIKE', "%$search%");
                })
                // Buscar por lote: código o nombre
                ->orWhereHas('lote', function ($q2) use ($search) {
                    $q2->where('codigo', 'LIKE', "%$search%")
                    ->orWhere('nombre', 'LIKE', "%$search%");
                })
                // Buscar por fecha exacta (YYYY-MM-DD)
                ->orWhereRaw("DATE(fecha_reserva) = ?", [$search])
                // Buscar por ID de reserva
                ->orWhere('id', 'LIKE', "%$search%");
            });
        }

        // Ordenar por fecha descendente
        $reservas = $query->latest()->paginate(10)->appends($request->query());

        return view('reservas.index', compact('reservas'));
    }

    public function create(Request $request)
    {
        $loteId = $request->query('lote_id');
        $lote = $loteId ? Lote::find($loteId) : null;

        $clientes = Cliente::all();
        $cajas = Caja::where('activo', true)->get();

        // Concepto para reserva (asegúrese de que exista)
        $conceptoReservaId = Concepto::where('nombre', 'like', '%reserva%')->value('id')
            ?? Concepto::where('tipo', 'ingreso')->first()?->id;

        $config = \App\Models\ConfiguracionGeneral::first();
        $monto_reserva = $config->monto_reserva_default; // ej: 200.00
        // $registrarCompra = $config->registrar_lote_compra; // true/false

        return view('reservas.create', compact('lote', 'clientes', 'cajas', 'conceptoReservaId','monto_reserva'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'lote_id' => 'required|exists:lotes,id|unique:reservas,lote_id',
            'caja_id' => 'required|exists:cajas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_reserva' => 'required|date',
            'observaciones' => 'nullable|string',
            'voucher' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ✅ Validar voucher
        ]);

        DB::transaction(function () use ($request) {
            // Verificar que el lote esté disponible
            $lote = Lote::findOrFail($request->lote_id);
            if ($lote->estado_lote_id !== 1) {
                throw new \Exception('El lote no está disponible para reservar.');
            }

            // Subir voucher si existe
            $voucherPath = null;
            if ($request->hasFile('voucher')) {
                $voucherPath = $request->file('voucher')->store('vouchers', 'public');
            }

            // 1. Crear la reserva
            $reserva = Reserva::create([
                'user_id' => auth()->id(),
                'cliente_id' => $request->cliente_id,
                'lote_id' => $request->lote_id,
                'caja_id' => $request->caja_id,
                'monto' => $request->monto,
                'fecha_reserva' => $request->fecha_reserva,
                'observaciones' => $request->observaciones,
                'voucher' => $voucherPath, // ✅ Guardar ruta del voucher
            ]);

            // 2. Registrar el movimiento en tesorería
            $conceptoId = Concepto::where('nombre', 'like', '%reserva%')->value('id')
                ?? Concepto::where('tipo', 'ingreso')->first()?->id;

            Movimiento::create([
                'caja_id' => $request->caja_id,
                'concepto_id' => $conceptoId,
                'venta_id' => null,
                'user_id' => auth()->id(),
                'referencia' => 'Reserva Lote ' . $reserva->lote->codigo,
                'monto' => $request->monto,
                'tipo' => 'ingreso',
                'fecha' => $request->fecha_reserva,
                'descripcion' => $request->observaciones,
            ]);

            // 3. Cambiar estado del lote a "Reservado"
            $estadoReservadoId = \App\Models\EstadoLote::where('estado', 'reservado')->value('id') ?? 2;
            $lote->update(['estado_lote_id' => $estadoReservadoId]);
        });

        return redirect()->route('reservas.index')->with('success', 'Reserva registrada correctamente.');
    }
    

    public function edit(Reserva $reserva)
    {
        $clientes = Cliente::all();
        $cajas = Caja::where('activo', true)->get();

        return view('reservas.edit', compact('reserva', 'clientes', 'cajas'));
    }
  

    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'caja_id' => 'required|exists:cajas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_reserva' => 'required|date',
            'observaciones' => 'nullable|string',
            'voucher' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ✅ Validar voucher
        ]);

        DB::transaction(function () use ($request, $reserva) {
            // Actualizar reserva
            $reserva->update($request->only([
                'cliente_id', 'caja_id', 'monto', 'fecha_reserva', 'observaciones'
            ]));

            // Actualizar voucher si se envió uno nuevo
            if ($request->hasFile('voucher')) {
                // Eliminar voucher anterior si existe
                if ($reserva->voucher) {
                    $rutaAnterior = storage_path('app/public/' . $reserva->voucher);
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }

                // Subir nuevo voucher
                $voucherPath = $request->file('voucher')->store('vouchers', 'public');
                $reserva->update(['voucher' => $voucherPath]);
            }

            // Actualizar movimiento asociado (por referencia o por lote)
            $movimiento = Movimiento::where('referencia', 'like', '%Reserva Lote ' . $reserva->lote->codigo . '%')
                ->first();

            if ($movimiento) {
                $movimiento->update([
                    'caja_id' => $request->caja_id,
                    'monto' => $request->monto,
                    'fecha' => $request->fecha_reserva,
                    'descripcion' => $request->observaciones,
                ]);
            }
        });

        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada correctamente.');
    }

    // public function update(Request $request, Reserva $reserva)
    // {
    //     $request->validate([
    //         'cliente_id' => 'required|exists:clientes,id',
    //         'caja_id' => 'required|exists:cajas,id',
    //         'monto' => 'required|numeric|min:0.01',
    //         'fecha_reserva' => 'required|date',
    //         'observaciones' => 'nullable|string',
    //         'voucher' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ✅ Validar voucher
    //     ]);

    //     DB::transaction(function () use ($request, $reserva) {
    //         // Actualizar reserva
    //         $reserva->update($request->only([
    //             'cliente_id', 'caja_id', 'monto', 'fecha_reserva', 'observaciones'
    //         ]));

    //         // Actualizar voucher si se envió uno nuevo
    //         if ($request->hasFile('voucher')) {
    //             // Eliminar voucher anterior si existe
    //             if ($reserva->voucher) {
    //                 $rutaAnterior = storage_path('app/public/' . $reserva->voucher);
    //                 if (file_exists($rutaAnterior)) {
    //                     unlink($rutaAnterior);
    //                 }
    //             }

    //             // Subir nuevo voucher
    //             $voucherPath = $this->comprimirYGuardarVoucher($request->file('voucher'));
    //             $reserva->update(['voucher' => $voucherPath]);
    //         }

    //         // Actualizar movimiento asociado (por referencia o por lote)
    //         $movimiento = Movimiento::where('referencia', 'like', '%Reserva Lote ' . $reserva->lote->codigo . '%')
    //             ->first();

    //         if ($movimiento) {
    //             $movimiento->update([
    //                 'caja_id' => $request->caja_id,
    //                 'monto' => $request->monto,
    //                 'fecha' => $request->fecha_reserva,
    //                 'descripcion' => $request->observaciones,
    //             ]);
    //         }
    //     });

    //     return redirect()->route('reservas.index')->with('success', 'Reserva actualizada correctamente.');
    // }

    public function destroy(Reserva $reserva)
        
        {if (auth()->user()->rol !== 'admin') {
            abort(403, 'Acceso denegado.');
        }

        DB::transaction(function () use ($reserva) {
            // Eliminar movimiento
            Movimiento::where('referencia', 'like', '%Reserva Lote ' . $reserva->lote->codigo . '%')->delete();

            // Restaurar lote a "Disponible"
            $estadoDisponibleId = \App\Models\EstadoLote::where('estado', 'disponible')->value('id') ?? 1;
            $reserva->lote()->update(['estado_lote_id' => $estadoDisponibleId]);

            $reserva->delete();
        });

        return redirect()->route('reservas.index')->with('success', 'Reserva eliminada.');
    }
}