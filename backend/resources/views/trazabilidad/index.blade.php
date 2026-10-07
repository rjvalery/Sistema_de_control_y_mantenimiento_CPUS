@extends('layouts.app')

@section('title', 'Trazabilidad y Hoja de Vida')

@section('styles')
<style>
    .search-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .timeline {
        position: relative;
        padding: 2rem 0;
        list-style: none;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 50%;
        width: 4px;
        margin-left: -2px;
        background: #e9ecef;
        border-radius: 4px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        min-height: 100px;
    }
    .timeline-item::after {
        content: "";
        display: table;
        clear: both;
    }
    .timeline-icon {
        position: absolute;
        width: 50px;
        height: 50px;
        left: 50%;
        margin-left: -25px;
        border-radius: 50%;
        background-color: #fff;
        border: 4px solid #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        text-align: center;
        line-height: 42px;
        font-size: 1.2rem;
        z-index: 10;
        color: white;
    }
    .timeline-content {
        position: relative;
        width: 45%;
        padding: 1.5rem;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid #f1f3f5;
    }
    .timeline-item:nth-child(even) .timeline-content {
        float: right;
    }
    .timeline-item:nth-child(odd) .timeline-content {
        float: left;
    }
    .timeline-content::before {
        content: '';
        position: absolute;
        top: 15px;
        width: 0;
        height: 0;
        border-style: solid;
    }
    .timeline-item:nth-child(odd) .timeline-content::before {
        right: -14px;
        border-width: 10px 0 10px 14px;
        border-color: transparent transparent transparent #f1f3f5;
    }
    .timeline-item:nth-child(odd) .timeline-content::after {
        content: '';
        position: absolute;
        top: 16px;
        right: -12px;
        border-width: 9px 0 9px 13px;
        border-style: solid;
        border-color: transparent transparent transparent #fff;
    }
    .timeline-item:nth-child(even) .timeline-content::before {
        left: -14px;
        border-width: 10px 14px 10px 0;
        border-color: transparent #f1f3f5 transparent transparent;
    }
    .timeline-item:nth-child(even) .timeline-content::after {
        content: '';
        position: absolute;
        top: 16px;
        left: -12px;
        border-width: 9px 13px 9px 0;
        border-style: solid;
        border-color: transparent #fff transparent transparent;
    }
    
    .timeline-date {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }
    .timeline-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #343a40;
    }
    .timeline-body {
        font-size: 0.9rem;
        color: #495057;
    }
    .timeline-body .detail-row {
        margin-bottom: 0.4rem;
        display: flex;
        align-items: flex-start;
    }
    .timeline-body .detail-label {
        font-weight: 600;
        min-width: 130px;
        color: #495057;
    }
    
    .bg-primary { background-color: #0d6efd !important; }
    .bg-info { background-color: #0dcaf0 !important; }
    .bg-warning { background-color: #ffc107 !important; }
    .bg-danger { background-color: #dc3545 !important; }
    .bg-success { background-color: #198754 !important; }
    
    @media (max-width: 768px) {
        .timeline::before {
            left: 30px;
        }
        .timeline-icon {
            left: 30px;
            width: 40px;
            height: 40px;
            margin-left: -20px;
            line-height: 32px;
            font-size: 1rem;
        }
        .timeline-content {
            width: calc(100% - 70px);
            float: right !important;
            margin-left: 70px;
        }
        .timeline-item:nth-child(odd) .timeline-content::before,
        .timeline-item:nth-child(odd) .timeline-content::after {
            display: none;
        }
        .timeline-item:nth-child(even) .timeline-content::before,
        .timeline-item:nth-child(even) .timeline-content::after {
            display: block;
        }
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center mb-5">
    <div class="col-md-8 text-center">
        <h2 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Hoja de Vida y Trazabilidad</h2>
        <p class="text-muted mb-4">Ingresa el Serial o Placa del equipo para auditar todo su historial cronológico de mantenimientos e ingresos.</p>
        
        <div class="search-container position-relative">
            <form id="formBuscarTrazabilidad" onsubmit="buscarTrazabilidad(event)">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-4 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="inputBusqueda" class="form-control border-0 shadow-none bg-white py-3" placeholder="Ej: SN-12345 o PC-999" required autocomplete="off">
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnBuscar">Buscar Historial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="loadingIndicator" class="text-center d-none py-5">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="mt-3 text-muted fw-semibold">Rastreando huella del equipo en el sistema...</p>
</div>

<div id="resultadoContainer" class="d-none">
    <!-- Card Resumen Equipo -->
    <div class="card shadow-sm border-0 rounded-4 mb-5">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-server text-secondary me-2"></i> Detalles del Equipo</h5>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row g-4" id="equipoResumen">
                <!-- Llenado dinámico por JS -->
            </div>
        </div>
    </div>

    <!-- Línea de Tiempo -->
    <h4 class="fw-bold text-center mb-4 text-dark">Línea de Tiempo de Intervenciones</h4>
    <ul class="timeline" id="timelineContainer">
        <!-- Nodos del timeline llenados dinámicamente -->
    </ul>
</div>
@endsection

@section('scripts')
<script>
    function buscarTrazabilidad(e) {
        e.preventDefault();
        const termino = document.getElementById('inputBusqueda').value.trim();
        if(termino.length < 3) return;

        const btn = document.getElementById('btnBuscar');
        const loading = document.getElementById('loadingIndicator');
        const container = document.getElementById('resultadoContainer');
        const ulTimeline = document.getElementById('timelineContainer');
        const divResumen = document.getElementById('equipoResumen');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Buscando...';
        container.classList.add('d-none');
        loading.classList.remove('d-none');

        fetch(`{{ route('trazabilidad.buscar') }}?termino=${encodeURIComponent(termino)}`)
            .then(res => res.json())
            .then(data => {
                if(!data.success) {
                    alert(data.message);
                    return;
                }

                const base = data.data.equipo_base;
                const timeline = data.data.timeline;

                // Pintar Resumen
                divResumen.innerHTML = `
                    <div class="col-md-3 col-sm-6">
                        <span class="d-block text-muted small fw-bold text-uppercase mb-1">Tipo / Modelo</span>
                        <span class="fs-6 fw-semibold text-dark">${base.tipo_equipo} ${base.marca ? '- ' + base.marca : ''}</span>
                        <div class="text-secondary small">${base.modelo || 'Sin modelo'}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <span class="d-block text-muted small fw-bold text-uppercase mb-1">Identificadores</span>
                        <span class="d-block"><i class="fa-solid fa-barcode text-muted me-1"></i> Placa: <span class="fw-semibold">${base.placa_id || 'N/A'}</span></span>
                        <span class="d-block mt-1"><i class="fa-solid fa-fingerprint text-muted me-1"></i> Serial: <span class="fw-semibold">${base.serial || 'N/A'}</span></span>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <span class="d-block text-muted small fw-bold text-uppercase mb-1">Ubicación Actual</span>
                        <span class="fs-6 fw-semibold text-dark"><i class="fa-solid fa-location-dot text-danger me-1"></i> ${base.ubicacion || 'General'}</span>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <span class="d-block text-muted small fw-bold text-uppercase mb-1">Último Traslado Vigente</span>
                        <span class="badge bg-primary fs-6 py-2 px-3 rounded-pill">${base.num_traslado || 'Sin Traslado'}</span>
                    </div>
                `;

                // Pintar Timeline
                ulTimeline.innerHTML = '';
                timeline.forEach(item => {
                    let detallesHtml = '';
                    for (const [key, value] of Object.entries(item.detalles)) {
                        detallesHtml += `<div class="detail-row"><span class="detail-label">${key}:</span> <span class="detail-value text-dark">${value}</span></div>`;
                    }

                    const li = document.createElement('li');
                    li.className = 'timeline-item';
                    
                    const esBaja = item.is_baja === true;
                    const contentClass = esBaja ? 'border border-danger border-2 shadow-lg' : '';
                    const titleClass = esBaja ? 'text-danger fw-bold' : '';
                    const badgeBaja = esBaja ? '<span class="badge bg-danger ms-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Baja Autorizada</span>' : '';
                    const trasladoBadge = item.traslado ? `<span class="badge bg-light text-dark border ms-1"><i class="fa-solid fa-truck-fast me-1 text-muted"></i> ${item.traslado}</span>` : '';

                    li.innerHTML = `
                        <div class="timeline-icon bg-${item.color}">
                            <i class="fa-solid ${item.icono}"></i>
                        </div>
                        <div class="timeline-content ${contentClass}" style="${esBaja ? 'background-color: #fff5f5;' : ''}">
                            <span class="timeline-date"><i class="fa-regular fa-calendar me-1"></i> ${item.fecha}</span>
                            <h5 class="timeline-title ${titleClass}">${item.modulo} ${badgeBaja}</h5>
                            <div class="mb-3">
                                <span class="badge ${esBaja ? 'bg-danger text-white' : 'bg-light text-dark border'}"><i class="fa-solid fa-user me-1 ${esBaja ? 'text-white' : 'text-muted'}"></i> ${item.analista}</span>
                                ${trasladoBadge}
                            </div>
                            <div class="timeline-body">
                                ${detallesHtml}
                            </div>
                        </div>
                    `;
                    ulTimeline.appendChild(li);
                });

                container.classList.remove('d-none');
            })
            .catch(err => {
                console.error(err);
                alert('Error de conexión al buscar el historial.');
            })
            .finally(() => {
                loading.classList.add('d-none');
                btn.disabled = false;
                btn.innerHTML = 'Buscar Historial';
            });
    }
</script>
@endsection
