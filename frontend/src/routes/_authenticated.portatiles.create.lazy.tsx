import { createLazyFileRoute } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/portatiles/create')({
  component: PortatilesCreate,
})

function PortatilesCreate() {
  const [tipoGestion, setTipoGestion] = useState('')
  const [estadoActual, setEstadoActual] = useState('')

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
            Diagnóstico e Intervención de Portátiles
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
            <div>
              <label className="input-label">Placa ID o Serial del equipo *</label>
              <input type="text" className="input-premium" placeholder="Ej: B123456 o Serial" />
            </div>

            {/* Tipo de Gestión */}
            <div>
              <label className="input-label">Tipo de gestión *</label>
              <select className="input-premium" value={tipoGestion} onChange={e => setTipoGestion(e.target.value)} style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Diagnostico">Diagnóstico</option>
                <option value="Mantenimiento">Mantenimiento</option>
                <option value="Baja">Baja</option>
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

            {/* Realizó Test Lenovo */}
            <div>
              <label className="input-label">¿Realizó test Lenovo? *</label>
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
                <option value="Novedad">Novedad</option>
                <option value="Pendiente repuesto">Pendiente repuesto</option>
                <option value="Reparado">Reparado</option>
                <option value="Baja">Baja</option>
                <option value="Donacion">Donación</option>
              </select>
            </div>

            {/* Conditional: Garantia */}
            {estadoActual === 'Garantia' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(245, 158, 11, 0.4)', background: 'rgba(245, 158, 11, 0.05)' }}>
                <h4 style={{ color: '#fbbf24', marginBottom: '16px', fontSize: '1rem' }}>Información de Garantía</h4>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                  <div>
                    <label className="input-label">Número de ticket *</label>
                    <input type="text" className="input-premium" placeholder="Ej: TCK-9988" />
                  </div>
                  <div>
                    <label className="input-label">Garantía *</label>
                    <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                      <option value="">-- Seleccione --</option>
                      <option value="Aplica">Aplica</option>
                      <option value="No Aplica">No Aplica</option>
                      <option value="En trámite">En trámite</option>
                    </select>
                  </div>
                  <div style={{ gridColumn: '1 / -1' }}>
                    <label className="input-label">¿Por qué solicita garantía?</label>
                    <textarea className="input-premium" rows={2} placeholder="Motivo de la solicitud..."></textarea>
                  </div>
                </div>
              </div>
            )}

            {/* Conditional: Diagnóstico */}
            {['Diagnostico', 'Novedad'].includes(tipoGestion) && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(37, 99, 235, 0.4)', background: 'rgba(37, 99, 235, 0.05)' }}>
                <label className="input-label" style={{ color: '#60a5fa' }}>Diagnóstico del laptop intervenido</label>
                <textarea className="input-premium" rows={2} placeholder="Describa el diagnóstico o detalle de la novedad..."></textarea>
              </div>
            )}

            {/* Conditional: Repuesto */}
            {estadoActual === 'Pendiente repuesto' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(6, 182, 212, 0.4)', background: 'rgba(6, 182, 212, 0.05)' }}>
                <h4 style={{ color: '#22d3ee', marginBottom: '16px', fontSize: '1rem' }}>Detalle de Repuestos y Piezas Requeridas</h4>
                <div style={{ display: 'flex', gap: '8px', marginBottom: '12px' }}>
                  <input type="text" className="input-premium" placeholder="Ej: Pantalla" style={{ flex: 1 }} />
                  <input type="text" className="input-premium" placeholder="FRU Ej: 5B20V12345" style={{ flex: 1 }} />
                  <button type="button" className="btn-premium" style={{ padding: '8px 12px', background: 'transparent', border: '1px solid var(--danger)', color: 'var(--danger)' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  </button>
                </div>
                <button type="button" className="btn-premium" style={{ width: 'auto', padding: '6px 12px', fontSize: '0.85rem' }}>+ Agregar otra pieza</button>
                
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginTop: '16px', paddingTop: '16px', borderTop: '1px solid rgba(255,255,255,0.1)' }}>
                  <div>
                    <label className="input-label">Pieza Principal Intervenida</label>
                    <input type="text" className="input-premium" placeholder="Ej: Board, Pantalla" />
                  </div>
                  <div>
                    <label className="input-label">Origen de la pieza</label>
                    <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                      <option value="">-- Seleccione --</option>
                      <option value="Nuevo">Nuevo</option>
                      <option value="Reacondicionado">Reacondicionado</option>
                    </select>
                  </div>
                </div>
              </div>
            )}

            {/* Conditional: Reparado */}
            {estadoActual === 'Reparado' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(16, 185, 129, 0.4)', background: 'rgba(16, 185, 129, 0.05)' }}>
                <h4 style={{ color: '#34d399', marginBottom: '16px', fontSize: '1rem' }}>Información de Reparación del Equipo</h4>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                  <div>
                    <label className="input-label">¿Quién realizó la reparación? *</label>
                    <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                      <option value="">-- Seleccione --</option>
                      <option value="Analista">Analista</option>
                      <option value="Lenovo">Lenovo</option>
                    </select>
                  </div>
                  <div style={{ gridColumn: '1 / -1' }}>
                    <label className="input-label">Comentario u Observaciones</label>
                    <textarea className="input-premium" rows={2} placeholder="Detalle los trabajos realizados..."></textarea>
                  </div>
                </div>
              </div>
            )}

            {/* Submit */}
            <div style={{ gridColumn: '1 / -1', marginTop: '16px' }}>
              <button className="btn-premium" style={{ width: '100%', padding: '16px', fontSize: '1.1rem' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Guardar Registro de Portátil
              </button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  )
}
