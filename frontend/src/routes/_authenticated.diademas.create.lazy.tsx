import { createLazyFileRoute } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/diademas/create')({
  component: DiademasCreate,
})

function DiademasCreate() {
  const [modo, setModo] = useState('funcional')

  return (
    <div style={{ maxWidth: '900px', margin: '0 auto' }}>
      <div className="card-premium">
        <div className="card-header-premium" style={{ background: 'var(--primary)', color: 'white', border: 'none' }}>
          <h2 style={{ fontSize: '1.25rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
              <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
            </svg>
            Recepción y Clasificación de Diademas por Lote
          </h2>
        </div>
        
        <div className="card-body-premium">
          <form style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }} onSubmit={e => e.preventDefault()}>
            
            {/* Analista */}
            <div>
              <label className="input-label">Nombre del analista *</label>
              <input type="text" className="input-premium" value="Administrador del Sistema" readOnly style={{ background: 'rgba(255,255,255,0.05)' }} />
            </div>

            {/* Traslado */}
            <div>
              <label className="input-label">Número de Traslado *</label>
              <input type="text" className="input-premium" placeholder="Ej: 123456" />
            </div>

            {/* Modo Escaneo */}
            <div style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">Modo de Escaneo (Seleccione destino) *</label>
              <div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
                <button 
                  type="button" 
                  onClick={() => setModo('funcional')}
                  style={{ 
                    padding: '12px 24px', 
                    borderRadius: '8px', 
                    border: modo === 'funcional' ? '2px solid var(--success)' : '1px solid var(--border-color)',
                    background: modo === 'funcional' ? 'rgba(16, 185, 129, 0.1)' : 'transparent',
                    color: modo === 'funcional' ? 'var(--success)' : 'var(--text-color)',
                    cursor: 'pointer',
                    fontWeight: 'bold',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px'
                  }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                  Ingresar Funcionales
                </button>
                <button 
                  type="button" 
                  onClick={() => setModo('garantia')}
                  style={{ 
                    padding: '12px 24px', 
                    borderRadius: '8px', 
                    border: modo === 'garantia' ? '2px solid var(--warning)' : '1px solid var(--border-color)',
                    background: modo === 'garantia' ? 'rgba(245, 158, 11, 0.1)' : 'transparent',
                    color: modo === 'garantia' ? 'var(--warning)' : 'var(--text-color)',
                    cursor: 'pointer',
                    fontWeight: 'bold',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px'
                  }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                  Ingresar Garantías
                </button>
                <button 
                  type="button" 
                  onClick={() => setModo('baja')}
                  style={{ 
                    padding: '12px 24px', 
                    borderRadius: '8px', 
                    border: modo === 'baja' ? '2px solid var(--danger)' : '1px solid var(--border-color)',
                    background: modo === 'baja' ? 'rgba(239, 68, 68, 0.1)' : 'transparent',
                    color: modo === 'baja' ? 'var(--danger)' : 'var(--text-color)',
                    cursor: 'pointer',
                    fontWeight: 'bold',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px'
                  }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  Ingresar Bajas
                </button>
              </div>
            </div>

            {/* Opcional: Motivo para bajas y garantias */}
            {(modo === 'garantia' || modo === 'baja') && (
              <div style={{ gridColumn: '1 / -1' }}>
                <label className="input-label" style={{ color: 'var(--text-muted)' }}>Motivo común para Garantías/Bajas (Opcional)</label>
                <input type="text" className="input-premium" placeholder="Ej: Cable trozado, Sin audio..." />
              </div>
            )}

            {/* Escaner Central */}
            <div style={{ gridColumn: '1 / -1', padding: '32px', borderRadius: '12px', border: '1px solid rgba(59, 130, 246, 0.3)', background: 'rgba(59, 130, 246, 0.05)', textAlign: 'center' }}>
              <label style={{ fontSize: '1.25rem', fontWeight: 'bold', color: 'var(--primary)', display: 'block', marginBottom: '16px' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: '8px' }}><path d="M3 5v14"></path><path d="M8 5v14"></path><path d="M12 5v14"></path><path d="M17 5v14"></path><path d="M21 5v14"></path></svg>
                Placa Id del equipo
              </label>
              <div style={{ maxWidth: '600px', margin: '0 auto', position: 'relative' }}>
                <input 
                  type="text" 
                  className="input-premium" 
                  placeholder="Escanea el código de barras aquí..." 
                  style={{ textAlign: 'center', fontSize: '1.25rem', padding: '16px', fontWeight: 'bold' }} 
                />
              </div>
            </div>

            {/* Summary Boxes */}
            <div style={{ gridColumn: '1 / -1', display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '16px', padding: '16px', background: 'var(--bg-dark)', borderRadius: '8px', border: '1px solid var(--border-color)' }}>
              <div style={{ textAlign: 'center' }}>
                <div style={{ color: 'var(--info)', fontWeight: 'bold', fontSize: '0.9rem' }}>Total Lote</div>
                <div style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>0</div>
              </div>
              <div style={{ textAlign: 'center' }}>
                <div style={{ color: 'var(--success)', fontWeight: 'bold', fontSize: '0.9rem' }}>Funcionales</div>
                <div style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>0</div>
              </div>
              <div style={{ textAlign: 'center' }}>
                <div style={{ color: 'var(--warning)', fontWeight: 'bold', fontSize: '0.9rem' }}>Garantías</div>
                <div style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>0</div>
              </div>
              <div style={{ textAlign: 'center' }}>
                <div style={{ color: 'var(--danger)', fontWeight: 'bold', fontSize: '0.9rem' }}>Bajas</div>
                <div style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>0</div>
              </div>
            </div>

            {/* Listas */}
            <div style={{ gridColumn: '1 / -1', display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '16px' }}>
              <div style={{ border: '1px solid rgba(16, 185, 129, 0.3)', borderRadius: '8px', overflow: 'hidden' }}>
                <div style={{ background: 'var(--success)', color: 'white', padding: '8px', fontWeight: 'bold', textAlign: 'center' }}>Funcionales</div>
                <div style={{ padding: '16px', minHeight: '150px', background: 'rgba(16, 185, 129, 0.05)' }}>
                  <div style={{ textAlign: 'center', color: 'var(--text-muted)', fontSize: '0.85rem', marginTop: '20px' }}>Sin items</div>
                </div>
              </div>
              <div style={{ border: '1px solid rgba(245, 158, 11, 0.3)', borderRadius: '8px', overflow: 'hidden' }}>
                <div style={{ background: 'var(--warning)', color: 'white', padding: '8px', fontWeight: 'bold', textAlign: 'center' }}>Garantías</div>
                <div style={{ padding: '16px', minHeight: '150px', background: 'rgba(245, 158, 11, 0.05)' }}>
                  <div style={{ textAlign: 'center', color: 'var(--text-muted)', fontSize: '0.85rem', marginTop: '20px' }}>Sin items</div>
                </div>
              </div>
              <div style={{ border: '1px solid rgba(239, 68, 68, 0.3)', borderRadius: '8px', overflow: 'hidden' }}>
                <div style={{ background: 'var(--danger)', color: 'white', padding: '8px', fontWeight: 'bold', textAlign: 'center' }}>Bajas</div>
                <div style={{ padding: '16px', minHeight: '150px', background: 'rgba(239, 68, 68, 0.05)' }}>
                  <div style={{ textAlign: 'center', color: 'var(--text-muted)', fontSize: '0.85rem', marginTop: '20px' }}>Sin items</div>
                </div>
              </div>
            </div>

            {/* Observaciones */}
            <div style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">Observaciones del Lote (Opcional)</label>
              <textarea className="input-premium" rows={2} placeholder="Cualquier otro detalle técnico del lote..."></textarea>
            </div>

            {/* Submit */}
            <div style={{ gridColumn: '1 / -1', marginTop: '16px' }}>
              <button className="btn-premium" style={{ width: '100%', padding: '16px', fontSize: '1.1rem' }} disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Guardar Lote Completo
              </button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  )
}
