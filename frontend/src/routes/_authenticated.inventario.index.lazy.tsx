import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/inventario/')({
  component: InventarioIndex,
})

// Mock data to simulate the backend
const mockData = [
  { id: 101, traslado: 'TR-045', placa: 'CPU-2034', serial: 'SN-982341', modelo: 'Optiplex 3080', tipo: 'Desktop', ubicacion: 'Almacén Principal', estado: 'Cargado', intervenido: true, modulo: 'Diagnóstico CPU', analista: 'Juan Perez', fecha: '2023-10-01 14:30' },
  { id: 102, traslado: null, placa: 'CPU-2035', serial: 'SN-982342', modelo: 'ProDesk 400', tipo: 'SFF', ubicacion: 'Bodega 2', estado: 'Pendiente', intervenido: false, modulo: null, analista: null, fecha: null },
  { id: 103, traslado: 'TR-046', placa: 'LPT-1050', serial: 'SN-772109', modelo: 'ThinkPad T14', tipo: 'Laptop', ubicacion: 'Almacén Principal', estado: 'En Revisión', intervenido: false, modulo: null, analista: null, fecha: null },
]

function InventarioIndex() {
  const [busqueda, setBusqueda] = useState('')

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '24px', borderBottom: '1px solid var(--border-color)', paddingBottom: '16px' }}>
        <div>
          <h1 style={{ fontSize: '1.5rem', fontWeight: 'bold', marginBottom: '8px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-primary">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
              <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            Inventario General
          </h1>
          <p style={{ color: 'var(--text-muted)', margin: 0 }}>Consulta, filtrado y gestión del parque de equipos en almacén.</p>
        </div>
        <div style={{ display: 'flex', gap: '12px' }}>
          <button className="btn-premium" style={{ padding: '8px 16px', background: 'transparent', border: '1px solid var(--primary)', color: 'var(--primary)' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            Cargue Masivo
          </button>
        </div>
      </div>

      {/* Metrics */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '16px', marginBottom: '32px' }}>
        <div className="metric-card metric-primary">
          <div>
            <div className="metric-title">Total Cargados (Cubic)</div>
            <div className="metric-value">1,245</div>
          </div>
        </div>
        <div className="metric-card metric-success">
          <div>
            <div className="metric-title">Intervenidos en Sistema</div>
            <div className="metric-value">
              842 <span className="badge badge-success" style={{ marginLeft: '8px', fontSize: '0.75rem' }}>67%</span>
            </div>
          </div>
        </div>
        <div className="metric-card metric-warning">
          <div>
            <div className="metric-title">Pendientes por Ingresar</div>
            <div className="metric-value">403</div>
          </div>
        </div>
        <div className="metric-card metric-info">
          <div>
            <div className="metric-title">Traslados Registrados</div>
            <div className="metric-value">15</div>
          </div>
        </div>
      </div>

      {/* Table Section */}
      <div className="card-premium">
        <div className="card-header-premium">
          <h3 style={{ fontSize: '1.1rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
            Listado de Equipos
            <span className="badge badge-primary">3 registros</span>
          </h3>
          <div style={{ display: 'flex', gap: '12px' }}>
            <input 
              type="text" 
              className="input-premium" 
              placeholder="Buscar Placa o Serial..." 
              value={busqueda}
              onChange={e => setBusqueda(e.target.value)}
              style={{ width: '250px', padding: '6px 12px' }}
            />
          </div>
        </div>
        <div className="card-body-premium" style={{ padding: 0 }}>
          <div className="table-container">
            <table className="table-premium">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Traslado</th>
                  <th>Placa / Serial</th>
                  <th>Modelo & Tipo</th>
                  <th>Ubicación</th>
                  <th>Estatus</th>
                  <th>Intervención</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {mockData.map(item => (
                  <tr key={item.id}>
                    <td style={{ color: 'var(--text-muted)' }}>#{item.id}</td>
                    <td>
                      {item.traslado ? (
                        <span className="badge badge-primary">{item.traslado}</span>
                      ) : (
                        <span style={{ color: 'var(--text-muted)' }}>—</span>
                      )}
                    </td>
                    <td>
                      <div style={{ fontWeight: '600' }}>{item.placa}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontFamily: 'monospace' }}>{item.serial}</div>
                    </td>
                    <td>
                      <div>{item.modelo}</div>
                      <span className="badge badge-secondary" style={{ marginTop: '4px' }}>{item.tipo}</span>
                    </td>
                    <td>{item.ubicacion}</td>
                    <td>
                      <span className={`badge ${item.estado === 'Cargado' ? 'badge-primary' : item.estado === 'Pendiente' ? 'badge-warning' : 'badge-info'}`}>
                        {item.estado}
                      </span>
                    </td>
                    <td>
                      {item.intervenido ? (
                        <div>
                          <span className="badge badge-success">{item.modulo}</span>
                          <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', marginTop: '4px' }}>
                            Por: {item.analista}
                          </div>
                        </div>
                      ) : (
                        <span className="badge badge-warning" style={{ opacity: 0.7 }}>Pendiente</span>
                      )}
                    </td>
                    <td>
                      <Link to="/inventario/$id" params={{ id: String(item.id) }} className="action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  )
}
