import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/portatiles/')({
  component: PortatilesIndex,
})

const mockData = [
  { id: 1, fecha: '12/10/2023 10:15', placa: 'LPT-2044', traslado: 'TR-045', ticket: 'TCK-9988', analista: 'Juan Perez', estado_actual: 'Reparado', estado_final: 'Entregado a usuario' },
  { id: 2, fecha: '12/10/2023 11:30', placa: 'LPT-2045', traslado: null, ticket: null, analista: 'Maria Gomez', estado_actual: 'Garantia', estado_final: 'Enviado a Lenovo' },
  { id: 3, fecha: '13/10/2023 08:45', placa: 'LPT-2046', traslado: 'TR-046', ticket: null, analista: 'Carlos Ruiz', estado_actual: 'Baja', estado_final: 'Para desarme' },
]

function PortatilesIndex() {
  const [busqueda, setBusqueda] = useState('')

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
        <h1 style={{ fontSize: '1.5rem', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px', margin: 0 }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ color: 'var(--success)' }}>
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
          </svg>
          Bitácora de Diagnóstico y Garantías Portátiles
        </h1>
        <Link to="/portatiles/create" className="btn-premium" style={{ width: 'auto', background: 'var(--success)' }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Nuevo Diagnóstico
        </Link>
      </div>

      <div className="card-premium">
        <div className="card-header-premium" style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
          <input 
            type="text" 
            className="input-premium" 
            placeholder="Buscar placa, traslado, ticket o analista..." 
            value={busqueda}
            onChange={e => setBusqueda(e.target.value)}
            style={{ width: '300px' }}
          />
          <input type="date" className="input-premium" style={{ width: '150px' }} title="Fecha desde" />
          <input type="date" className="input-premium" style={{ width: '150px' }} title="Fecha hasta" />
          <button className="btn-premium" style={{ width: 'auto', background: 'var(--success)' }}>Filtrar</button>
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
                  <th>Ticket</th>
                  <th>Analista</th>
                  <th>Estado Actual</th>
                  <th>Estado Final</th>
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
                    <td>
                      {row.ticket ? <code>{row.ticket}</code> : <span style={{ color: 'var(--text-muted)' }}>N/A</span>}
                    </td>
                    <td>{row.analista}</td>
                    <td>
                      <span className={`badge ${row.estado_actual === 'Reparado' ? 'badge-success' : row.estado_actual === 'Garantia' ? 'badge-warning' : 'badge'}`}
                            style={row.estado_actual === 'Baja' ? { background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', border: '1px solid rgba(239, 68, 68, 0.4)' } : {}}>
                        {row.estado_actual}
                      </span>
                    </td>
                    <td style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>{row.estado_final}</td>
                    <td style={{ textAlign: 'center' }}>
                      <button className="action-btn" style={{ color: 'var(--success)' }}>
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
