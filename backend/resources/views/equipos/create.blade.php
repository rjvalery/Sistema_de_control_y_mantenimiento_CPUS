@extends('layouts.app')

@section('title', 'Diagnóstico CPU')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-microchip me-2"></i>Diagnóstico CPU - Formulario de Garantías y Mantenimiento</h5>
            </div>
            <div class="card-body p-4">

                <form id="formGarantias" enctype="multipart/form-data" onsubmit="event.preventDefault(); return false;">
                    @csrf
                    <input type="hidden" name="timestamp_registro" value="{{ old('timestamp_registro', now()->format('Y-m-d H:i:s')) }}">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del analista *</label>
                            @if (auth()->user() && auth()->user()->rol === 'analista')
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="fa-solid fa-user-check"></i></span>
                                    <input type="text" name="nombre_analista" id="nombre_analista" class="form-control bg-light fw-semibold" value="{{ auth()->user()->nombre }}" readonly required>
                                </div>
                                <div class="form-text text-muted small"><i class="fa-solid fa-lock me-1 text-success"></i>Sincronizado automáticamente con tu sesión activa.</div>
                            @else
                                <select name="nombre_analista" id="nombre_analista" class="form-select" required>
                                    <option value="" disabled {{ old('nombre_analista') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                    @if (!empty($analistas))
                                        @foreach ($analistas as $a)
                                            <option value="{{ $a->nombre }}" {{ old('nombre_analista') == $a->nombre ? 'selected' : '' }}>
                                                {{ $a->nombre }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="form-text text-muted small">Selecciona el analista o usa tu sesión actual.</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de Traslado *</label>
                            <input type="text" name="num_traslado" id="num_traslado" class="form-control" value="{{ old('num_traslado') }}" placeholder="Ej: 123456" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Placa ID o Serial del equipo *</label>
                            <div class="input-group">
                                <input type="text" name="placa_id" id="placa_id" class="form-control" value="{{ old('placa_id') }}" placeholder="Ej: B123456 o Serial" required autocomplete="off">
                                <span class="input-group-text d-none" id="spinner_placa">
                                    <i class="fa-solid fa-spinner fa-spin text-primary"></i>
                                </span>
                            </div>
                            <div id="inventario_feedback" class="mt-2"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de gestión *</label>
                            <select name="tipo_gestion" id="tipo_gestion" class="form-select" required onchange="evaluarGestion()">
                                <option value="" disabled {{ old('tipo_gestion') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Diagnostico" {{ old('tipo_gestion') == 'Diagnostico' ? 'selected' : '' }}>Diagnóstico</option>
                                <option value="Intervencion" {{ old('tipo_gestion') == 'Intervencion' ? 'selected' : '' }}>Intervención</option>
                                <option value="Novedad" {{ old('tipo_gestion') == 'Novedad' ? 'selected' : '' }}>Novedad</option>
                                <option value="Baja" {{ old('tipo_gestion') == 'Baja' ? 'selected' : '' }}>Baja</option>
                                <option value="IT" {{ old('tipo_gestion') == 'IT' ? 'selected' : '' }}>IT</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Energiza? *</label>
                            <select name="energiza" id="energiza" class="form-select" required>
                                <option value="" disabled {{ old('energiza') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Si" {{ old('energiza') == 'Si' ? 'selected' : '' }}>Si</option>
                                <option value="No" {{ old('energiza') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Da video? *</label>
                            <select name="da_video" id="da_video" class="form-select" required>
                                <option value="" disabled {{ old('da_video') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Si" {{ old('da_video') == 'Si' ? 'selected' : '' }}>Si</option>
                                <option value="No" {{ old('da_video') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado actual del equipo *</label>
                            <select name="estado_actual" id="estado_actual" class="form-select" required onchange="evaluarEstado(this.value)">
                                <option value="" disabled {{ old('estado_actual') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Funcional" {{ old('estado_actual', $equipo->estado_actual ?? '') === 'Funcional' ? 'selected' : '' }}>Funcional</option>
                                <option value="Garantia" {{ old('estado_actual', $equipo->estado_actual ?? '') === 'Garantia' ? 'selected' : '' }}>Garantía</option>
                                <option value="Pendiente Repuesto" {{ old('estado_actual', $equipo->estado_actual ?? '') === 'Pendiente Repuesto' ? 'selected' : '' }}>Pendiente Repuesto</option>
                                <option value="Baja" {{ old('estado_actual', $equipo->estado_actual ?? '') === 'Baja' ? 'selected' : '' }}>Baja</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ubicación destino *</label>
                            <select name="ubicacion_destino" id="ubicacion_destino" class="form-select" required>
                                <option value="" disabled {{ old('ubicacion_destino') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Sala Dban" {{ old('ubicacion_destino') == 'Sala Dban' ? 'selected' : '' }}>Sala Dban</option>
                                <option value="Sala Garantias" {{ old('ubicacion_destino') == 'Sala Garantias' ? 'selected' : '' }}>Sala Garantías</option>
                                <option value="Almacen" {{ old('ubicacion_destino') == 'Almacen' ? 'selected' : '' }}>Almacén</option>
                                <option value="Sala Bajas" {{ old('ubicacion_destino') == 'Sala Bajas' ? 'selected' : '' }}>Sala Bajas</option>
                            </select>
                        </div>

                        <!-- SECCIÓN CONDICIONAL A: GARANTÍA -->
                        <div class="col-12 d-none" id="seccion_garantia">
                            <div class="p-3 bg-light rounded border border-warning-subtle">
                                <div class="fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-shield-halved text-warning me-2"></i>Información de Solución / Trámite de Garantía
                                </div>
                                <label class="form-label fw-bold" for="solucion_garantias">Solución o Trámite de Garantías</label>
                                <textarea name="solucion_garantias" id="solucion_garantias" class="form-control" rows="2" placeholder="Detalle la solución brindada o trámite de garantías...">{{ old('solucion_garantias') }}</textarea>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL B: INTERVENCIÓN -->
                        <div class="col-12 d-none" id="seccion_intervencion">
                            <div class="p-3 bg-light rounded border border-primary-subtle">
                                <div class="fw-bold text-primary mb-2">
                                    <i class="fa-solid fa-screwdriver-wrench me-2"></i>Detalles de Intervención
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">¿Qué va a intervenir? *</label>
                                        <select name="que_va_intervenir" id="que_va_intervenir" class="form-select">
                                            <option value="" disabled {{ old('que_va_intervenir') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                            <option value="Disco" {{ old('que_va_intervenir') == 'Disco' ? 'selected' : '' }}>Disco Duro / SSD</option>
                                            <option value="RAM" {{ old('que_va_intervenir') == 'RAM' ? 'selected' : '' }}>Memoria RAM</option>
                                            <option value="Pila de BIOS" {{ old('que_va_intervenir') == 'Pila de BIOS' ? 'selected' : '' }}>Pila de BIOS</option>
                                            <option value="Disco;RAM" {{ old('que_va_intervenir') == 'Disco;RAM' ? 'selected' : '' }}>Disco y RAM</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Origen de pieza *</label>
                                        <select name="origen_pieza" id="origen_pieza" class="form-select">
                                            <option value="" disabled {{ old('origen_pieza') === null ? 'selected' : '' }}>-- Seleccione origen de la pieza --</option>
                                            <option value="Nuevo" {{ old('origen_pieza') == 'Nuevo' ? 'selected' : '' }}>Nuevo</option>
                                            <option value="Garantía" {{ old('origen_pieza') == 'Garantía' ? 'selected' : '' }}>Garantía</option>
                                            <option value="Reacondicionado" {{ old('origen_pieza') == 'Reacondicionado' ? 'selected' : '' }}>Reacondicionado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL C: NOVEDAD -->
                        <div class="col-12 d-none" id="seccion_novedad">
                            <div class="p-3 bg-light rounded border border-warning-subtle">
                                <label class="form-label fw-bold" for="descripcion_novedad">
                                    <i class="fa-solid fa-circle-exclamation text-warning me-1"></i> Escriba la novedad del equipo *
                                </label>
                                <textarea name="descripcion_novedad" id="descripcion_novedad" class="form-control" rows="2" placeholder="Detalle la novedad...">{{ old('descripcion_novedad') }}</textarea>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL D: BAJA -->
                        <div class="col-12 d-none" id="seccion_baja">
                            <div class="p-3 bg-light rounded border border-danger-subtle">
                                <div class="fw-bold text-danger mb-2">
                                    <i class="fa-solid fa-trash-can me-2"></i>Información de Baja del Equipo
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Motivo de baja *</label>
                                        <select name="motivo_baja" id="motivo_baja" class="form-select">
                                            <option value="" disabled {{ old('motivo_baja') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                            <option value="Obsoleto" {{ old('motivo_baja') == 'Obsoleto' ? 'selected' : '' }}>Obsoleto</option>
                                            <option value="No energiza" {{ old('motivo_baja') == 'No energiza' ? 'selected' : '' }}>No energiza</option>
                                            <option value="Baja total" {{ old('motivo_baja') == 'Baja total' ? 'selected' : '' }}>Baja total</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold" for="serial_disco_baja">Serial del disco duro</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-hard-drive"></i></span>
                                            <input type="text" name="serial_disco_baja" id="serial_disco_baja" class="form-control" value="{{ old('serial_disco_baja') }}" placeholder="Escriba el serial del disco duro...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL E: GESTIÓN IT -->
                        <div class="col-12 d-none" id="seccion_it">
                            <div class="p-3 bg-light rounded border border-info-subtle">
                                <div class="fw-bold text-info mb-2">
                                    <i class="fa-solid fa-laptop-code me-2"></i>Información de Gestión IT
                                </div>
                                <label class="form-label fw-bold" for="novedad_it">Escriba la información de la gestión IT *</label>
                                <textarea name="novedad_it" id="novedad_it" class="form-control" rows="2" placeholder="Detalle la información o procedimiento realizado en IT...">{{ old('novedad_it') }}</textarea>
                                <input type="hidden" name="descripcion_it" id="descripcion_it">
                            </div>
                        </div>

                        <!-- EVIDENCIA FOTOGRÁFICA (IDÉNTICO AL FORMATO DE SOPLADO) -->
                        <div class="col-12">
                            <label class="form-label fw-bold" id="lbl_evidencia">
                                <i class="fa-solid fa-camera me-1 text-primary"></i> Evidencia Fotográfica <span id="foto_asterisco" class="text-danger">*</span>
                            </label>
                            
                            <div class="p-3 border rounded text-center bg-light" style="border-style: dashed !important; border-width: 2px !important; border-color: #0d6efd !important;">
                                <!-- Input principal para validación del formulario -->
                                <input type="file" name="evidencia" id="foto_equipo" 
                                       style="position: absolute; opacity: 0; width: 0.1px; height: 0.1px; overflow: hidden;" 
                                       accept="image/*">

                                <div class="d-flex flex-wrap justify-content-center gap-3 mb-2">
                                    <!-- Botón Cámara con input nativo superpuesto -->
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-primary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-camera me-2"></i> Abrir Cámara
                                        </button>
                                        <input type="file" id="foto_camara_diagnostico" accept="image/*" capture="environment" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFoto(this)">
                                    </div>

                                    <!-- Botón Galería con input nativo superpuesto -->
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
                                    <a href="javascript:void(0)" class="text-decoration-none small text-muted" onclick="document.getElementById('selector_respaldo_diagnostico').classList.toggle('d-none')">
                                        <i class="fa-solid fa-sliders me-1"></i> ¿Problemas en la tablet? Probar selector directo alternativo
                                    </a>
                                    <div id="selector_respaldo_diagnostico" class="mt-2 d-none">
                                        <input type="file" id="foto_respaldo_diagnostico" accept="image/*" class="form-control form-control-sm" onchange="manejarSeleccionFoto(this)">
                                    </div>
                                </div>

                                <div id="preview-container" class="mt-3 text-center d-none">
                                    <div class="position-relative d-inline-block">
                                        <img id="preview" src="#" alt="Vista previa" class="img-thumbnail shadow-sm rounded" style="max-height: 220px; max-width: 100%;">
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
                            <button type="button" class="btn btn-primary w-100 py-2 fs-6 fw-bold" id="btnGuardar" onclick="enviarFormulario()">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Diagnóstico
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

@section('scripts')
<script>
    window.AppUrls = {
        buscarEquipo: '{{ route("inventario.buscar") }}',
        guardarEquipo: '{{ route("equipos.store") }}'
    };
</script>
<script src="{{ asset('assets/js/equipos_formulario.js') }}?v={{ time() }}"></script>
@endsection
