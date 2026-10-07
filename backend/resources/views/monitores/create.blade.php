@extends('layouts.app')

@section('title', 'Reacondicionamiento de Monitores')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-desktop me-2"></i>Reacondicionamiento de Monitores - Formulario de Diagnóstico</h5>
            </div>
            <div class="card-body p-4">

                <form id="formMonitor" action="{{ route('monitores.store') }}" method="POST">
                    @csrf
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del analista *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary"><i class="fa-solid fa-user-check"></i></span>
                                <input type="text" name="nombre_analista" id="nombre_analista" class="form-control bg-light fw-semibold" value="{{ auth()->user()->name ?? auth()->user()->nombre }}" readonly required>
                            </div>
                            <div class="form-text text-muted small"><i class="fa-solid fa-lock me-1 text-success"></i>Sincronizado automáticamente con tu sesión activa.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de Traslado *</label>
                            <input type="text" name="numero_traslado" id="numero_traslado" class="form-control" value="{{ old('numero_traslado') }}" placeholder="Ej: 123456" readonly required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Serial del equipo *</label>
                            <div class="input-group">
                                <input type="text" name="serial" id="serial" class="form-control" value="{{ old('serial') }}" placeholder="Ej: SN123456" required autocomplete="off" autofocus>
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
                                <option value="diagnostico" {{ old('tipo_gestion') == 'diagnostico' ? 'selected' : '' }}>Diagnóstico</option>
                                <option value="novedad" {{ old('tipo_gestion') == 'novedad' ? 'selected' : '' }}>Novedad</option>
                                <option value="baja" {{ old('tipo_gestion') == 'baja' ? 'selected' : '' }}>Baja</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado actual del equipo *</label>
                            <select name="estado_actual" id="estado_actual" class="form-select" required>
                                <option value="" disabled {{ old('estado_actual') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="funcional" {{ old('estado_actual') == 'funcional' ? 'selected' : '' }}>Funcional</option>
                                <option value="garantia" {{ old('estado_actual') == 'garantia' ? 'selected' : '' }}>Garantía</option>
                                <option value="baja" {{ old('estado_actual') == 'baja' ? 'selected' : '' }}>Baja</option>
                            </select>
                        </div>

                        <!-- SECCIÓN CONDICIONAL A: DIAGNÓSTICO -->
                        <div class="col-12 d-none" id="seccion_diagnostico">
                            <div class="p-3 bg-light rounded border border-info-subtle">
                                <div class="fw-bold text-info mb-2">
                                    <i class="fa-solid fa-stethoscope me-2"></i>Información de Diagnóstico
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">¿Energiza? *</label>
                                        <select name="energiza" id="energiza" class="form-select">
                                            <option value="" disabled {{ old('energiza') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                            <option value="1" {{ old('energiza') == '1' ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('energiza') == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">¿Da video? *</label>
                                        <select name="da_video" id="da_video" class="form-select">
                                            <option value="" disabled {{ old('da_video') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                            <option value="1" {{ old('da_video') == '1' ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('da_video') == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL B: NOVEDAD -->
                        <div class="col-12 d-none" id="seccion_novedad">
                            <div class="p-3 bg-light rounded border border-warning-subtle">
                                <label class="form-label fw-bold" for="motivo_novedad">
                                    <i class="fa-solid fa-circle-exclamation text-warning me-1"></i> Escriba la novedad del equipo *
                                </label>
                                <textarea name="motivo_novedad" id="motivo_novedad" class="form-control" rows="2" placeholder="Detalle la novedad...">{{ old('motivo_novedad') }}</textarea>
                            </div>
                        </div>



                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold">Observaciones adicionales (Opcional)</label>
                            <input type="text" name="observaciones" class="form-control" placeholder="Cualquier otro detalle técnico..." value="{{ old('observaciones') }}">
                        </div>

                        <div class="col-12 mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1 py-2 fs-6 fw-bold" id="btnGuardar">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Monitor
                            </button>
                            <a href="{{ route('monitores.index') }}" class="btn btn-secondary py-2 px-4 fw-bold">
                                <i class="fa-solid fa-arrow-left me-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serialInput = document.getElementById('serial');
        const trasladoInput = document.getElementById('numero_traslado');
        const spinnerPlaca = document.getElementById('spinner_placa');
        const inventarioFeedback = document.getElementById('inventario_feedback');
        
        let timeoutBuscador = null;
        serialInput.addEventListener('keyup', function(e) {
            clearTimeout(timeoutBuscador);
            const val = this.value.trim();
            if(val.length > 3) {
                timeoutBuscador = setTimeout(() => buscarEquipoAPI(val), 800);
            }
        });
        
        serialInput.addEventListener('blur', function() {
            const val = this.value.trim();
            if(val.length > 3) buscarEquipoAPI(val);
        });

        function buscarEquipoAPI(serial) {
            spinnerPlaca.classList.remove('d-none');
            fetch(`{{ url('/monitores/buscar') }}/${encodeURIComponent(serial)}`)
                .then(res => res.json())
                .then(data => {
                    if(data.encontrado) {
                        inventarioFeedback.innerHTML = `<span class="text-success small fw-bold"><i class="fa-solid fa-check-circle me-1"></i> Equipo encontrado: ${data.modelo || 'Genérico'}</span>`;
                        
                        if(data.numero_traslado) {
                            trasladoInput.value = data.numero_traslado;
                            trasladoInput.removeAttribute('readonly');
                        } else {
                            trasladoInput.value = '';
                            trasladoInput.removeAttribute('readonly');
                        }
                    } else {
                        inventarioFeedback.innerHTML = `<span class="text-warning small fw-bold"><i class="fa-solid fa-circle-info me-1"></i> Equipo no en inventario. Continúe manual.</span>`;
                        trasladoInput.removeAttribute('readonly');
                    }
                })
                .catch(() => {
                    inventarioFeedback.innerHTML = '<span class="text-danger small">Error conectando al servidor.</span>';
                    trasladoInput.removeAttribute('readonly');
                })
                .finally(() => {
                    spinnerPlaca.classList.add('d-none');
                });
        }

        // Para ejecutar al cargar la página en caso de haber errores de validación (old)
        evaluarGestion();
        
        document.getElementById('formMonitor').addEventListener('submit', function() {
            const btn = document.getElementById('btnGuardar');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';
            btn.disabled = true;
        });
    });

    function evaluarGestion() {
        const gestion = document.getElementById('tipo_gestion').value;
        const seccionDiagnostico = document.getElementById('seccion_diagnostico');
        const seccionNovedad = document.getElementById('seccion_novedad');
        const estadoActual = document.getElementById('estado_actual');
        const energiza = document.getElementById('energiza');
        const daVideo = document.getElementById('da_video');
        const motivoNovedad = document.getElementById('motivo_novedad');
        
        // Ocultar todos
        seccionDiagnostico.classList.add('d-none');
        seccionNovedad.classList.add('d-none');
        
        // Quitar requeridos
        energiza.removeAttribute('required');
        daVideo.removeAttribute('required');
        motivoNovedad.removeAttribute('required');

        if(gestion === 'diagnostico') {
            seccionDiagnostico.classList.remove('d-none');
            energiza.setAttribute('required', 'required');
            daVideo.setAttribute('required', 'required');
        } else if(gestion === 'novedad') {
            seccionNovedad.classList.remove('d-none');
            motivoNovedad.setAttribute('required', 'required');
        } else if(gestion === 'baja') {
            estadoActual.value = 'baja';
        }
    }
</script>
@endsection
