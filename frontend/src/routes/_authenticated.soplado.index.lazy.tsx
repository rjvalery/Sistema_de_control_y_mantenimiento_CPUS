import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/soplado/')({
  component: SopladoIndex,
})

const mockData = [
  { id: 1, fecha: '12/10/2023 10:15', placa: 'CPU-2044', traslado: 'TR-045', analista: 'Juan Perez', energiza: 'Si', da_video: 'Si', contenia: 'Polvo' },
  { id: 2, fecha: '12/10/2023 11:30', placa: 'CPU-2045', traslado: null, analista: 'Maria Gomez', energiza: 'No', da_video: 'No', contenia: 'Cucaracha' },
  { id: 3, fecha: '13/10/2023 08:45', placa: 'CPU-2046', traslado: 'TR-046', analista: 'Carlos Ruiz', energiza: 'Si', da_video: 'Si', contenia: 'Normal' },
]

function SopladoIndex() {
  const [busqueda, setBusqueda] = useState('')

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
        <h1 style={{ fontSize: '1.5rem', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px', margin: 0 }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ color: 'var(--info)' }}>
            <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"></path>
          </svg>
          Bitácora de Soplado y Mantenimiento
        </h1>
        <Link to="/soplado/create" className="btn-premium" style={{ width: 'auto', background: 'var(--info)' }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Nuevo Registro
        </Link>
      </div>

      <div className="card-premium">
        <div className="card-header-premium" style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
          <input 
            type="text" 
            className="input-premium" 
            placeholder="Buscar placa, traslado o analista..." 
            value={busqueda}
            onChange={e => setBusqueda(e.target.value)}
            style={{ width: '300px' }}
          />
          <input type="date" className="input-premium" style={{ width: '150px' }} title="Fecha desde" />
          <input type="date" className="input-premium" style={{ width: '150px' }} title="Fecha hasta" />
          <button className="btn-premium" style={{ width: 'auto', background: 'var(--info)' }}>Filtrar</button>
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
                  <th>Energiza</th>
                  <th>Da Video</th>
                  <th>Máquina Contenía</th>
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
                    <td>
                      {row.energiza === 'Si' ? <span style={{ color: 'var(--success)' }}>Sí</span> : <span style={{ color: 'var(--danger)' }}>No</span>}
                    </td>
                    <td>
                      {row.da_video === 'Si' ? <span style={{ color: 'var(--success)' }}>Sí</span> : <span style={{ color: 'var(--danger)' }}>No</span>}
                    </td>
                    <td>
                      <span className="badge badge-secondary" style={{ background: 'rgba(255,255,255,0.1)' }}>{row.contenia}</span>
                    </td>
                    <td style={{ textAlign: 'center' }}>
                      <button className="action-btn" style={{ color: 'var(--info)' }}>
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
