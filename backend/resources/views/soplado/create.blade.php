@extends('layouts.app')

@section('title', 'Soplado de CPUs')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-wind me-2"></i>Soplado de CPUs - Registro de Mantenimiento</h5>
            </div>
            <div class="card-body p-4">

                <form id="formSoplado" enctype="multipart/form-data" onsubmit="event.preventDefault(); return false;">
                    @csrf
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
                                <div class="form-text text-muted small">Seleccione el analista responsable.</div>
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
                            <label class="form-label fw-bold">¿Detecta disco? *</label>
                            <select name="detecta_disco" id="detecta_disco" class="form-select" required>
                                <option value="" disabled {{ old('detecta_disco') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Si" {{ old('detecta_disco') == 'Si' ? 'selected' : '' }}>Si</option>
                                <option value="No" {{ old('detecta_disco') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Ingresó a la BIOS? *</label>
                            <select name="ingreso_bios" id="ingreso_bios" class="form-select" required>
                                <option value="" disabled {{ old('ingreso_bios') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Si" {{ old('ingreso_bios') == 'Si' ? 'selected' : '' }}>Si</option>
                                <option value="No" {{ old('ingreso_bios') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Se aplicó pasta térmica? *</label>
                            <select name="pasta_termica" id="pasta_termica" class="form-select" required>
                                <option value="" disabled {{ old('pasta_termica') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Si" {{ old('pasta_termica') == 'Si' ? 'selected' : '' }}>Si</option>
                                <option value="No" {{ old('pasta_termica') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">La máquina contenía: *</label>
                            <select name="maquina_contenia" id="maquina_contenia" class="form-select" required>
                                <option value="" disabled {{ old('maquina_contenia') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Polvo" {{ old('maquina_contenia') == 'Polvo' ? 'selected' : '' }}>Polvo</option>
                                <option value="Cucaracha" {{ old('maquina_contenia') == 'Cucaracha' ? 'selected' : '' }}>Cucaracha</option>
                                <option value="Papeles de comida" {{ old('maquina_contenia') == 'Papeles de comida' ? 'selected' : '' }}>Papeles de comida</option>
                                <option value="Humedad / Líquidos" {{ old('maquina_contenia') == 'Humedad / Líquidos' ? 'selected' : '' }}>Humedad / Líquidos</option>
                                <option value="Otro" {{ old('maquina_contenia') == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>

                        <div class="col-12 d-none" id="contenedor_gel_cucarachas">
                            <label class="form-label fw-bold"><i class="fa-solid fa-shield-virus text-warning me-1"></i> ¿Se aplicó gel para cucarachas? *</label>
                            <select name="gel_cucarachas" id="gel_cucarachas" class="form-select">
                                <option value="" disabled {{ old('gel_cucarachas') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                <option value="Si" {{ old('gel_cucarachas') == 'Si' ? 'selected' : '' }}>Si</option>
                                <option value="No" {{ old('gel_cucarachas') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <x-captura-evidencia name="foto_equipo" label="Evidencia Fotográfica" on-change="manejarSeleccionFotoSoplado" />

                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary w-100 py-2 fs-6 fw-bold" id="btnGuardar" onclick="enviarFormulario()">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Soplado
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<x-modal-emergente id="modalEmergente" />
@endsection

@section('scripts')
<script>
    window.AppUrls = {
        buscarEquipo: '{{ route("inventario.buscar") }}',
        guardarSoplado: '{{ route("soplado.store") }}'
    };
</script>
<script src="{{ asset('assets/js/soplado_formulario.js') }}?v={{ time() }}"></script>
@endsection
