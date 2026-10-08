import { createLazyFileRoute, Link } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/_authenticated/portatiles/evidencia')({
  component: PortatilesEvidencia,
})

function PortatilesEvidencia() {
  return (
    <div style={{ maxWidth: '600px', margin: '0 auto' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
        <div>
          <h2 style={{ fontSize: '1.25rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-primary">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
              <circle cx="12" cy="13" r="4"></circle>
            </svg>
            Subir Evidencia Fotográfica
          </h2>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem', margin: '4px 0 0 0' }}>
            Módulo exclusivo para capturar y adjuntar la evidencia de laptops intervenidas.
          </p>
        </div>
        <Link to="/portatiles" className="btn-premium" style={{ width: 'auto', background: 'transparent', border: '1px solid var(--border-color)' }}>
          Bitácora
        </Link>
      </div>

      <div className="card-premium">
        <div className="card-body-premium">
          <form style={{ display: 'flex', flexDirection: 'column', gap: '20px' }} onSubmit={e => e.preventDefault()}>
            
            {/* Placa ID o Serial */}
            <div>
              <label className="input-label">Placa ID o Serial del portátil *</label>
              <div style={{ display: 'flex' }}>
                <div style={{ padding: '12px 16px', background: 'rgba(255,255,255,0.05)', border: '1px solid var(--border-color)', borderRight: 'none', borderRadius: '8px 0 0 8px' }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M4 5v14"></path><path d="M8 5v14"></path><path d="M12 5v14"></path><path d="M16 5v14"></path><path d="M20 5v14"></path></svg>
                </div>
                <input type="text" className="input-premium" placeholder="Ej: L123456 o Serial de la laptop" style={{ borderRadius: '0 8px 8px 0' }} />
              </div>
            </div>

            {/* Analista Responsable */}
            <div>
              <label className="input-label">Analista que captura la evidencia *</label>
              <input type="text" className="input-premium" value="Administrador del Sistema" readOnly style={{ background: 'rgba(255,255,255,0.05)' }} />
            </div>

            {/* Área de Captura */}
            <div>
              <label className="input-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: '8px', verticalAlign: 'text-bottom' }}><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                Fotografía del Portátil *
              </label>
              <div style={{ padding: '32px', border: '2px dashed var(--primary)', borderRadius: '8px', textAlign: 'center', background: 'rgba(6, 182, 212, 0.05)' }}>
                <div style={{ display: 'flex', justifyContent: 'center', gap: '16px', marginBottom: '16px', flexWrap: 'wrap' }}>
                  <button type="button" className="btn-premium" style={{ width: 'auto', padding: '12px 24px' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    Abrir Cámara
                  </button>
                  <button type="button" className="btn-premium" style={{ width: 'auto', padding: '12px 24px', background: 'transparent', border: '1px solid var(--border-color)' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    Galería / Archivos
                  </button>
                </div>
                <div style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>
                  Toca un botón para activar la cámara o seleccionar de la galería de tu dispositivo.
                </div>
              </div>
            </div>

            {/* Enviar */}
            <div style={{ marginTop: '16px' }}>
              <button className="btn-premium" style={{ width: '100%', padding: '16px', fontSize: '1.1rem' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path><path d="M12 12v9"></path><path d="m8 17 4-4 4 4"></path></svg>
                Guardar Evidencia Fotográfica
              </button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  )
}
