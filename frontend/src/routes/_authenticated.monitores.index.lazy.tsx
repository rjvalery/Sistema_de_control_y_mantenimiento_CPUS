import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/monitores/')({
  component: MonitoresIndex,
})

const mockData = [
  { id: 1, fecha: '12/10/2023 10:15', serial: 'SN-MON-9812', placa: 'MON-321', traslado: 'TR-045', gestion: 'diagnostico', energiza: true, video: true, estado: 'funcional', analista: 'Juan Perez' },
  { id: 2, fecha: '12/10/2023 11:30', serial: 'SN-MON-9813', placa: 'MON-322', traslado: null, gestion: 'novedad', energiza: null, video: null, estado: 'garantia', analista: 'Maria Gomez', motivo_novedad: 'Pantalla rayada en el centro' },
  { id: 3, fecha: '13/10/2023 08:45', serial: 'SN-MON-9814', placa: 'MON-323', traslado: 'TR-046', gestion: 'baja', energiza: false, video: false, estado: 'baja', analista: 'Carlos Ruiz' },
]

function MonitoresIndex() {
  const [busqueda, setBusqueda] = useState('')

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
        <h1 style={{ fontSize: '1.5rem', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px', margin: 0 }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-primary">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
          </svg>
          Reacondicionamiento de Monitores
        </h1>
        <Link to="/monitores/create" className="btn-premium" style={{ width: 'auto' }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Nuevo Registro
        </Link>
      </div>

      <div className="card-premium">
        <div className="card-header-premium" style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
          <input 
            type="text" 
            className="input-premium" 
            placeholder="Buscar por serial, placa o analista..." 
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
                  <th>Fecha y Hora</th>
                  <th>Serial / Placa</th>
                  <th>Traslado</th>
                  <th>Tipo Gestión</th>
                  <th>Diagnóstico / Motivo</th>
                  <th>Estado Actual</th>
                  <th>Analista</th>
                  <th style={{ textAlign: 'center' }}>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {mockData.map(row => (
                  <tr key={row.id}>
                    <td>
                      <div style={{ fontWeight: '600' }}>{row.fecha.split(' ')[0]}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{row.fecha.split(' ')[1]}</div>
                    </td>
                    <td>
                      <div style={{ fontWeight: 'bold', color: 'var(--primary)' }}>{row.serial}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{row.placa}</div>
                    </td>
                    <td>
                      {row.traslado ? <span className="badge badge-secondary">{row.traslado}</span> : <span style={{ color: 'var(--text-muted)' }}>N/A</span>}
                    </td>
                    <td>
                      {row.gestion === 'diagnostico' && <span className="badge badge-info">Diagnóstico</span>}
                      {row.gestion === 'novedad' && <span className="badge badge-warning">Novedad</span>}
                      {row.gestion === 'baja' && <span className="badge" style={{ background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', border: '1px solid rgba(239, 68, 68, 0.4)' }}>Baja</span>}
                    </td>
                    <td>
                      {row.gestion === 'diagnostico' && (
                        <div style={{ fontSize: '0.85rem' }}>
                          <span style={{ fontWeight: 'bold' }}>Energiza:</span> {row.energiza ? <span style={{ color: 'var(--success)' }}>Sí</span> : <span style={{ color: 'var(--danger)' }}>No</span>} | <span style={{ fontWeight: 'bold' }}>Video:</span> {row.video ? <span style={{ color: 'var(--success)' }}>Sí</span> : <span style={{ color: 'var(--danger)' }}>No</span>}
                        </div>
                      )}
                      {row.gestion === 'novedad' && (
                        <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                          {row.motivo_novedad}
                        </div>
                      )}
                      {row.gestion === 'baja' && (
                        <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                          Baja autorizada
                        </div>
                      )}
                    </td>
                    <td>
                      <span className={`badge ${row.estado === 'funcional' ? 'badge-success' : row.estado === 'garantia' ? 'badge-warning' : 'badge'}`}
                            style={row.estado === 'baja' ? { background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', border: '1px solid rgba(239, 68, 68, 0.4)' } : {}}>
                        {row.estado.charAt(0).toUpperCase() + row.estado.slice(1)}
                      </span>
                    </td>
                    <td>{row.analista}</td>
                    <td style={{ textAlign: 'center' }}>
                      <button className="action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
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
