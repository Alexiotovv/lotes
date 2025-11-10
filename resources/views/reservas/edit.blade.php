@extends('layouts.app')

@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/select2-bootstrap.css')}}" rel="stylesheet" />
    <link href="{{asset('css/toastr.min.css')}}" rel="stylesheet">
@endsection

@section('content')
    <h3>Editar Reserva</h3>
    <form action="{{ route('reservas.update', $reserva) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label>Cliente *</label>
                <select name="cliente_id" class="form-select select2" required>
                    <option value="">Seleccione</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ $c->id == $reserva->cliente_id ? 'selected' : '' }}>
                            {{ $c->nombre_cliente }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Monto *</label>
                <input type="number" step="0.01" name="monto" class="form-control" value="{{ old('monto', $reserva->monto) }}" required>
            </div>

            <div class="col-md-4">
                <label>Fecha *</label>
                <input type="date" name="fecha_reserva" class="form-control" value="{{ old('fecha_reserva', \Carbon\Carbon::parse($reserva->fecha_reserva)->format('Y-m-d')) }}" required>
            </div>

            <div class="col-md-4">
                <label>Caja *</label>
                <select name="caja_id" class="form-select" required>
                    @foreach($cajas as $c)
                        <option value="{{ $c->id }}" {{ $c->id == $reserva->caja_id ? 'selected' : '' }}>
                            {{ $c->nombre }} ({{ $c->tipo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control">{{ old('observaciones', $reserva->observaciones) }}</textarea>
            </div>

            <!-- Campo de voucher -->
            <div class="col-12">
                <label class="form-label">Comprobante de pago (voucher):</label>
                @if($reserva->voucher)
                    <div class="mb-3">
                        <label class="form-label">Voucher actual:</label>
                        <img src="{{ asset('storage/' . $reserva->voucher) }}" 
                            alt="Voucher de reserva" 
                            class="img-fluid rounded shadow-sm" 
                            style="max-height: 200px; max-width: 300px; object-fit: cover; border: 1px solid #dee2e6;">
                            <a href="{{ asset('storage/' . $reserva->voucher) }}" target="_blank" class="">Ver Otra ventana</a>
                    </div>
                    
                    @else
                    
                    <div class="mb-3">
                        <label class="form-label">Voucher actual:</label>
                        <div class="border rounded p-3 text-center text-muted" style="max-height: 200px; max-width: 300px; display: flex; align-items: center; justify-content: center;">
                            📷 No se ha subido ningún voucher
                        </div>
                    </div>
                @endif
                <div class="d-flex flex-column align-items-start">
                    <!-- Botones para cámara y explorador -->
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCamaraEdit">
                            📷 Tomar Foto
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" id="btnCargarFotoEdit">
                            📁 Cargar Foto
                        </button>
                    </div>
                    
                    <!-- Input file oculto para cámara -->
                    <input type="file" name="voucher" id="voucherInputEdit" class="form-control d-none" accept="image/*" capture="environment">
                    
                    <!-- Input file oculto para explorador -->
                    <input type="file" name="voucher_explorador" id="voucherExploradorInputEdit" class="form-control d-none" accept="image/*">
                    
                    <!-- Vista previa -->
                    <div id="vistaPreviaEdit" class="mt-2" style="display:none; width: 180px; height: 180px; border: 1px dashed #ccc; border-radius: 8px; overflow: hidden; position: relative;">
                        <img id="imgPreviaEdit" src="" alt="Vista previa" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" class="btn btn-danger btn-sm" id="btnEliminarFotoEdit" style="position: absolute; top: 4px; right: 4px; padding: 2px 6px;">
                            ✕
                        </button>
                    </div>
                </div>
                <small class="text-muted">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">💾 Actualizar Reserva</button>
            <a href="{{ route('reservas.index') }}" class="btn btn-secondary">↩️ Volver</a>
        </div>
    </form>
@endsection

@section('scripts')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/toastr.min.js')}}"></script>
    <script src="{{ asset('js/select2-focus.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

            // === Manejo de cámara y vista previa para edición ===

            // Tomar foto con cámara
            document.getElementById('btnCamaraEdit').addEventListener('click', function() {
                document.getElementById('voucherInputEdit').click();
            });

            // Cargar foto desde explorador
            document.getElementById('btnCargarFotoEdit').addEventListener('click', function() {
                document.getElementById('voucherExploradorInputEdit').click();
            });

            // Procesar imagen desde cámara
            document.getElementById('voucherInputEdit').addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;
                await procesarImagenEdit(file);
            });

            // Procesar imagen desde explorador
            document.getElementById('voucherExploradorInputEdit').addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;
                await procesarImagenEdit(file);
            });

            // Función para procesar y comprimir imagen
            async function procesarImagenEdit(file) {
                // Mostrar vista previa temporal
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('imgPreviaEdit').src = event.target.result;
                    document.getElementById('vistaPreviaEdit').style.display = 'block';
                };
                reader.readAsDataURL(file);

                // === COMPRESIÓN DE IMAGEN ===
                const compressedFile = await compressImageEdit(file, 0.6);
                replaceFileInputEdit(compressedFile);
            }

            // Función para comprimir imagen con <canvas>
            function compressImageEdit(file, quality = 0.6) {
                return new Promise((resolve) => {
                    const img = new Image();
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        img.src = e.target.result;
                    };
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const MAX_WIDTH = 1024;
                        const scale = Math.min(MAX_WIDTH / img.width, 1);
                        canvas.width = img.width * scale;
                        canvas.height = img.height * scale;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                        canvas.toBlob(
                            (blob) => {
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
            function replaceFileInputEdit(newFile) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                document.getElementById('voucherInputEdit').files = dataTransfer.files;
            }

            // Eliminar foto
            document.getElementById('btnEliminarFotoEdit').addEventListener('click', function() {
                document.getElementById('voucherInputEdit').value = '';
                document.getElementById('voucherExploradorInputEdit').value = '';
                document.getElementById('vistaPreviaEdit').style.display = 'none';
            });
        });
    </script>
@endsection