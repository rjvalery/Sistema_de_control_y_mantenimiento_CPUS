import { createLazyFileRoute } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/soplado/create')({
  component: SopladoCreate,
})

function SopladoCreate() {
  const [contenia, setContenia] = useState('')

  return (
    <div style={{ maxWidth: '800px', margin: '0 auto' }}>
      <div className="card-premium">
        <div className="card-header-premium" style={{ background: 'var(--primary)', color: 'white', border: 'none' }}>
          <h2 style={{ fontSize: '1.25rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"></path>
            </svg>
            Soplado de CPUs - Registro de Mantenimiento
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

            {/* Detecta Disco */}
            <div>
              <label className="input-label">¿Detecta disco? *</label>
              <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Si">Si</option>
                <option value="No">No</option>
              </select>
            </div>

            {/* Ingreso a BIOS */}
            <div>
              <label className="input-label">¿Ingresó a la BIOS? *</label>
              <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Si">Si</option>
                <option value="No">No</option>
              </select>
            </div>

            {/* Pasta Termica */}
            <div>
              <label className="input-label">¿Se aplicó pasta térmica? *</label>
              <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Si">Si</option>
                <option value="No">No</option>
              </select>
            </div>

            {/* Contenia */}
            <div>
              <label className="input-label">La máquina contenía: *</label>
              <select className="input-premium" value={contenia} onChange={e => setContenia(e.target.value)} style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                <option value="">-- Seleccione una opción --</option>
                <option value="Polvo">Polvo</option>
                <option value="Cucaracha">Cucaracha</option>
                <option value="Papeles de comida">Papeles de comida</option>
                <option value="Humedad / Líquidos">Humedad / Líquidos</option>
                <option value="Otro">Otro</option>
              </select>
            </div>

            {/* Conditional: Cucaracha */}
            {contenia === 'Cucaracha' && (
              <div style={{ gridColumn: '1 / -1', padding: '16px', borderRadius: '8px', border: '1px solid rgba(245, 158, 11, 0.4)', background: 'rgba(245, 158, 11, 0.05)' }}>
                <label className="input-label" style={{ color: '#fbbf24' }}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: '8px', verticalAlign: 'text-bottom' }}><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                  ¿Se aplicó gel para cucarachas? *
                </label>
                <select className="input-premium" style={{ appearance: 'auto', backgroundColor: 'var(--bg-dark)' }}>
                  <option value="">-- Seleccione una opción --</option>
                  <option value="Si">Si</option>
                  <option value="No">No</option>
                </select>
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
                Guardar Registro de Soplado
              </button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  )
}
