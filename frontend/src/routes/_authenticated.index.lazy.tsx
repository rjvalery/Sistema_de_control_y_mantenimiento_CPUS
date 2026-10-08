import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/_authenticated/')({
  component: Dashboard,
})

function Dashboard() {
  return (
    <div>
      <h1 style={{ fontSize: '2rem', fontWeight: 'bold', marginBottom: '16px' }}>Dashboard</h1>
      
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '24px', marginTop: '24px' }}>
        
        {/* Card 1 */}
        <div style={{ background: 'var(--bg-card)', padding: '24px', borderRadius: '12px', border: '1px solid var(--border-color)' }}>
          <div style={{ color: 'var(--text-muted)', marginBottom: '8px', fontSize: '0.9rem' }}>Total CPUs</div>
          <div style={{ fontSize: '2rem', fontWeight: 'bold', color: 'white' }}>124</div>
        </div>
        
        {/* Card 2 */}
        <div style={{ background: 'var(--bg-card)', padding: '24px', borderRadius: '12px', border: '1px solid var(--border-color)' }}>
          <div style={{ color: 'var(--text-muted)', marginBottom: '8px', fontSize: '0.9rem' }}>Portátiles en Revisión</div>
          <div style={{ fontSize: '2rem', fontWeight: 'bold', color: 'white' }}>18</div>
        </div>

        {/* Card 3 */}
        <div style={{ background: 'var(--bg-card)', padding: '24px', borderRadius: '12px', border: '1px solid var(--border-color)' }}>
          <div style={{ color: 'var(--text-muted)', marginBottom: '8px', fontSize: '0.9rem' }}>Mantenimientos Hoy</div>
          <div style={{ fontSize: '2rem', fontWeight: 'bold', color: 'white' }}>5</div>
        </div>

      </div>
    </div>
  )
}
