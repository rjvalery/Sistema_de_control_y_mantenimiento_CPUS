@extends('layouts.app')

@section('title', 'Recepción de Diademas por Lote')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-headset me-2"></i>Recepción y Clasificación de Diademas por Lote</h5>
            </div>
            <div class="card-body p-4">

                <form id="formDiademas" action="{{ route('diademas.store') }}" method="POST">
                    @csrf
                    
                    @if($errors->any())
                        <div class="alert alert-danger rounded border-danger border-start border-4">
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
                            @if (auth()->user() && auth()->user()->rol === 'analista')
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="fa-solid fa-user-check"></i></span>
                                    <input type="text" name="nombre_analista" id="nombre_analista" class="form-control bg-light fw-semibold" value="{{ auth()->user()->nombre ?? auth()->user()->name }}" readonly required>
                                </div>
                                <div class="form-text text-muted small"><i class="fa-solid fa-lock me-1 text-success"></i>Sincronizado automáticamente con tu sesión activa.</div>
                            @else
                                @if(isset($analistas) && count($analistas) > 0)
                                    <select name="nombre_analista" id="nombre_analista" class="form-select" required>
                                        <option value="" disabled {{ old('nombre_analista') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                                        @foreach ($analistas as $a)
                                            <option value="{{ $a->nombre }}" {{ old('nombre_analista') == $a->nombre ? 'selected' : '' }}>{{ $a->nombre }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" name="nombre_analista" id="nombre_analista" class="form-control" value="{{ old('nombre_analista', auth()->user()->nombre ?? auth()->user()->name) }}" required>
                                @endif
                                <div class="form-text text-muted small">Selecciona o escribe el analista.</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de Traslado *</label>
                            <input type="text" name="numero_traslado" class="form-control" value="{{ old('numero_traslado') }}" placeholder="Ej: 123456" required>
                        </div>

                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold">Modo de Escaneo (Seleccione destino) *</label>
                            <div class="d-flex flex-wrap gap-2">
                                <input type="radio" class="btn-check" name="modo_escaneo" id="modo_funcional" value="funcional" checked>
                                <label class="btn btn-outline-success px-4 py-2" for="modo_funcional"><i class="fa-solid fa-check-circle me-2"></i>Ingresar Funcionales</label>

                                <input type="radio" class="btn-check" name="modo_escaneo" id="modo_garantia" value="garantia">
                                <label class="btn btn-outline-warning px-4 py-2" for="modo_garantia"><i class="fa-solid fa-triangle-exclamation me-2"></i>Ingresar Garantías</label>

                                <input type="radio" class="btn-check" name="modo_escaneo" id="modo_baja" value="baja">
                                <label class="btn btn-outline-danger px-4 py-2" for="modo_baja"><i class="fa-solid fa-trash-can me-2"></i>Ingresar Bajas</label>
                            </div>
                        </div>

                        <div class="col-12 d-none mt-2" id="contenedor_motivo">
                            <label class="form-label fw-bold text-muted small">Motivo común para Garantías/Bajas (Opcional)</label>
                            <input type="text" id="motivo_comun" class="form-control form-control-sm" placeholder="Ej: Cable trozado, Sin audio...">
                        </div>

                        <div class="col-12 mt-4">
                            <div class="p-4 bg-light rounded border border-primary-subtle text-center">
                                <label class="form-label fw-bold fs-5 text-primary mb-3">
                                    <i class="fa-solid fa-barcode me-2"></i>Placa Id del equipo
                                </label>
                                <div class="input-group input-group-lg mx-auto" style="max-width: 600px;">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-crosshairs text-primary"></i></span>
                                    <input type="text" id="input_escaner" class="form-control fw-bold text-center" placeholder="Escanea el código de barras aquí..." autofocus autocomplete="off">
                                </div>
                                <div id="alerta_escaneo" class="mt-2" style="height: 24px;"></div>
                            </div>
                        </div>

                        <!-- Panel de Contadores -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between bg-dark text-white p-3 rounded shadow-sm">
                                <div class="fw-bold fs-5">Total Lote: <span id="contador_total" class="text-info">0</span></div>
                                <div class="fw-bold text-success">Funcionales: <span id="contador_funcional">0</span></div>
                                <div class="fw-bold text-warning">Garantías: <span id="contador_garantia">0</span></div>
                                <div class="fw-bold text-danger">Bajas: <span id="contador_baja">0</span></div>
                            </div>
                        </div>

                        <!-- Columnas de Revisión -->
                        <div class="col-12 mt-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card border-success h-100">
                                        <div class="card-header bg-success text-white fw-bold py-2"><i class="fa-solid fa-check-circle me-1"></i> Funcionales</div>
                                        <div class="card-body p-2" id="lista_funcional" style="max-height: 400px; overflow-y: auto;">
                                            <!-- Items funcionales -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-warning h-100">
                                        <div class="card-header bg-warning text-dark fw-bold py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Garantías</div>
                                        <div class="card-body p-2" id="lista_garantia" style="max-height: 400px; overflow-y: auto;">
                                            <!-- Items garantia -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-danger h-100">
                                        <div class="card-header bg-danger text-white fw-bold py-2"><i class="fa-solid fa-trash-can me-1"></i> Bajas</div>
                                        <div class="card-body p-2" id="lista_baja" style="max-height: 400px; overflow-y: auto;">
                                            <!-- Items baja -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold">Observaciones del Lote (Opcional)</label>
                            <textarea name="observaciones" class="form-control" rows="2" placeholder="Cualquier otro detalle técnico del lote...">{{ old('observaciones') }}</textarea>
                        </div>

                        <div id="inputs_ocultos"></div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold" id="btnGuardar" disabled>
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Lote Completo
                            </button>
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
        const inputEscaner = document.getElementById('input_escaner');
        const modosEscaneo = document.querySelectorAll('input[name="modo_escaneo"]');
        const contenedorMotivo = document.getElementById('contenedor_motivo');
        const motivoComun = document.getElementById('motivo_comun');
        const alertaEscaneo = document.getElementById('alerta_escaneo');
        const btnGuardar = document.getElementById('btnGuardar');
        const inputsOcultos = document.getElementById('inputs_ocultos');

        const contadores = {
            total: document.getElementById('contador_total'),
            funcional: document.getElementById('contador_funcional'),
            garantia: document.getElementById('contador_garantia'),
            baja: document.getElementById('contador_baja')
        };

        const listas = {
            funcional: document.getElementById('lista_funcional'),
            garantia: document.getElementById('lista_garantia'),
            baja: document.getElementById('lista_baja')
        };

        let itemsLote = [];
        let indexItem = 0;

        // Mostrar u ocultar motivo
        modosEscaneo.forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.value === 'garantia' || this.value === 'baja') {
                    contenedorMotivo.classList.remove('d-none');
                } else {
                    contenedorMotivo.classList.add('d-none');
                    motivoComun.value = '';
                }
                inputEscaner.focus();
            });
        });

        inputEscaner.addEventListener('keydown', async function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                const idScan = this.value.trim();
                
                if(idScan === '') return;

                // Validar duplicado
                const duplicado = itemsLote.find(item => item.identificador.toLowerCase() === idScan.toLowerCase());
                if(duplicado) {
                    mostrarAlerta(`El ID ${idScan} ya fue escaneado en este lote.`, 'text-danger fw-bold');
                    this.value = '';
                    return;
                }

                inputEscaner.disabled = true;
                mostrarAlerta(`<i class="fa-solid fa-spinner fa-spin"></i> Buscando equipo...`, 'text-primary');

                let marcaModeloStr = '';
                try {
                    const response = await fetch(`{{ route('inventario.buscar') }}?termino=${encodeURIComponent(idScan)}`);
                    const data = await response.json();
                    if(data.encontrado && data.equipo) {
                        const marca = data.equipo.marca || '';
                        const modelo = data.equipo.modelo || '';
                        marcaModeloStr = `${marca} ${modelo}`.trim();
                    }
                } catch(error) {
                    console.error('Error buscando equipo', error);
                }

                inputEscaner.disabled = false;
                inputEscaner.value = '';
                inputEscaner.focus();

                // Agregar
                const modoActual = document.querySelector('input[name="modo_escaneo"]:checked').value;
                const motivoActual = motivoComun.value.trim();

                const newItem = {
                    idInterno: indexItem++,
                    identificador: idScan,
                    marca_modelo: marcaModeloStr || 'N/A',
                    estado: modoActual,
                    motivo: (modoActual === 'garantia' || modoActual === 'baja') ? motivoActual : ''
                };

                itemsLote.push(newItem);
                renderizarItem(newItem);
                actualizarContadores();
                generarInputsOcultos();

                mostrarAlerta(`ID ${idScan} agregado a ${modoActual}.`, 'text-success fw-bold');
            }
        });

        function mostrarAlerta(msg, clases) {
            alertaEscaneo.className = `mt-2 ${clases}`;
            alertaEscaneo.innerHTML = `<i class="fa-solid fa-circle-info me-1"></i>${msg}`;
            setTimeout(() => { alertaEscaneo.innerHTML = ''; }, 3000);
        }

        function renderizarItem(item) {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between align-items-center bg-white border rounded p-2 mb-2 shadow-sm';
            div.id = `item_${item.idInterno}`;

            let badgeHtml = `<span class="fw-bold">${item.identificador}</span>`;
            if (item.marca_modelo && item.marca_modelo !== 'N/A') {
                badgeHtml += `<br><small class="text-secondary"><i class="fa-solid fa-tag"></i> ${item.marca_modelo}</small>`;
            }
            if(item.motivo) {
                badgeHtml += `<br><small class="text-muted"><i class="fa-solid fa-comment-dots"></i> ${item.motivo}</small>`;
            }

            div.innerHTML = `
                <div>${badgeHtml}</div>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removerItem(${item.idInterno})">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            listas[item.estado].prepend(div);
        }

        window.removerItem = function(idInterno) {
            itemsLote = itemsLote.filter(i => i.idInterno !== idInterno);
            const el = document.getElementById(`item_${idInterno}`);
            if(el) el.remove();
            actualizarContadores();
            generarInputsOcultos();
            inputEscaner.focus();
        };

        function actualizarContadores() {
            const func = itemsLote.filter(i => i.estado === 'funcional').length;
            const gar = itemsLote.filter(i => i.estado === 'garantia').length;
            const baj = itemsLote.filter(i => i.estado === 'baja').length;
            
            contadores.funcional.innerText = func;
            contadores.garantia.innerText = gar;
            contadores.baja.innerText = baj;
            contadores.total.innerText = itemsLote.length;

            btnGuardar.disabled = itemsLote.length === 0;
        }

        function generarInputsOcultos() {
            inputsOcultos.innerHTML = '';
            itemsLote.forEach((item, index) => {
                const inId = document.createElement('input');
                inId.type = 'hidden';
                inId.name = `items[${index}][identificador]`;
                inId.value = item.identificador;
                inputsOcultos.appendChild(inId);

                const inEst = document.createElement('input');
                inEst.type = 'hidden';
                inEst.name = `items[${index}][estado]`;
                inEst.value = item.estado;
                inputsOcultos.appendChild(inEst);

                if(item.marca_modelo) {
                    const inMar = document.createElement('input');
                    inMar.type = 'hidden';
                    inMar.name = `items[${index}][marca_modelo]`;
                    inMar.value = item.marca_modelo;
                    inputsOcultos.appendChild(inMar);
                }

                if(item.motivo) {
                    const inMot = document.createElement('input');
                    inMot.type = 'hidden';
                    inMot.name = `items[${index}][motivo]`;
                    inMot.value = item.motivo;
                    inputsOcultos.appendChild(inMot);
                }
            });
        }

        document.getElementById('formDiademas').addEventListener('submit', function() {
            btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando Lote...';
            btnGuardar.disabled = true;
        });
    });
</script>
@endsection
