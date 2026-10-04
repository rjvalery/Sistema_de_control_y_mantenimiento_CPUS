@extends('layouts.app')

@section('title', 'Diagnóstico y Garantías CPUs')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-microchip me-2"></i>Formulario CPU Garantías</h5>
            </div>
            <div class="card-body p-4">

                <form id="formGarantias" enctype="multipart/form-data" onsubmit="event.preventDefault(); return false;">
                    @csrf
                    <input type="hidden" name="timestamp_registro" value="{{ old('timestamp_registro', now()->format('Y-m-d H:i:s')) }}">
                    <div class="row g-3">
                        
                        <!-- 1. Analista y Traslado -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del analista *</label>
                            @if(Auth::check() && Auth::user()->rol === 'analista')
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="fa-solid fa-user-check"></i></span>
                                    <input type="text" name="nombre_analista" id="nombre_analista" class="form-control bg-light fw-semibold" value="{{ Auth::user()->nombre }}" readonly required>
                                </div>
                                <div class="form-text text-muted small"><i class="fa-solid fa-lock me-1 text-success"></i>Sincronizado automáticamente con tu sesión activa.</div>
                            @else
                                <select name="nombre_analista" id="nombre_analista" class="form-select" required>
                                    <option value="" disabled selected>-- Seleccione Analista --</option>
                                    @php $encontrado = false; @endphp
                                    @foreach($analistas as $a)
                                        @php 
                                            $esActual = Auth::check() && (trim($a->nombre) === trim(Auth::user()->nombre));
                                            if ($esActual) $encontrado = true;
                                        @endphp
                                        <option value="{{ $a->nombre }}" {{ $esActual ? 'selected' : '' }}>
                                            {{ $a->nombre }} {{ $esActual ? '(Tu sesión)' : '' }}
                                        </option>
                                    @endforeach
                                    @if(!$encontrado && Auth::check())
                                        <option value="{{ Auth::user()->nombre }}" selected>{{ Auth::user()->nombre }} (Tu sesión)</option>
                                    @endif
                                </select>
                                <div class="form-text text-muted small">Selecciona el analista o usa tu sesión actual.</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de Traslado *</label>
                            <input type="text" name="num_traslado" id="num_traslado" class="form-control" placeholder="Ej: 123456" required>
                        </div>

                        <!-- 2. Placa e ID -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Placa ID o Serial del equipo *</label>
                            <div class="input-group">
                                <input type="text" name="placa_id" id="placa_id" class="form-control" placeholder="Ej: B123456 o Serial" required autocomplete="off">
                                <span class="input-group-text d-none" id="spinner_placa">
                                    <i class="fa-solid fa-spinner fa-spin text-primary"></i>
                                </span>
                            </div>
                            <div id="inventario_feedback" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de gestión *</label>
                            <select name="tipo_gestion" id="tipo_gestion" class="form-select" required onchange="evaluarGestion()">
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Diagnostico">Diagnóstico</option>
                                <option value="Intervencion">Intervención</option>
                                <option value="Novedad">Novedad</option>
                                <option value="Baja">Baja</option>
                                <option value="IT">IT</option>
                            </select>
                        </div>

                        <!-- CAMPOS CONDICIONALES -->
                        
                        <!-- A. DIAGNÓSTICO -->
                        <div id="seccion_diagnostico" class="col-12 d-none p-3 bg-light rounded border">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">¿Energiza? *</label>
                                    <select name="energiza" id="energiza" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">¿Da video? *</label>
                                    <select name="da_video" id="da_video" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Estado actual del equipo *</label>
                                    <select name="estado_actual" id="estado_actual" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Funcional" {{ old('estado_actual') == 'Funcional' ? 'selected' : '' }}>Funcional</option>
                                        <option value="Garantia" {{ old('estado_actual') == 'Garantia' ? 'selected' : '' }}>Garantía</option>
                                        <option value="Pendiente Repuesto" {{ old('estado_actual') == 'Pendiente Repuesto' ? 'selected' : '' }}>Pendiente Repuesto</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Solución o Trámite de Garantías</label>
                                    <textarea name="solucion_garantias" id="solucion_garantias" class="form-control" rows="2" placeholder="Detalle la solución brindada o trámite de garantías...">{{ old('solucion_garantias') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- B. INTERVENCIÓN -->
                        <div id="seccion_intervencion" class="col-12 d-none p-3 bg-light rounded border">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">¿Qué va a intervenir? *</label>
                                    <select name="que_va_intervenir" id="que_va_intervenir" class="form-select" onchange="evaluarIntervencion(this.value)">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Disco" {{ old('que_va_intervenir') == 'Disco' ? 'selected' : '' }}>Disco</option>
                                        <option value="RAM" {{ old('que_va_intervenir') == 'RAM' ? 'selected' : '' }}>RAM</option>
                                        <option value="Pila de BIOS" {{ old('que_va_intervenir') == 'Pila de BIOS' ? 'selected' : '' }}>Pila de BIOS</option>
                                        <option value="Disco;RAM" {{ old('que_va_intervenir') == 'Disco;RAM' ? 'selected' : '' }}>Disco;RAM</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Origen de pieza *</label>
                                    <select name="origen_pieza" id="origen_pieza" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Nuevo" {{ old('origen_pieza') == 'Nuevo' ? 'selected' : '' }}>Nuevo</option>
                                        <option value="Reacondicionado" {{ old('origen_pieza') == 'Reacondicionado' ? 'selected' : '' }}>Reacondicionado</option>
                                    </select>
                                </div>
                                <div id="campos_ram" class="col-12 d-none">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Tipo de RAM</label>
                                            <input type="text" name="tipo_ram" id="tipo_ram" class="form-control" value="{{ old('tipo_ram') }}" placeholder="Ej: DDR4, DDR3">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Marca de RAM</label>
                                            <input type="text" name="marca_ram" id="marca_ram" class="form-control" value="{{ old('marca_ram') }}" placeholder="Ej: Kingston, Crucial">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Capacidad RAM</label>
                                            <input type="text" name="capacidad_ram" id="capacidad_ram" class="form-control" value="{{ old('capacidad_ram') }}" placeholder="Ej: 8GB, 16GB">
                                        </div>
                                    </div>
                                </div>
                                <div id="campos_disco" class="col-12 d-none">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Tipo de Disco</label>
                                            <input type="text" name="tipo_disco" id="tipo_disco" class="form-control" value="{{ old('tipo_disco') }}" placeholder="Ej: SSD, HDD, NVMe">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Marca Disco</label>
                                            <input type="text" name="marca_disco" id="marca_disco" class="form-control" value="{{ old('marca_disco') }}" placeholder="Ej: Western Digital">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Capacidad Disco</label>
                                            <input type="text" name="capacidad_disco" id="capacidad_disco" class="form-control" value="{{ old('capacidad_disco') }}" placeholder="Ej: 500GB, 1TB">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Serial del disco</label>
                                            <input type="text" name="serial_disco" id="serial_disco" class="form-control" value="{{ old('serial_disco') }}" placeholder="Escriba el serial...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- C. NOVEDAD -->
                        <div id="seccion_novedad" class="col-12 d-none p-3 bg-light rounded border">
                            <label class="form-label fw-bold">Escriba la novedad del equipo *</label>
                            <textarea name="descripcion_novedad" id="descripcion_novedad" class="form-control" rows="3" placeholder="Detalle la novedad...">{{ old('descripcion_novedad') }}</textarea>
                        </div>

                        <!-- D. BAJA -->
                        <div id="seccion_baja" class="col-12 d-none p-3 bg-light rounded border border-danger-subtle">
                            <div class="fw-bold text-danger mb-2">
                                <i class="fa-solid fa-trash-can me-2"></i>Información de Baja del Equipo
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Motivo Baja *</label>
                                    <select name="motivo_baja" id="motivo_baja" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Obsoleto" {{ old('motivo_baja') == 'Obsoleto' ? 'selected' : '' }}>Obsoleto</option>
                                        <option value="No energiza" {{ old('motivo_baja') == 'No energiza' ? 'selected' : '' }}>No energiza</option>
                                        <option value="Baja total" {{ old('motivo_baja') == 'Baja total' ? 'selected' : '' }}>Baja total</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Serial del disco duro</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-hard-drive"></i></span>
                                        <input type="text" name="serial_disco_baja" id="serial_disco_baja" class="form-control" value="{{ old('serial_disco_baja') }}" placeholder="Escriba el serial del disco duro...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- E. GESTIÓN IT -->
                        <div id="seccion_it" class="col-12 d-none p-3 bg-light rounded border border-primary-subtle">
                            <div class="fw-bold text-primary mb-2">
                                <i class="fa-solid fa-laptop-code me-2"></i>Información de Gestión IT
                            </div>
                            <label class="form-label fw-bold">Escriba la información de la gestión IT *</label>
                            <textarea name="novedad_it" id="novedad_it" class="form-control" rows="3" placeholder="Detalle la información o procedimiento realizado en IT...">{{ old('novedad_it') }}</textarea>
                            <input type="hidden" name="descripcion_it" id="descripcion_it">
                        </div>

                        <!-- 3. Ubicación Destino -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Ubicación destino *</label>
                            <select name="ubicacion_destino" id="ubicacion_destino" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione Ubicación --</option>
                                <option value="Sala Dban">Sala Dban</option>
                                <option value="Sala Garantias">Sala Garantías</option>
                                <option value="Almacen">Almacén</option>
                                <option value="Sala Bajas">Sala Bajas</option>
                            </select>
                        </div>

                        <!-- 4. Adjuntar / Tomar Foto -->
                        <div class="col-12" id="contenedor_foto">
                            <label class="form-label fw-bold" id="lbl_evidencia">
                                <i class="fa-solid fa-camera me-1 text-primary"></i> Evidencia Fotográfica <span id="foto_asterisco" class="text-danger">*</span>
                            </label>

                            <div class="p-3 border rounded text-center bg-light" style="border-style: dashed !important; border-width: 2px !important; border-color: #0d6efd !important;">
                                <!-- Input principal para validación del formulario -->
                                <input type="file" name="foto_equipo" id="foto_equipo" 
                                       style="position: absolute; opacity: 0; width: 0.1px; height: 0.1px; overflow: hidden;" 
                                       accept="image/*" required>

                                <div class="d-flex flex-wrap justify-content-center gap-3 mb-2">
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-primary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-camera me-2"></i> Abrir Cámara
                                        </button>
                                        <input type="file" id="foto_camara_diagnostico" accept="image/*" capture="environment" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFoto(this)">
                                    </div>

                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-outline-secondary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-images me-2"></i> Galería / Archivos
                                        </button>
                                        <input type="file" id="foto_galeria_diagnostico" accept="image/*" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFoto(this)">
                                    </div>
                                </div>

                                <div id="upload-label" class="form-text mt-1 text-muted fw-semibold">
                                    Toca un botón para activar la cámara o seleccionar de la galería.
                                </div>

                                <div class="mt-2 text-center">
                                    <a href="javascript:void(0)" class="text-decoration-none small text-muted" onclick="document.getElementById('selector_respaldo').classList.toggle('d-none')">
                                        <i class="fa-solid fa-sliders me-1"></i> ¿Problemas en la tablet? Probar selector directo alternativo
                                    </a>
                                    <div id="selector_respaldo" class="mt-2 d-none">
                                        <input type="file" id="foto_respaldo" accept="image/*" class="form-control form-control-sm" onchange="manejarSeleccionFoto(this)">
                                    </div>
                                </div>

                                <div id="preview-container" class="mt-3 text-center d-none">
                                    <div class="position-relative d-inline-block">
                                        <img id="preview" src="#" alt="Previsualización de la foto" class="img-thumbnail shadow-sm rounded" style="max-height: 240px; max-width: 100%;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle shadow" style="width: 28px; height: 28px; padding: 0;" title="Quitar foto" onclick="limpiarPrevisualizacion()">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Foto lista para enviar
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm" id="btnGuardar" onclick="enviarFormulario()">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- MODAL EMERGENTE DE CONFIRMACIÓN -->
<div class="modal fade" id="modalEmergente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div id="modalIcono" class="display-4 mb-2"></div>
                <h5 class="modal-title fw-bold mb-2" id="modalTitulo"></h5>
                <p class="text-muted small mb-3" id="modalMensaje"></p>
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables de Entorno y Rutas pasadas desde Blade a JS
    window.AppUrls = {
        buscarEquipo: '{{ route("inventario.buscar") }}',
        guardarEquipo: '{{ route("equipos.store") }}'
    };

    let fotoOptimBlob = null;
    let optimizacionPromesa = null;

    function evaluarIntervencion(valor) {
        const camposRam = document.getElementById('campos_ram');
        const camposDisco = document.getElementById('campos_disco');
        if (camposRam) camposRam.classList.add('d-none');
        if (camposDisco) camposDisco.classList.add('d-none');

        const inputs = document.querySelectorAll('#campos_ram input, #campos_disco input');
        inputs.forEach(i => {
            i.required = false;
            i.value = '';
        });

        if (valor === 'RAM' || valor === 'Disco;RAM') {
            if (camposRam) camposRam.classList.remove('d-none');
        }
        if (valor === 'Disco' || valor === 'Disco;RAM') {
            if (camposDisco) camposDisco.classList.remove('d-none');
        }
    }

    function evaluarGestion() {
        const selector = document.getElementById('tipo_gestion');
        if (!selector) return;

        const valor = selector.value;
        const secciones = ['seccion_diagnostico', 'seccion_intervencion', 'seccion_novedad', 'seccion_baja', 'seccion_it'];
        
        secciones.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });

        const selects = document.querySelectorAll('#seccion_diagnostico select, #seccion_intervencion select, #seccion_novedad textarea, #seccion_baja textarea, #seccion_it textarea, #seccion_intervencion input, #seccion_baja input');
        selects.forEach(s => { s.required = false; s.value = ''; });

        if (!valor) return;

        const fotoPrincipal = document.getElementById('foto_equipo');
        const fotoAsterisco = document.getElementById('foto_asterisco');
        const uploadLabel   = document.getElementById('upload-label');

        if (valor === 'Diagnostico') {
            document.getElementById('seccion_diagnostico').classList.remove('d-none');
            document.getElementById('energiza').required = true;
            document.getElementById('da_video').required = true;
            document.getElementById('estado_actual').required = true;
        } else if (valor === 'Intervencion') {
            document.getElementById('seccion_intervencion').classList.remove('d-none');
            document.getElementById('que_va_intervenir').required = true;
            document.getElementById('origen_pieza').required = true;
        } else if (valor === 'Novedad') {
            document.getElementById('seccion_novedad').classList.remove('d-none');
            document.getElementById('descripcion_novedad').required = true;
        } else if (valor === 'Baja') {
            document.getElementById('seccion_baja').classList.remove('d-none');
            document.getElementById('motivo_baja').required = true;
            const selectDestino = document.getElementById('ubicacion_destino');
            if (selectDestino && !selectDestino.value) selectDestino.value = 'Sala Bajas';
        } else if (valor === 'IT') {
            document.getElementById('seccion_it').classList.remove('d-none');
            document.getElementById('descripcion_it').required = true;
        }

        if (valor === 'Baja') {
            if (fotoPrincipal) fotoPrincipal.required = false;
            if (fotoAsterisco) fotoAsterisco.innerHTML = '<span class="badge bg-secondary-subtle text-secondary fw-normal ms-1">(Opcional)</span>';
            if (uploadLabel) uploadLabel.textContent = 'Para equipos en baja la foto es opcional.';
        } else {
            if (fotoPrincipal) fotoPrincipal.required = !fotoOptimBlob;
            if (fotoAsterisco) fotoAsterisco.innerHTML = '<span class="text-danger">*</span>';
            if (uploadLabel && !fotoOptimBlob) uploadLabel.textContent = 'Toma la foto directamente con la cámara o selecciónala de la galería.';
        }
    }

    function manejarSeleccionFoto(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const principal = document.getElementById('foto_equipo');
        
        if (principal) {
            principal.required = false;
            try {
                if (window.DataTransfer) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    principal.files = dt.files;
                }
            } catch (e) {}
        }
        optimizarImagen(file);
        input.value = '';
    }

    function optimizarImagen(file) {
        const previewContainer = document.getElementById('preview-container');
        const preview = document.getElementById('preview');
        const uploadLabel = document.getElementById('upload-label');

        if (!file) { fotoOptimBlob = null; optimizacionPromesa = null; return; }

        const instantUrl = URL.createObjectURL(file);
        if (preview) preview.src = instantUrl;
        if (previewContainer) previewContainer.classList.remove('d-none');
        if (uploadLabel) uploadLabel.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> Optimizando peso...';

        optimizacionPromesa = (async () => {
            try {
                const maxDim = 1000;
                let canvas = document.createElement('canvas');
                let ctx = canvas.getContext('2d');
                let procesado = false;

                if ('createImageBitmap' in window) {
                    try {
                        const bitmap = await createImageBitmap(file, { resizeWidth: maxDim, resizeQuality: 'medium' });
                        canvas.width = bitmap.width; canvas.height = bitmap.height;
                        ctx.drawImage(bitmap, 0, 0);
                        bitmap.close(); procesado = true;
                    } catch (e) { }
                }
                
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.65));
                if (blob && procesado) {
                    fotoOptimBlob = blob;
                    if (uploadLabel) uploadLabel.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Foto lista (${(blob.size / 1024).toFixed(0)} KB)`;
                } else {
                    fotoOptimBlob = file;
                    if (uploadLabel) uploadLabel.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i> Foto cargada original.';
                }
            } catch (err) {
                fotoOptimBlob = file;
            }
            return fotoOptimBlob;
        })();
    }

    function limpiarPrevisualizacion() {
        fotoOptimBlob = null; optimizacionPromesa = null;
        document.getElementById('foto_camara_diagnostico').value = '';
        document.getElementById('foto_galeria_diagnostico').value = '';
        document.getElementById('foto_respaldo').value = '';
        document.getElementById('foto_equipo').value = '';
        document.getElementById('preview').src = '#';
        document.getElementById('preview-container').classList.add('d-none');
        document.getElementById('upload-label').textContent = 'Toma la foto directamente con la cámara o selecciónala de la galería.';
        
        if (document.getElementById('tipo_gestion')?.value !== 'Baja') {
            document.getElementById('foto_equipo').required = true;
        }
    }

    function mostrarModal(icono, titulo, mensaje) {
        document.getElementById('modalIcono').innerHTML = icono;
        document.getElementById('modalTitulo').innerText = titulo;
        document.getElementById('modalMensaje').innerText = mensaje;
        new bootstrap.Modal(document.getElementById('modalEmergente')).show();
    }

    async function enviarFormulario() {
        const form = document.getElementById('formGarantias');
        const tipoGestion = document.getElementById('tipo_gestion')?.value;
        const principal = document.getElementById('foto_equipo');

        if (tipoGestion === 'Baja') {
            if (principal) principal.required = false;
        } else {
            if (principal && !fotoOptimBlob && (!principal.files || !principal.files[0])) principal.required = true;
        }

        if (!form.checkValidity()) { form.reportValidity(); return; }

        const btnSubmit = document.getElementById('btnGuardar');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';

        if (optimizacionPromesa) { try { await optimizacionPromesa; } catch (e) {} }

        const formData = new FormData(form);
        // Laravel CSRF Token handled automatically via form markup @csrf but also good to ensure Headers
        const csrfToken = document.querySelector('input[name="_token"]').value;

        if (fotoOptimBlob) formData.set('foto_equipo', fotoOptimBlob, 'foto_diagnostico.jpg');

        fetch(window.AppUrls.guardarEquipo, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.message && !data.error) {
                mostrarModal('<i class="fa-solid fa-circle-check text-success"></i>', '¡Guardado con éxito!', data.message);
                form.reset(); limpiarPrevisualizacion(); evaluarGestion();
                document.getElementById('inventario_feedback').innerHTML = '';
            } else {
                mostrarModal('<i class="fa-solid fa-circle-xmark text-danger"></i>', 'Error al guardar', data.error || 'Verifica los campos.');
            }
        })
        .catch(() => mostrarModal('<i class="fa-solid fa-triangle-exclamation text-warning"></i>', 'Error de red', 'No se pudo conectar con el servidor.'))
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const placaInput = document.getElementById('placa_id');
        let debounceTimer = null;

        if (placaInput) {
            placaInput.addEventListener('input', function() {
                const val = this.value.trim();
                clearTimeout(debounceTimer);
                
                if (val.length < 3) {
                    document.getElementById('inventario_feedback').innerHTML = '';
                    document.getElementById('spinner_placa').classList.add('d-none');
                    return;
                }

                document.getElementById('spinner_placa').classList.remove('d-none');
                
                debounceTimer = setTimeout(() => {
                    fetch(window.AppUrls.buscarEquipo + '?query=' + encodeURIComponent(val))
                        .then(r => r.json())
                        .then(res => {
                            document.getElementById('spinner_placa').classList.add('d-none');
                            const fd = document.getElementById('inventario_feedback');
                            
                            if (res.encontrado && res.equipo) {
                                const eq = res.equipo;
                                if (eq.intervenido) {
                                    fd.innerHTML = `<div class="alert alert-warning py-2 small shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i><strong>¡YA INTERVENIDO PREVIAMENTE!</strong> por ${eq.analista_intervencion}</div>`;
                                } else {
                                    fd.innerHTML = `<div class="alert alert-success py-2 small shadow-sm"><i class="fa-solid fa-circle-check me-2"></i><strong>Equipo en Inventario (${eq.tipo_equipo}).</strong> Se descontará automáticamente.</div>`;
                                }
                                if(eq.num_traslado && document.getElementById('num_traslado').value === '') {
                                    document.getElementById('num_traslado').value = eq.num_traslado;
                                }
                            } else {
                                fd.innerHTML = `<div class="alert alert-light border py-1 small text-muted"><i class="fa-solid fa-info-circle me-1"></i> No registrado en cargue masivo. Será nuevo.</div>`;
                            }
                        }).catch(() => document.getElementById('spinner_placa').classList.add('d-none'));
                }, 400);
            });
        }
    });
</script>
@endpush
