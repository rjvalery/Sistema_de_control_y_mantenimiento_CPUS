import { createLazyFileRoute } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/equipos/create')({
  component: EquiposCreate,
})

function EquiposCreate() {
  const [tipoGestion, setTipoGestion] = useState('')
  const [estadoActual, setEstadoActual] = useState('')

  return (
    <div style={{ maxWidth: '800px', margin: '0 auto' }}>
      <div className="card-premium">
        <div className="card-header-premium" style={{ background: 'var(--primary)', color: 'white', border: 'none' }}>
          <h2 style={{ fontSize: '1.25rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
              <rect x="9" y="9" width="6" height="6"></rect>
              <line x1="9" y1="1" x2="9" y2="4"></line>
              <line x1="15" y1="1" x2="15" y2="4"></line>
              <line x1="9" y1="20" x2="9" y2="23"></line>
              <line x1="15" y1="20" x2="15" y2="23"></line>
              <line x1="20" y1="9" x2="23" y2="9"></line>
              <line x1="20" y1="14" x2="23" y2="14"></line>
              <line x1="1" y1="9" x2="4" y2="9"></line>
              <line x1="1" y1="14" x2="4" y2="14"></line>
            </svg>
            Diagnóstico CPU - Formulario de Garantías y Mantenimiento
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

            {/* Placa ID */}
            <div style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">Placa ID o Serial del equipo *</label>
              <input type="text" className="input-premium" placeholder="Ej: B123456 o Serial" />
            </div>

            {/* Tipo de Gestión */}
            <div>
              <label className="input-label">Tipo de gestión *</label>
              <select className="input-premium" value={tipoGestion} onChange={e => setTipoGestion(e.target.value)} style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Diagnostico">Diagnóstico</option>
                <option value="Intervencion">Intervención</option>
                <option value="Novedad">Novedad</option>
                <option value="Baja">Baja</option>
                <option value="IT">IT</option>
              </select>
            </div>

            {/* Energiza */}
            <div>
              <label className="input-label">¿Energiza? *</label>
              <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Si">Si</option>
                <option value="No">No</option>
              </select>
            </div>

            {/* Da Video */}
            <div>
              <label className="input-label">¿Da video? *</label>
              <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Si">Si</option>
                <option value="No">No</option>
              </select>
            </div>

            {/* Estado Actual */}
            <div>
              <label className="input-label">Estado actual del equipo *</label>
              <select className="input-premium" value={estadoActual} onChange={e => setEstadoActual(e.target.value)} style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Funcional">Funcional</option>
                <option value="Garantia">Garantía</option>
                <option value="Pendiente Repuesto">Pendiente Repuesto</option>
                <option value="Baja">Baja</option>
              </select>
            </div>

            {/* Ubicación */}
            <div style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">Ubicación destino *</label>
              <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Sala Dban">Sala Dban</option>
                <option value="Sala Garantias">Sala Garantías</option>
                <option value="Almacen">Almacén</option>
                <option value="Sala Bajas">Sala Bajas</option>
              </select>
            </div>

            {/* Conditional Sections */}
            {estadoActual === 'Garantia' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(245, 158, 11, 0.4)', background: 'rgba(245, 158, 11, 0.05)' }}>
                <label className="input-label" style={{ color: '#fbbf24' }}>Solución o Trámite de Garantías</label>
                <textarea className="input-premium" rows={3} placeholder="Detalle la solución..."></textarea>
              </div>
            )}

            {tipoGestion === 'Intervencion' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(37, 99, 235, 0.4)', background: 'rgba(37, 99, 235, 0.05)' }}>
                <h4 style={{ color: '#60a5fa', marginBottom: '16px', fontSize: '1rem' }}>Detalles de Intervención</h4>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                  <div>
                    <label className="input-label">¿Qué va a intervenir? *</label>
                    <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                      <option value="">-- Seleccione --</option>
                      <option value="Disco">Disco</option>
                      <option value="RAM">RAM</option>
                    </select>
                  </div>
                  <div>
                    <label className="input-label">Origen de pieza *</label>
                    <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                      <option value="">-- Seleccione --</option>
                      <option value="Nuevo">Nuevo</option>
                      <option value="Garantía">Garantía</option>
                    </select>
                  </div>
                </div>
              </div>
            )}

            {/* Evidencia */}
            <div style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">Evidencia Fotográfica</label>
              <div style={{ padding: '32px', border: '2px dashed var(--border-color)', borderRadius: '8px', textAlign: 'center', cursor: 'pointer' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginBottom: '8px' }}>
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <div style={{ color: 'var(--text-muted)' }}>Haz clic para subir una foto</div>
              </div>
            </div>

            {/* Submit */}
            <div style={{ gridColumn: '1 / -1', marginTop: '16px' }}>
              <button className="btn-premium" style={{ width: '100%', padding: '16px', fontSize: '1.1rem' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Guardar Registro de Diagnóstico
              </button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  )
}
