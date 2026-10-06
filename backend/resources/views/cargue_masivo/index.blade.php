@extends('layouts.app')

@section('title', 'Módulo de Inventario y Cargue Masivo')
@section('container_class', 'container-fluid px-3 px-xl-4 py-2')

@section('styles')
<style>
    .upload-box {
        border: 2px dashed #0d6efd;
        border-radius: 12px;
        background-color: #f8faff;
        transition: all 0.2s ease-in-out;
        padding: 2.2rem 1.5rem;
        text-align: center;
        cursor: pointer;
    }
    .upload-box:hover {
        background-color: #eef4ff;
        border-color: #0b5ed7;
        transform: translateY(-2px);
    }
    .table-inventario-sticky thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        <!-- ENCABEZADO DE MÓDULO UNIFICADO -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                <h1 class="h3 fw-bold text-dark mb-1">
                    <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Cargue Masivo de Equipos
                </h1>
                <p class="text-muted mb-0">
                    Importación masiva de planillas Excel/CSV a <code>inventario_general</code>.
                </p>
            </div>
            <div class="mt-2 mt-md-0 d-flex flex-wrap gap-2">
                <a href="{{ route('cargue-masivo.plantilla') }}" class="btn btn-success btn-sm fw-semibold">
                    <i class="fa-solid fa-file-excel me-1"></i> Plantilla Excel/CSV
                </a>
                @can('inventario.ver')
                <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Ver Inventario General
                </a>
                @endcan
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('msg') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="row g-4">
                
                <!-- FORMULARIO DE CARGUE DIRECTO -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Subir Archivo para Inserción en Base de Datos
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <form action="{{ route('cargue-masivo.procesar') }}" method="POST" enctype="multipart/form-data" id="formCargueMasivo">
                                @csrf
                                <input type="hidden" name="num_traslado" id="num_traslado">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Archivo Excel (.xlsx, .xls) o CSV (.csv) *</label>
                                    <div class="upload-box" id="upload-box-container" onclick="document.getElementById('archivo_csv').click();">
                                        <i class="fa-solid fa-file-excel display-4 text-success mb-3" id="upload-icon"></i>
                                        <h6 class="fw-bold text-dark mb-1" id="file-label">Haz clic para seleccionar tu archivo Excel o CSV</h6>
                                        <p class="small text-muted mb-0" id="file-subtext">Admite libros de Excel (.xlsx, .xls) y archivos delimitados (.csv, .txt).</p>
                                        <input type="file" name="archivo_csv" id="archivo_csv" class="d-none" accept=".xlsx, .xls, .csv, .txt, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, text/csv, text/plain" required onchange="mostrarNombreArchivo(this)">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <small class="text-muted">
                                        <i class="fa-solid fa-info-circle me-1"></i>Tabla de destino: <code>inventario_general</code>
                                    </small>
                                    <button type="button" class="btn btn-primary px-4 py-2 fw-bold" id="btnProcesar" onclick="abrirModalTraslado()">
                                        <i class="fa-solid fa-upload me-2"></i>Importar a Base de Datos
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- INSTRUCCIONES Y GUÍA -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-list-check text-success me-2"></i>Estructura de Columnas
                            </h5>
                            <a href="{{ route('cargue-masivo.plantilla') }}" class="btn btn-sm btn-outline-success">
                                <i class="fa-solid fa-download me-1"></i>Plantilla
                            </a>
                        </div>
                        <div class="card-body p-4">
                            <p class="small text-muted mb-3">
                                Estructura de 8 columnas adaptada a la plantilla oficial <strong>Formato en Cubic</strong>:
                            </p>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered small mb-3">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Columna (Excel/CSV)</th>
                                            <th>Tipo</th>
                                            <th>Ejemplo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>Identificador 1</code></td>
                                            <td><span class="badge bg-danger-subtle text-danger">Requerido*</span></td>
                                            <td>ACT-10021</td>
                                        </tr>
                                        <tr>
                                            <td><code>Identificador 2</code></td>
                                            <td><span class="badge bg-danger-subtle text-danger">Requerido*</span></td>
                                            <td>SN-MBP99201</td>
                                        </tr>
                                        <tr>
                                            <td><code>Ref. Principal</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>MacBook Pro 16 M1</td>
                                        </tr>
                                        <tr>
                                            <td><code>Descripción</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Laptop Apple Corporativo</td>
                                        </tr>
                                        <tr>
                                            <td><code>Zona Origen</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Sede Central</td>
                                        </tr>
                                        <tr>
                                            <td><code>Ubicación Origen</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Piso 3 - Puesto 302</td>
                                        </tr>
                                        <tr>
                                            <td><code>Verificado</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Verificado / Pendiente</td>
                                        </tr>
                                        <tr>
                                            <td><code>Observaciones</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Equipo en buen estado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted d-block"><em>* Se requiere al menos Identificador 1 o Identificador 2 para procesar la fila.</em></small>
                        </div>
                    </div>
                </div>

            </div>


    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN CON NÚMERO DE TRASLADO -->
<div class="modal fade" id="modalConfirmarCargue" tabindex="-1" aria-labelledby="modalCargueLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="modalCargueLabel">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i>Confirmación de Cargue Masivo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-primary-subtle border border-primary-subtle d-flex align-items-center mb-4 p-3 rounded">
                    <i class="fa-solid fa-file-excel fs-2 text-success me-3" id="modal-archivo-icono"></i>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate" id="modal-archivo-nombre">archivo.xlsx</div>
                        <small class="text-muted" id="modal-archivo-tamano">0 KB</small>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="input_num_traslado" class="form-label fw-bold text-dark">
                        Número de Traslado *
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary"><i class="fa-solid fa-hashtag"></i></span>
                        <input type="text" id="input_num_traslado" class="form-control form-control-lg fw-bold" placeholder="Ej: TRAS-2026-001 o N° de Guía" required autocomplete="off">
                    </div>
                    <div class="invalid-feedback text-danger small mt-1" id="error_num_traslado" style="display: none;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>El número de traslado es obligatorio para confirmar la subida.
                    </div>
                    <div class="form-text mt-2 text-muted small">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>Ingresa el número de traslado o remisión con el que llegaron estos equipos para asociarlos al lote.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-3">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm" id="btnConfirmarSubida" onclick="confirmarYSubir()">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Confirmar y Subir al Sistema
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let modalCargueInstance = null;

function mostrarNombreArchivo(input) {
    const label = document.getElementById('file-label');
    const icon = document.getElementById('upload-icon');
    const subtext = document.getElementById('file-subtext');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        if (icon) {
            if (ext === 'xlsx' || ext === 'xls') {
                icon.className = 'fa-solid fa-file-excel display-4 text-success mb-3';
            } else {
                icon.className = 'fa-solid fa-file-csv display-4 text-primary mb-3';
            }
        }
        label.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Archivo seleccionado: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
        if (subtext) subtext.innerText = 'Archivo cargado y listo para ser procesado.';
    } else {
        label.innerText = 'Haz clic para seleccionar tu archivo Excel o CSV';
        if (icon) icon.className = 'fa-solid fa-file-excel display-4 text-success mb-3';
        if (subtext) subtext.innerText = 'Admite libros de Excel (.xlsx, .xls) y archivos delimitados (.csv, .txt).';
    }
}

function abrirModalTraslado() {
    const fileInput = document.getElementById('archivo_csv');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Por favor selecciona primero un archivo Excel (.xlsx, .xls) o CSV antes de continuar.');
        fileInput.click();
        return;
    }

    const file = fileInput.files[0];
    const ext = file.name.split('.').pop().toLowerCase();
    const modalIcon = document.getElementById('modal-archivo-icono');
    if (modalIcon) {
        if (ext === 'xlsx' || ext === 'xls') {
            modalIcon.className = 'fa-solid fa-file-excel fs-2 text-success me-3';
        } else {
            modalIcon.className = 'fa-solid fa-file-csv fs-2 text-primary me-3';
        }
    }
    document.getElementById('modal-archivo-nombre').innerText = file.name;
    document.getElementById('modal-archivo-tamano').innerText = (file.size / 1024).toFixed(1) + ' KB';

    const inputTraslado = document.getElementById('input_num_traslado');
    inputTraslado.classList.remove('is-invalid');
    document.getElementById('error_num_traslado').style.display = 'none';

    if (!modalCargueInstance) {
        modalCargueInstance = new bootstrap.Modal(document.getElementById('modalConfirmarCargue'));
    }
    modalCargueInstance.show();

    setTimeout(() => {
        inputTraslado.focus();
        inputTraslado.select();
    }, 450);
}

function confirmarYSubir() {
    const inputTraslado = document.getElementById('input_num_traslado');
    const valor = inputTraslado.value.trim();

    if (valor === '') {
        inputTraslado.classList.add('is-invalid');
        document.getElementById('error_num_traslado').style.display = 'block';
        inputTraslado.focus();
        return;
    }

    inputTraslado.classList.remove('is-invalid');
    document.getElementById('error_num_traslado').style.display = 'none';

    // Asignar al formulario oculto
    document.getElementById('num_traslado').value = valor;

    // Cambiar estado del botón modal y botón principal
    const btnModal = document.getElementById('btnConfirmarSubida');
    btnModal.disabled = true;
    btnModal.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Subiendo al sistema...';

    const btnPrincipal = document.getElementById('btnProcesar');
    btnPrincipal.disabled = true;
    btnPrincipal.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Procesando...';

    // Enviar el formulario
    document.getElementById('formCargueMasivo').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const inputTraslado = document.getElementById('input_num_traslado');
    if (inputTraslado) {
        inputTraslado.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmarYSubir();
            }
        });
    }

    const dropZone = document.getElementById('upload-box-container');
    const fileInput = document.getElementById('archivo_csv');
    if (dropZone && fileInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('border-success', 'bg-light');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-success', 'bg-light');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                fileInput.files = dt.files;
                mostrarNombreArchivo(fileInput);
            }
        }, false);
    }
});
</script>
@endsection
