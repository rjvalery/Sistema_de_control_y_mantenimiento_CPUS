let fotoOptimBlob = null;
let optimizacionPromesa = null;

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
    });
}

function mostrarModal(icono, titulo, mensaje) {
    const elIcono = document.getElementById('modalIcono');
    const elTitulo = document.getElementById('modalTitulo');
    const elMensaje = document.getElementById('modalMensaje');
    if (elIcono) elIcono.innerHTML = icono;
    if (elTitulo) elTitulo.innerText = titulo;
    if (elMensaje) elMensaje.innerText = mensaje;
    new bootstrap.Modal(document.getElementById('modalEmergente')).show();
}

function evaluarEstado(estado) {
    const secGarantia = document.getElementById('seccion_garantia');
    if (!secGarantia) return;
    if (estado === 'Garantia') {
        secGarantia.classList.remove('d-none');
    } else {
        secGarantia.classList.add('d-none');
    }
}

function evaluarGestion() {
    const selector = document.getElementById('tipo_gestion');
    if (!selector) return;

    const valor = selector.value;
    const secciones = ['seccion_diagnostico_campos', 'seccion_intervencion', 'seccion_novedad', 'seccion_baja', 'seccion_it'];
    
    secciones.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('d-none');
    });

    const fotoPrincipal = document.getElementById('foto_equipo');
    const fotoAsterisco = document.getElementById('foto_asterisco');
    const uploadLabel   = document.getElementById('upload-label');

    if (valor === 'Intervencion') {
        document.getElementById('seccion_intervencion')?.classList.remove('d-none');
        const selInterv = document.getElementById('que_va_intervenir');
        if (selInterv) evaluarIntervencion(selInterv.value);
    } else if (valor === 'Novedad') {
        document.getElementById('seccion_novedad')?.classList.remove('d-none');
    } else if (valor === 'Baja') {
        document.getElementById('seccion_baja')?.classList.remove('d-none');
        const selUbicacion = document.getElementById('ubicacion_destino');
        if (selUbicacion && !selUbicacion.value) {
            selUbicacion.value = 'Sala Bajas';
        }
    } else if (valor === 'IT') {
        document.getElementById('seccion_it')?.classList.remove('d-none');
    } else {
        // Diagnóstico por defecto o seleccionado
        document.getElementById('seccion_diagnostico_campos')?.classList.remove('d-none');
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

function evaluarIntervencion(valor) {
    const camposRam = document.getElementById('campos_ram');
    const camposDisco = document.getElementById('campos_disco');
    if (camposRam) camposRam.classList.add('d-none');
    if (camposDisco) camposDisco.classList.add('d-none');

    if (valor === 'RAM' || valor === 'Disco;RAM') {
        if (camposRam) camposRam.classList.remove('d-none');
    }
    if (valor === 'Disco' || valor === 'Disco;RAM') {
        if (camposDisco) camposDisco.classList.remove('d-none');
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
    const cam = document.getElementById('foto_camara_diagnostico');
    const gal = document.getElementById('foto_galeria_diagnostico');
    const res = document.getElementById('foto_respaldo_diagnostico');
    const pri = document.getElementById('foto_equipo');
    const prv = document.getElementById('preview');
    const con = document.getElementById('preview-container');
    const lbl = document.getElementById('upload-label');

    if (cam) cam.value = '';
    if (gal) gal.value = '';
    if (res) res.value = '';
    if (pri) pri.value = '';
    if (prv) prv.src = '#';
    if (con) con.classList.add('d-none');
    if (lbl) lbl.textContent = 'Toma la foto directamente con la cámara o selecciónala de la galería.';
    
    if (document.getElementById('tipo_gestion')?.value !== 'Baja' && pri) {
        pri.required = true;
    }
}

async function enviarFormulario() {
    const form = document.getElementById('formGarantias');
    const tipoGestion = document.getElementById('tipo_gestion')?.value;
    const principal = document.getElementById('foto_equipo');

    if (tipoGestion === 'Baja') {
        if (principal) principal.required = false;
    } else {
        if (principal && !fotoOptimBlob && (!principal.files || !principal.files[0])) {
            principal.required = true;
        }
    }

    if (!form.checkValidity()) { 
        form.reportValidity(); 
        return; 
    }

    const btnSubmit = document.getElementById('btnGuardar');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';

    if (optimizacionPromesa) { 
        try { await optimizacionPromesa; } catch (e) {} 
    }

    const formData = new FormData(form);
    const csrfToken = document.querySelector('input[name="_token"]').value;

    if (fotoOptimBlob) {
        formData.set('foto_equipo', fotoOptimBlob, 'foto_diagnostico.jpg');
    }

    fetch(window.AppUrls.guardarEquipo, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.message && !data.error) {
            mostrarModal('<i class="fa-solid fa-circle-check text-success"></i>', '¡Guardado con éxito!', data.message);
            form.reset(); 
            limpiarPrevisualizacion(); 
            evaluarGestion();
            const fd = document.getElementById('inventario_feedback');
            if (fd) fd.innerHTML = '';
        } else {
            mostrarModal('<i class="fa-solid fa-circle-xmark text-danger"></i>', 'Error al guardar', data.error || 'Verifica los campos.');
        }
    })
    .catch(() => mostrarModal('<i class="fa-solid fa-triangle-exclamation text-warning"></i>', 'Error de red', 'No se pudo conectar con el servidor.'))
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Diagnóstico';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    evaluarGestion();
    const estadoAct = document.getElementById('estado_actual');
    if (estadoAct) evaluarEstado(estadoAct.value);

    const placaInput = document.getElementById('placa_id');
    const spinnerPlaca = document.getElementById('spinner_placa');
    const feedbackDiv = document.getElementById('inventario_feedback');
    const inputTraslado = document.getElementById('num_traslado');
    let debounceTimer = null;

    if (placaInput) {
        placaInput.addEventListener('input', function() {
            const val = this.value.trim();
            clearTimeout(debounceTimer);
            
            if (val.length < 3) {
                if (feedbackDiv) feedbackDiv.innerHTML = '';
                if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                return;
            }

            if (spinnerPlaca) spinnerPlaca.classList.remove('d-none');
            
            debounceTimer = setTimeout(() => {
                fetch(window.AppUrls.buscarEquipo + '?query=' + encodeURIComponent(val))
                    .then(r => r.json())
                    .then(res => {
                        if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                        if (!feedbackDiv) return;
                        
                        if (res.encontrado && res.equipo) {
                            const eq = res.equipo;
                            if (inputTraslado && !inputTraslado.value && eq.num_traslado) {
                                inputTraslado.value = eq.num_traslado;
                            }

                            const trasladoBadge = eq.num_traslado ? `
                                <div class="mt-1 small text-primary fw-bold">
                                    <i class="fa-solid fa-truck-ramp-box me-1"></i>Traslado de Inventario: <span class="badge bg-primary fs-7 px-2 py-1">${escapeHtml(eq.num_traslado)}</span>
                                </div>
                            ` : '';

                            if (eq.intervenido) {
                                feedbackDiv.innerHTML = `
                                    <div class="alert alert-warning py-2 px-3 mb-0 small border-warning shadow-sm">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fa-solid fa-triangle-exclamation text-warning me-2 fs-5"></i>
                                            <strong>Equipo en Sistema — ¡YA INTERVENIDO PREVIAMENTE!</strong>
                                        </div>
                                        <div class="text-dark">
                                            <strong>Serial:</strong> <code>${escapeHtml(eq.serial || 'N/A')}</code> | 
                                            <strong>Placa:</strong> <code>${escapeHtml(eq.placa_id || 'N/A')}</code> | 
                                            <strong>Equipo:</strong> ${escapeHtml(eq.marca || '')} ${escapeHtml(eq.modelo || '')}
                                        </div>
                                        ${trasladoBadge}
                                        <div class="text-muted mt-1 small">
                                            <i class="fa-regular fa-clock me-1"></i>Intervenido el <strong>${escapeHtml(eq.fecha_intervencion || '')}</strong> en módulo <strong>${escapeHtml(eq.modulo_intervencion || '')}</strong> por <strong>${escapeHtml(eq.analista_intervencion || 'N/A')}</strong>.
                                        </div>
                                    </div>
                                `;
                            } else {
                                feedbackDiv.innerHTML = `
                                    <div class="alert alert-success py-2 px-3 mb-0 small border-success shadow-sm">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fa-solid fa-circle-check text-success me-2 fs-5"></i>
                                            <strong>Equipo sincronizado con Inventario General</strong>
                                        </div>
                                        <div class="text-dark">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">${escapeHtml(eq.tipo_equipo || 'CPU')}</span>
                                            <strong>Serial:</strong> <code class="text-dark fw-bold">${escapeHtml(eq.serial || 'N/A')}</code> | 
                                            <strong>Placa:</strong> <code class="text-dark fw-bold">${escapeHtml(eq.placa_id || 'N/A')}</code>
                                        </div>
                                        <div class="text-muted mt-1">
                                            <strong>Marca / Modelo:</strong> ${escapeHtml(eq.marca || '')} ${escapeHtml(eq.modelo || '')} | 
                                            <strong>Ubicación:</strong> ${escapeHtml(eq.ubicacion || 'Sede')}
                                        </div>
                                        ${trasladoBadge}
                                        <div class="text-success fw-semibold mt-1">
                                            <i class="fa-solid fa-arrow-down-long me-1"></i> Se marcará como intervenido y se descontará del inventario pendiente al guardar.
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            feedbackDiv.innerHTML = `
                                <div class="alert alert-light border py-1 px-2 mb-0 small text-muted">
                                    <i class="fa-solid fa-info-circle me-1 text-secondary"></i> No registrado en cargue masivo previo. Se registrará como equipo nuevo.
                                </div>
                            `;
                        }
                    })
                    .catch(() => {
                        if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                    });
            }, 350);
        });
    }
});
