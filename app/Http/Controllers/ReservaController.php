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
    public function index()
    {
        $reservas = Reserva::with(['cliente', 'lote', 'caja', 'user'])
            ->latest()
            ->paginate(15);

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

        return view('reservas.create', compact('lote', 'clientes', 'cajas', 'conceptoReservaId'));
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
        ]);

        
        DB::transaction(function () use ($request) {
            // Verificar que el lote esté disponible
            $lote = Lote::findOrFail($request->lote_id);
            if ($lote->estado_lote_id !== 1) { // Asumiendo que ID=1 es "Disponible"
                return back()->withErrors(['lote_id' => 'El lote no está disponible para reservar.']);
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
            ]);

            // 2. Registrar el movimiento en tesorería
            $conceptoId = Concepto::where('nombre', 'like', '%reserva%')->value('id')
                ?? Concepto::where('tipo', 'ingreso')->first()?->id;

            Movimiento::create([
                'caja_id' => $request->caja_id,
                'concepto_id' => $conceptoId,
                'venta_id' => null, // No es una venta
                'user_id' => auth()->id(),
                'referencia' => 'Reserva Lote ' . $reserva->lote->codigo,
                'monto' => $request->monto,
                'tipo' => 'ingreso',
                'fecha' => $request->fecha_reserva,
                'descripcion' => $request->observaciones,
            ]);

            // 3. Cambiar estado del lote a "Reservado"
            // Asumiendo que el estado "Reservado" tiene ID = 2
            $estadoReservadoId = \App\Models\EstadoLote::where('estado', 'reservado')->value('id') ?? 2;
            $lote->update(['estado_lote_id' => $estadoReservadoId]);
        });

        return redirect()->route('reservas.index')->with('success', 'Reserva registrada correctamente.');
    }

    public function edit(Reserva $reserva)
    {
        $clientes = Cliente::all();
        $cajas = Caja::where('activo', true)->get();
        return response()->json([
            'reserva' => $reserva->load('cliente', 'lote', 'caja'),
            'clientes' => $clientes,
            'cajas' => $cajas,
        ]);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'caja_id' => 'required|exists:cajas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_reserva' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $reserva) {
            // Actualizar reserva
            $reserva->update($request->only([
                'cliente_id', 'caja_id', 'monto', 'fecha_reserva', 'observaciones'
            ]));

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

        return response()->json(['success' => true]);
    }

    public function destroy(Reserva $reserva)
    {
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