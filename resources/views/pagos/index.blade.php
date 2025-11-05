@extends('layouts.app')
@section('styles')
    <style>
        #listaCuotas::-webkit-scrollbar {
            width: 8px;
        }
        #listaCuotas::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        #listaCuotas::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        #listaCuotas::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
          /* Estilo para vista previa */
        #vistaPrevia {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        #vistaPrevia:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        #btnEliminarFoto {
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        #vistaPrevia:hover #btnEliminarFoto {
            opacity: 1;
        }
    </style>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h4>📋 Lista de Cobros (Créditos)</h4>
    </div>
    <div class="card-body">
        <table id="tablaPagos" class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha Venta</th>
                    <th>Próximo Pago</th>
                    <th>Cuota Mensual (S/)</th>
                    <th>Total Deuda (S/)</th>
                    <th>Total Venta (S/)</th>
                    <th>Calendario</th>
                    <th>Pagos</th>
                    <th>Cobrar</th> <!-- Nueva columna -->
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $v)
                    @php
                        $proxPago = $v->cronogramas->where('estado','pendiente')->first();
                        $totalDeuda = $v->cronogramas->where('estado','!=','pagado')->sum('cuota');
                        $totalVenta = $v->lote->area_m2 * $v->lote->precio_m2;
                    @endphp
                    <tr>
                        <td>{{ $v->id }}</td>
                        <td>{{ $v->cliente->nombre_cliente }}</td>
                        <td>{{ $v->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($proxPago)
                                <span class="badge bg-{{ $proxPago->fecha_pago < today() ? 'danger' : 'success' }}">
                                    {{ $proxPago->fecha_pago }}
                                </span>
                            @else
                                <span class="badge bg-secondary">FINALIZADO</span>
                            @endif
                        </td>
                        <td><span class="badge bg-info">{{ number_format($v->cuota, 2) }}</span></td>
                        <td><span class="badge bg-primary">{{ number_format($totalDeuda, 2) }}</span></td>
                        <td><span class="badge bg-dark">{{ number_format($totalVenta, 2) }}</span></td>
                        <td><a href="{{ route('ventas.cronograma', $v) }}" target="_blank" class="btn btn-sm btn-primary">Calendario</a></td>
                        <td><button class="btn btn-sm btn-success" onclick="verPagos({{ $v->id }})">Ver pagos</button></td>
                        <td><button class="btn btn-sm btn-warning" onclick="modalCobrar({{ $v->id }})">Cobrar</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Ver Pagos -->
<div class="modal fade" id="modalPagos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pagos realizados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="tablaDetallePagos">
          <thead class="table-secondary">
            <tr>
              <th>#</th>
              <th>N° Cuota</th> <!-- ← Nueva columna -->
              <th>Fecha Pago</th>
              <th>Monto</th>
              <th>Método</th>
              <th>Referencia</th>
              <th>Voucher</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
        <button class="btn btn-outline-primary" onclick="exportarExcel()">📤 Exportar Excel</button>
        <button class="btn btn-outline-secondary" onclick="window.print()">🖨️ Imprimir</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Registrar Cobro -->
<div class="modal fade" id="modalCobrar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">Registrar Cobro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formCobro" method="POST" action="{{ route('pagos.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="venta_id" id="venta_id">
          <div class="mb-3">
            <label class="form-label fw-bold">Seleccione la caja donde se depositará el pago:</label>
            <select name="caja_id" class="form-select" required>
                <option value="">Seleccione una caja</option>
                @foreach($cajas as $caja)
                    <option value="{{ $caja->id }}">
                        {{ $caja->nombre }} 
                        @if($caja->tipo === 'banco') 
                            (Banco)
                        @elseif($caja->tipo === 'digital')
                            (Digital)
                        @else
                            (Efectivo)
                        @endif
                    </option>
                @endforeach
            </select>
            <div class="text-danger mt-1" id="errorCaja" style="display:none;">Seleccione una caja.</div>
          
          </div>


          <div class="row g-4">
            <!-- Columna izquierda: Lista de cuotas -->
            <div class="col-lg-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Seleccione la cuota a cobrar:</label>
                <div id="listaCuotas" class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                  <div class="text-center">
                    <div class="spinner-border spinner-border-sm"></div> Cargando...
                  </div>
                </div>
                <div class="text-danger mt-2" id="errorCuota" style="display:none;">Seleccione una cuota.</div>
              </div>
            </div>

            <!-- Columna derecha: Formulario de cobro -->
            <div class="col-lg-6">
              <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                  <h6 class="mb-0">Detalles del Cobro</h6>
                </div>
                <div class="card-body">
                  <div id="detalleCuota" style="display:none;">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Monto de la cuota:</label>
                        <input type="text" class="form-control" id="cuotaTotal" readonly>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Saldo pendiente:</label>
                        <input type="text" class="form-control" id="saldoPendiente" readonly>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Fecha de pago real:</label>
                        <input type="date" name="fecha_pago" class="form-control" value="{{ today()->toDateString() }}" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Monto a cobrar (S/):</label>
                        <input type="number" step="0.01" name="monto_pagado" class="form-control" min="0.01" required>
                        <div class="text-danger mt-1" id="errorMonto"></div>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Método de pago:</label>
                        <input type="text" name="metodo_pago" class="form-control" placeholder="Efectivo, Transferencia, etc.">
                      </div>
                      <div class="col-12">
                        <label class="form-label">Referencia (N° operación, recibo, etc.):</label>
                        <input type="text" name="referencia" class="form-control">
                      </div>
                      <div class="col-12">
                          <label class="form-label">Comprobante de pago (voucher):</label>
                          <div class="d-flex flex-column align-items-start">
                              <!-- Botón para cámara -->
                              <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btnCamara">
                                  📷 Tomar Foto
                              </button>
                              
                              <!-- Input file oculto -->
                              <input type="file" name="voucher" id="voucherInput" class="form-control d-none" accept="image/*" capture="environment">
                              
                              <!-- Vista previa -->
                              <div id="vistaPrevia" class="mt-2" style="display:none; width: 180px; height: 180px; border: 1px dashed #ccc; border-radius: 8px; overflow: hidden; position: relative;">
                                  <img id="imgPrevia" src="" alt="Vista previa" style="width: 100%; height: 100%; object-fit: cover;">
                                  <button type="button" class="btn btn-danger btn-sm" id="btnEliminarFoto" style="position: absolute; top: 4px; right: 4px; padding: 2px 6px;">
                                      ✕
                                  </button>
                              </div>
                          </div>
                          <small class="text-muted">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Observación:</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
                      </div>
                    </div>
                  </div>
                  <div id="sinCuotaSeleccionada" class="text-center py-4">
                    <i class="fas fa-info-circle text-muted fs-1 mb-2"></i>
                    <p class="text-muted">Seleccione una cuota para registrar el cobro.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">✅ Registrar Pago</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script>
function verPagos(venta_id) {
    fetch(`/pagos/${venta_id}/detalle`)
        .then(r => r.json())
        .then(pagos => {
            const tbody = document.querySelector('#tablaDetallePagos tbody');
            tbody.innerHTML = '';
            pagos.forEach((p, i) => {
                const voucherHtml = p.voucher 
                    ? `<a href="/storage/${p.voucher}" target="_blank" class="btn btn-sm btn-outline-primary">🖼️ Ver</a>`
                    : '—';
                
                // Mostrar número de cuota o guion si no existe
                const nroCuota = p.nro_cuota ? `#${p.nro_cuota}` : '—';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${nroCuota}</td> <!-- ← Nueva columna -->
                        <td>${p.fecha_pago}</td>
                        <td>S/ ${parseFloat(p.monto_pagado).toFixed(2)}</td>
                        <td>${p.metodo_pago ?? '-'}</td>
                        <td>${p.referencia ?? '-'}</td>
                        <td>${voucherHtml}</td>
                    </tr>
                `;
            });
            new bootstrap.Modal(document.getElementById('modalPagos')).show();
        });
}

function modalCobrar(venta_id) {
    document.getElementById('venta_id').value = venta_id;
    document.getElementById('listaCuotas').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div> Cargando...</div>';
    
    fetch(`/pagos/${venta_id}/cobrar`)
        .then(r => r.json())
        .then(cuotas => {
            const contenedor = document.getElementById('listaCuotas');
            if (cuotas.length === 0) {
                contenedor.innerHTML = '<div class="alert alert-info">No hay cuotas registradas.</div>';
                return;
            }
            
            let html = '';
            cuotas.forEach(c => {
                // Determinar clase y texto del badge
                let badgeHtml = '';
                let isDisabled = false;
                let cursorStyle = 'pointer';
                
                if (c.estado === 'pagado') {
                    badgeHtml = '<span class="badge bg-success ms-2">PAGADO</span>';
                    isDisabled = true;
                    cursorStyle = 'not-allowed';
                } else if (c.estado === 'vencido') {
                    badgeHtml = '<span class="badge bg-danger ms-2">VENCIDA</span>';
                } else {
                    badgeHtml = '<span class="badge bg-warning ms-2">PENDIENTE</span>';
                }

                html += `
                    <div class="form-check mb-2 p-2 border rounded" style="cursor: ${cursorStyle}; opacity: ${isDisabled ? '0.7' : '1'};">
                        <input class="form-check-input cuota-radio" type="radio" name="cronograma_id" value="${c.id}" 
                            ${isDisabled ? 'disabled' : ''}
                            data-total="${c.cuota_total}" data-pendiente="${c.pendiente}">
                        <label class="form-check-label" ${isDisabled ? 'style="color: #6c757d;"' : ''}>
                            Cuota #${c.nro_cuota} - ${c.fecha_pago} 
                            (S/ ${parseFloat(c.cuota_total).toFixed(2)}) ${badgeHtml}
                            <br><small class="text-muted">
                                Pagado: S/ ${parseFloat(c.pagado).toFixed(2)} | 
                                Pendiente: S/ ${parseFloat(c.pendiente).toFixed(2)}
                            </small>
                        </label>
                    </div>
                `;
            });
            contenedor.innerHTML = html;
            
            // Evento al seleccionar una cuota (solo para no deshabilitadas)
            document.querySelectorAll('.cuota-radio:not(:disabled)').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const total = this.dataset.total;
                        const pendiente = this.dataset.pendiente;
                        document.getElementById('cuotaTotal').value = `S/ ${parseFloat(total).toFixed(2)}`;
                        document.getElementById('saldoPendiente').value = `S/ ${parseFloat(pendiente).toFixed(2)}`;
                        document.querySelector('input[name="monto_pagado"]').value = pendiente;
                        document.getElementById('detalleCuota').style.display = 'block';
                        document.getElementById('errorCuota').style.display = 'none';
                    }
                });
            });
        });
    
    new bootstrap.Modal(document.getElementById('modalCobrar')).show();
}
// Validación en tiempo real del monto
document.querySelector('input[name="monto_pagado"]')?.addEventListener('input', function() {
    const pendiente = parseFloat(document.getElementById('saldoPendiente')?.value.replace('S/ ', '') || 0);
    const monto = parseFloat(this.value || 0);
    const error = document.getElementById('errorMonto');
    
    if (monto > pendiente) {
        error.textContent = `El monto no puede exceder el saldo pendiente de S/ ${pendiente.toFixed(2)}`;
        error.style.display = 'block';
    } else {
        error.style.display = 'none';
    }
});

// Validación del formulario
document.getElementById('formCobro')?.addEventListener('submit', function(e) {
    const cronogramaId = document.querySelector('input[name="cronograma_id"]:checked');
    if (!cronogramaId) {
        e.preventDefault();
        document.getElementById('errorCuota').style.display = 'block';
    }
});


  // === Manejo de cámara y vista previa ===
    document.getElementById('btnCamara').addEventListener('click', function() {
        document.getElementById('voucherInput').click();
    });

    document.getElementById('voucherInput').addEventListener('change', async function(e) {
      const file = e.target.files[0];
      if (!file) return;

      // Mostrar vista previa temporal
      const reader = new FileReader();
      reader.onload = function(event) {
          document.getElementById('imgPrevia').src = event.target.result;
          document.getElementById('vistaPrevia').style.display = 'block';
      };
      reader.readAsDataURL(file);

      // === COMPRESIÓN DE IMAGEN ===
      const compressedFile = await compressImage(file, 0.6); // calidad entre 0.5–0.7 suele ir bien
      replaceFileInput(compressedFile);
  });


  // Función para comprimir imagen con <canvas>
  function compressImage(file, quality = 0.6) {
      return new Promise((resolve) => {
          const img = new Image();
          const reader = new FileReader();
          reader.onload = (e) => {
              img.src = e.target.result;
          };
          img.onload = () => {
              const canvas = document.createElement('canvas');
              const MAX_WIDTH = 1024; // puedes bajarlo a 800 si quieres más compresión
              const scale = Math.min(MAX_WIDTH / img.width, 1);
              canvas.width = img.width * scale;
              canvas.height = img.height * scale;
              const ctx = canvas.getContext('2d');
              ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

              // Exportar como JPEG comprimido
              canvas.toBlob(
                  (blob) => {
                      // Crear nuevo archivo tipo File para que Laravel lo lea normalmente
                      const compressed = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                          type: 'image/jpeg',
                          lastModified: Date.now(),
                      });
                      resolve(compressed);
                  },
                  'image/jpeg',
                  quality
              );
          };
          reader.readAsDataURL(file);
      });
  }

  // Reemplaza el archivo seleccionado por el comprimido
  function replaceFileInput(newFile) {
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(newFile);
      document.getElementById('voucherInput').files = dataTransfer.files;
  }







  document.getElementById('btnEliminarFoto').addEventListener('click', function() {
      document.getElementById('voucherInput').value = '';
      document.getElementById('vistaPrevia').style.display = 'none';
  });




</script>
@endsection