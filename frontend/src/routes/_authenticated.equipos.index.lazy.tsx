import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/equipos/')({
  component: EquiposIndex,
})

const mockData = [
  { id: 1, fecha: '12/10/2023 14:30', placa: 'CPU-2034', traslado: 'TR-045', analista: 'Juan Perez', gestion: 'Diagnostico', video: 'Si', estado: 'Funcional' },
  { id: 2, fecha: '12/10/2023 15:45', placa: 'CPU-2035', traslado: null, analista: 'Maria Gomez', gestion: 'Baja', video: 'No', estado: 'Baja' },
  { id: 3, fecha: '13/10/2023 09:15', placa: 'LPT-1050', traslado: 'TR-046', analista: 'Carlos Ruiz', gestion: 'Intervencion', video: 'Si', estado: 'Garantia' },
]

function EquiposIndex() {
  const [busqueda, setBusqueda] = useState('')

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
        <h1 style={{ fontSize: '1.5rem', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px', margin: 0 }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-primary">
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
          Bitácora de Diagnóstico CPU
        </h1>
        <Link to="/equipos/create" className="btn-premium" style={{ width: 'auto' }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Nuevo Registro
        </Link>
      </div>

      <div className="card-premium">
        <div className="card-header-premium" style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
          <input 
            type="text" 
            className="input-premium" 
            placeholder="Buscar placa, traslado, analista..." 
            value={busqueda}
            onChange={e => setBusqueda(e.target.value)}
            style={{ width: '300px' }}
          />
          <input type="date" className="input-premium" style={{ width: '150px' }} title="Fecha desde" />
          <input type="date" className="input-premium" style={{ width: '150px' }} title="Fecha hasta" />
          <button className="btn-premium" style={{ width: 'auto' }}>Filtrar</button>
        </div>
        
        <div className="card-body-premium" style={{ padding: 0 }}>
          <div className="table-container">
            <table className="table-premium">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Fecha</th>
                  <th>Placa</th>
                  <th>Traslado</th>
                  <th>Analista</th>
                  <th>Gestión</th>
                  <th>Video</th>
                  <th>Estado Actual</th>
                  <th style={{ textAlign: 'center' }}>Evidencia</th>
                </tr>
              </thead>
              <tbody>
                {mockData.map(row => (
                  <tr key={row.id}>
                    <td style={{ color: 'var(--text-muted)' }}>{row.id}</td>
                    <td>{row.fecha}</td>
                    <td style={{ fontWeight: 'bold', color: 'var(--primary)' }}>{row.placa}</td>
                    <td>
                      {row.traslado ? <span className="badge badge-secondary">{row.traslado}</span> : <span style={{ color: 'var(--text-muted)' }}>N/A</span>}
                    </td>
                    <td>{row.analista}</td>
                    <td><span className="badge badge-info" style={{ background: 'rgba(6, 182, 212, 0.2)', color: '#22d3ee', border: '1px solid rgba(6, 182, 212, 0.4)' }}>{row.gestion}</span></td>
                    <td>
                      {row.video === 'Si' ? (
                        <span className="badge badge-success">Sí</span>
                      ) : (
                        <span className="badge" style={{ background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', border: '1px solid rgba(239, 68, 68, 0.4)' }}>No</span>
                      )}
                    </td>
                    <td>
                      <span className={`badge ${row.estado === 'Funcional' ? 'badge-success' : row.estado === 'Garantia' ? 'badge-warning' : 'badge'}`}
                            style={row.estado === 'Baja' ? { background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', border: '1px solid rgba(239, 68, 68, 0.4)' } : {}}>
                        {row.estado}
                      </span>
                    </td>
                    <td style={{ textAlign: 'center' }}>
                      <button className="action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                      </button>
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
