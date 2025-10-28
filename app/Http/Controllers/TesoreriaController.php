<?php
namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Concepto;
use App\Models\Movimiento;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TesoreriaController extends Controller
{
    // Listado de movimientos
    public function index(Request $request)
    {
        $query = Movimiento::with(['caja', 'concepto', 'venta.cliente']);
        
        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }
        if ($request->filled('caja_id')) {
            $query->where('caja_id', $request->caja_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $movimientos = $query->latest()->paginate(15);
        $cajas = Caja::where('activo', true)->get();
        
        return view('tesoreria.index', compact('movimientos', 'cajas', 'request'));
    }

    // Formulario para registrar movimiento
    public function create()
    {
        $cajas = Caja::where('activo', true)->get();
        $conceptos = Concepto::where('activo', true)->get();
        $ventas = Venta::with('cliente')->get(); // Para vincular ventas
        
        return view('tesoreria.create', compact('cajas', 'conceptos', 'ventas'));
    }

    // Registrar movimiento (gasto o ingreso manual)
    public function store(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'concepto_id' => 'required|exists:conceptos,id',
            'venta_id' => 'nullable|exists:ventas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'comprobante' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validar coherencia: si es ingreso, puede tener venta_id; si es egreso, no
        $concepto = Concepto::findOrFail($request->concepto_id);
        if ($concepto->tipo === 'egreso' && $request->venta_id) {
            return back()->withErrors(['venta_id' => 'Los egresos no pueden vincularse a una venta.']);
        }

        $data = $request->except('comprobante');
        $data['user_id'] = auth()->id();
        $data['tipo'] = $concepto->tipo;

        // Subir comprobante
        if ($request->hasFile('comprobante')) {
            $data['comprobante'] = $request->file('comprobante')->store('tesoreria/comprobantes', 'public');
        }

        Movimiento::create($data);

        return redirect()->route('tesoreria.index')->with('success', 'Movimiento registrado correctamente.');
    }

    // Registrar ingreso AUTOMÁTICO desde una venta (llamado desde PagoController)
    public static function registrarIngresoVenta($ventaId, $cajaId, $monto, $fecha, $conceptoId = null, $referencia = null)
    {
        $conceptoId = $conceptoId ?? Concepto::where('nombre', 'Cuota de financiamiento')->value('id');
        $conceptoId = $conceptoId ?? Concepto::where('tipo', 'ingreso')->first()->id;

        return Movimiento::create([
            'caja_id' => $cajaId,
            'concepto_id' => $conceptoId,
            'venta_id' => $ventaId,
            'user_id' => auth()->id(),
            'monto' => $monto,
            'tipo' => 'ingreso',
            'fecha' => $fecha,
            'referencia' => $referencia,
            'descripcion' => 'Ingreso automático desde cobro de venta',
        ]);
    }
}