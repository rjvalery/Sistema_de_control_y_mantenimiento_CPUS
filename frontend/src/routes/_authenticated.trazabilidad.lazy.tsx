import { createLazyFileRoute } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/trazabilidad')({
  component: TrazabilidadIndex,
})

function TrazabilidadIndex() {
  const [busqueda, setBusqueda] = useState('')
  const [cargando, setCargando] = useState(false)
  const [resultado, setResultado] = useState(false)

  const handleBuscar = (e: React.FormEvent) => {
    e.preventDefault()
    if (busqueda.length < 3) return
    setCargando(true)
    setResultado(false)
    setTimeout(() => {
      setCargando(false)
      setResultado(true)
    }, 1500)
  }

  return (
    <div style={{ maxWidth: '1000px', margin: '0 auto' }}>
      
      <div style={{ textAlign: 'center', marginBottom: '40px' }}>
        <h2 style={{ fontSize: '2rem', fontWeight: 'bold', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '12px', margin: '0 0 16px 0' }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path><path d="M12 7v5l4 2"></path></svg>
          Hoja de Vida y Trazabilidad
        </h2>
        <p style={{ color: 'var(--text-muted)', fontSize: '1.1rem', maxWidth: '600px', margin: '0 auto 24px auto' }}>
          Ingresa el Serial o Placa del equipo para auditar todo su historial cronológico de mantenimientos e ingresos.
        </p>

        <form onSubmit={handleBuscar} style={{ maxWidth: '600px', margin: '0 auto', position: 'relative' }}>
          <div style={{ display: 'flex', background: 'var(--bg-dark)', borderRadius: '30px', border: '1px solid var(--border-color)', overflow: 'hidden' }}>
            <div style={{ padding: '16px 16px 16px 24px', display: 'flex', alignItems: 'center', color: 'var(--text-muted)' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <input 
              type="text" 
              placeholder="Ej: SN-12345 o PC-999" 
              value={busqueda}
              onChange={e => setBusqueda(e.target.value)}
              style={{ flex: 1, background: 'transparent', border: 'none', color: 'var(--text-color)', fontSize: '1.1rem', padding: '16px 8px', outline: 'none' }}
            />
            <button type="submit" className="btn-premium" style={{ width: 'auto', borderRadius: '0', padding: '0 32px', fontSize: '1.1rem' }} disabled={cargando}>
              {cargando ? 'Buscando...' : 'Buscar Historial'}
            </button>
          </div>
        </form>
      </div>

      {cargando && (
        <div style={{ textAlign: 'center', padding: '40px 0' }}>
          <div style={{ display: 'inline-block', width: '48px', height: '48px', border: '4px solid rgba(59, 130, 246, 0.2)', borderTopColor: 'var(--primary)', borderRadius: '50%', animation: 'spin 1s linear infinite' }}></div>
          <p style={{ marginTop: '16px', color: 'var(--text-muted)', fontWeight: 'bold' }}>Rastreando huella del equipo en el sistema...</p>
        </div>
      )}

      {resultado && (
        <div style={{ animation: 'fadeIn 0.5s ease' }}>
          {/* Card Resumen Equipo */}
          <div className="card-premium" style={{ marginBottom: '40px' }}>
            <div className="card-header-premium" style={{ background: 'transparent', borderBottom: 'none', paddingBottom: 0 }}>
              <h5 style={{ margin: 0, fontSize: '1.1rem', display: 'flex', alignItems: 'center', gap: '8px' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ color: 'var(--text-muted)' }}><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                Detalles del Equipo
              </h5>
            </div>
            <div className="card-body-premium">
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '24px' }}>
                <div>
                  <div style={{ fontSize: '0.75rem', textTransform: 'uppercase', fontWeight: 'bold', color: 'var(--text-muted)', marginBottom: '4px' }}>Tipo / Modelo</div>
                  <div style={{ fontSize: '1.1rem', fontWeight: 'bold' }}>CPU - LENOVO</div>
                  <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>ThinkCentre M720q</div>
                </div>
                <div>
                  <div style={{ fontSize: '0.75rem', textTransform: 'uppercase', fontWeight: 'bold', color: 'var(--text-muted)', marginBottom: '4px' }}>Identificadores</div>
                  <div style={{ fontSize: '0.9rem', marginBottom: '4px' }}><span style={{ color: 'var(--text-muted)' }}>Placa:</span> <span style={{ fontWeight: 'bold' }}>CPU-2044</span></div>
                  <div style={{ fontSize: '0.9rem' }}><span style={{ color: 'var(--text-muted)' }}>Serial:</span> <span style={{ fontWeight: 'bold' }}>PC-0X98</span></div>
                </div>
                <div>
                  <div style={{ fontSize: '0.75rem', textTransform: 'uppercase', fontWeight: 'bold', color: 'var(--text-muted)', marginBottom: '4px' }}>Ubicación Actual</div>
                  <div style={{ fontSize: '1.1rem', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Sede Central
                  </div>
                </div>
                <div>
                  <div style={{ fontSize: '0.75rem', textTransform: 'uppercase', fontWeight: 'bold', color: 'var(--text-muted)', marginBottom: '8px' }}>Último Traslado Vigente</div>
                  <span className="badge" style={{ background: 'var(--primary)', color: 'white', padding: '8px 16px', fontSize: '1rem', borderRadius: '30px' }}>TR-045</span>
                </div>
              </div>
            </div>
          </div>

          {/* Timeline - Simplified Mockup without complex CSS ::before/after for now */}
          <h4 style={{ textAlign: 'center', fontWeight: 'bold', marginBottom: '32px' }}>Línea de Tiempo de Intervenciones</h4>
          
          <div style={{ display: 'flex', flexDirection: 'column', gap: '24px', position: 'relative', paddingLeft: '24px' }}>
            <div style={{ position: 'absolute', top: 0, bottom: 0, left: '11px', width: '2px', background: 'var(--border-color)', zIndex: 0 }}></div>
            
            {/* Timeline Item 1 */}
            <div style={{ position: 'relative', zIndex: 1, display: 'flex', gap: '24px' }}>
              <div style={{ width: '24px', height: '24px', borderRadius: '50%', background: 'var(--primary)', flexShrink: 0, position: 'relative', left: '-12px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <div style={{ width: '8px', height: '8px', borderRadius: '50%', background: 'white' }}></div>
              </div>
              <div style={{ flex: 1, background: 'var(--bg-dark)', border: '1px solid var(--border-color)', borderRadius: '12px', padding: '24px', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
                <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 'bold', marginBottom: '8px' }}>12/10/2023 - 10:15 AM</div>
                <h5 style={{ margin: '0 0 12px 0', fontSize: '1.2rem', color: 'var(--text-color)' }}>Diagnóstico CPU</h5>
                <div style={{ display: 'flex', gap: '8px', marginBottom: '16px' }}>
                  <span className="badge badge-secondary">Analista: Juan Perez</span>
                  <span className="badge badge-secondary">TR-045</span>
                </div>
                <div style={{ fontSize: '0.9rem' }}>
                  <div style={{ marginBottom: '4px' }}><strong style={{ color: 'var(--text-muted)', minWidth: '120px', display: 'inline-block' }}>Estado:</strong> Reparado</div>
                  <div style={{ marginBottom: '4px' }}><strong style={{ color: 'var(--text-muted)', minWidth: '120px', display: 'inline-block' }}>Energiza:</strong> Si</div>
                  <div style={{ marginBottom: '4px' }}><strong style={{ color: 'var(--text-muted)', minWidth: '120px', display: 'inline-block' }}>Da Video:</strong> Si</div>
                </div>
              </div>
            </div>

            {/* Timeline Item 2 */}
            <div style={{ position: 'relative', zIndex: 1, display: 'flex', gap: '24px' }}>
              <div style={{ width: '24px', height: '24px', borderRadius: '50%', background: 'var(--info)', flexShrink: 0, position: 'relative', left: '-12px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <div style={{ width: '8px', height: '8px', borderRadius: '50%', background: 'white' }}></div>
              </div>
              <div style={{ flex: 1, background: 'var(--bg-dark)', border: '1px solid var(--border-color)', borderRadius: '12px', padding: '24px', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
                <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)', fontWeight: 'bold', marginBottom: '8px' }}>05/09/2023 - 02:30 PM</div>
                <h5 style={{ margin: '0 0 12px 0', fontSize: '1.2rem', color: 'var(--text-color)' }}>Soplado</h5>
                <div style={{ display: 'flex', gap: '8px', marginBottom: '16px' }}>
                  <span className="badge badge-secondary">Analista: Maria Gomez</span>
                </div>
                <div style={{ fontSize: '0.9rem' }}>
                  <div style={{ marginBottom: '4px' }}><strong style={{ color: 'var(--text-muted)', minWidth: '120px', display: 'inline-block' }}>Contenía:</strong> Polvo</div>
                  <div style={{ marginBottom: '4px' }}><strong style={{ color: 'var(--text-muted)', minWidth: '120px', display: 'inline-block' }}>Pasta térmica:</strong> Si</div>
                </div>
              </div>
            </div>

          </div>
        </div>
      )}

      <style>{`
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  )
}
