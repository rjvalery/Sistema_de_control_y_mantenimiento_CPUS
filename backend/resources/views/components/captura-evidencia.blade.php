@props(['name' => 'evidencia', 'label' => 'Evidencia Fotográfica', 'onChange' => 'manejarSeleccionFoto'])

<div class="col-12">
    <label class="form-label fw-bold" id="lbl_evidencia">
        <i class="fa-solid fa-camera me-1 text-primary"></i> {{ $label }} <span id="foto_asterisco" class="text-danger">*</span>
    </label>
    
    <div class="p-3 border rounded text-center bg-light" style="border-style: dashed !important; border-width: 2px !important; border-color: #0d6efd !important;">
        <!-- Input principal para validación del formulario -->
        <input type="file" name="{{ $name }}" id="foto_equipo" 
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
                       onchange="{{ $onChange }}(this)">
            </div>

            <!-- Botón Galería con input nativo superpuesto -->
            <div class="position-relative d-inline-block">
                <button type="button" class="btn btn-outline-secondary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                    <i class="fa-solid fa-images me-2"></i> Galería / Archivos
                </button>
                <input type="file" id="foto_galeria_diagnostico" accept="image/*" 
                       style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                       onchange="{{ $onChange }}(this)">
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
                <input type="file" id="foto_respaldo_diagnostico" accept="image/*" class="form-control form-control-sm" onchange="{{ $onChange }}(this)">
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
