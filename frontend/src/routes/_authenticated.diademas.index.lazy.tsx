import { createLazyFileRoute, Link } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/_authenticated/diademas/')({
  component: DiademasIndex,
})

const mockData = [
  { id: 1, fecha: '12/10/2023 10:15', traslado: 'TR-045', total: 15, funcionales: 10, garantia: 3, baja: 2, analista: 'Juan Perez' },
  { id: 2, fecha: '12/10/2023 11:30', traslado: 'TR-046', total: 5, funcionales: 5, garantia: 0, baja: 0, analista: 'Maria Gomez' },
  { id: 3, fecha: '13/10/2023 08:45', traslado: 'TR-047', total: 20, funcionales: 15, garantia: 4, baja: 1, analista: 'Carlos Ruiz' },
]

function DiademasIndex() {
  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
        <h1 style={{ fontSize: '1.5rem', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px', margin: 0 }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-primary">
            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
          </svg>
          Bitácora de Diademas Recibidas
        </h1>
        <Link to="/diademas/create" className="btn-premium" style={{ width: 'auto' }}>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Recepción por Lote
        </Link>
      </div>

      <div className="card-premium">
        <div className="card-body-premium" style={{ padding: 0 }}>
          <div className="table-container">
            <table className="table-premium">
              <thead>
                <tr>
                  <th>Fecha y Hora</th>
                  <th>N° Traslado</th>
                  <th style={{ textAlign: 'center' }}>Total Unidades</th>
                  <th style={{ textAlign: 'center' }}>Desglose</th>
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
                      <span className="badge badge-secondary">{row.traslado}</span>
                    </td>
                    <td style={{ textAlign: 'center', fontWeight: 'bold', fontSize: '1.1rem' }}>
                      {row.total}
                    </td>
                    <td style={{ textAlign: 'center' }}>
                      <span className="badge badge-success" style={{ marginRight: '4px' }}>{row.funcionales} F</span>
                      <span className="badge badge-warning" style={{ marginRight: '4px' }}>{row.garantia} G</span>
                      <span className="badge" style={{ background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', border: '1px solid rgba(239, 68, 68, 0.4)' }}>{row.baja} B</span>
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
