import { createLazyFileRoute } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/monitores/create')({
  component: MonitoresCreate,
})

function MonitoresCreate() {
  const [tipoGestion, setTipoGestion] = useState('')

  return (
    <div style={{ maxWidth: '800px', margin: '0 auto' }}>
      <div className="card-premium">
        <div className="card-header-premium" style={{ background: 'var(--primary)', color: 'white', border: 'none' }}>
          <h2 style={{ fontSize: '1.25rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
              <line x1="8" y1="21" x2="16" y2="21"></line>
              <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            Reacondicionamiento de Monitores - Formulario
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

            {/* Serial */}
            <div style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">Serial del equipo *</label>
              <input type="text" className="input-premium" placeholder="Ej: SN123456" />
            </div>

            {/* Tipo de Gestión */}
            <div>
              <label className="input-label">Tipo de gestión *</label>
              <select className="input-premium" value={tipoGestion} onChange={e => setTipoGestion(e.target.value)} style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="diagnostico">Diagnóstico</option>
                <option value="novedad">Novedad</option>
                <option value="baja">Baja</option>
              </select>
            </div>

            {/* Estado Actual */}
            <div>
              <label className="input-label">Estado actual del equipo *</label>
              <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="funcional">Funcional</option>
                <option value="garantia">Garantía</option>
                <option value="baja">Baja</option>
              </select>
            </div>

            {/* Conditional Sections */}
            {tipoGestion === 'diagnostico' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(6, 182, 212, 0.4)', background: 'rgba(6, 182, 212, 0.05)' }}>
                <h4 style={{ color: '#22d3ee', marginBottom: '16px', fontSize: '1rem' }}>Información de Diagnóstico</h4>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                  <div>
                    <label className="input-label">¿Energiza? *</label>
                    <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                      <option value="">-- Seleccione --</option>
                      <option value="1">Sí</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                  <div>
                    <label className="input-label">¿Da video? *</label>
                    <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                      <option value="">-- Seleccione --</option>
                      <option value="1">Sí</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                </div>
              </div>
            )}

            {tipoGestion === 'novedad' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(245, 158, 11, 0.4)', background: 'rgba(245, 158, 11, 0.05)' }}>
                <label className="input-label" style={{ color: '#fbbf24' }}>Escriba la novedad del equipo *</label>
                <textarea className="input-premium" rows={3} placeholder="Detalle la novedad..."></textarea>
              </div>
            )}

            {/* Observaciones */}
            <div style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">Observaciones adicionales (Opcional)</label>
              <input type="text" className="input-premium" placeholder="Cualquier otro detalle técnico..." />
            </div>

            {/* Submit */}
            <div style={{ gridColumn: '1 / -1', marginTop: '16px', display: 'flex', gap: '16px' }}>
              <button className="btn-premium" style={{ flex: 1, padding: '16px', fontSize: '1.1rem' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Guardar Registro de Monitor
              </button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  )
}
