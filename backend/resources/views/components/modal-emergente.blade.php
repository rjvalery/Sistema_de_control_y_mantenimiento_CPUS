@props(['id' => 'modalEmergente'])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-3 shadow-lg border-0">
            <div class="modal-body">
                <div id="modalIcono" class="display-4 mb-2"></div>
                <h5 class="modal-title fw-bold mb-2" id="modalTitulo"></h5>
                <div class="text-muted small mb-3" id="modalMensaje"></div>
                
                @if ($slot->isNotEmpty())
                    {{ $slot }}
                @else
                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Aceptar</button>
                @endif
            </div>
        </div>
    </div>
</div>
