import { createFileRoute, Link, Outlet } from '@tanstack/react-router'
import { useState } from 'react'
import '../layout.css'

// Layout for authenticated routes
export const Route = createFileRoute('/_authenticated')({
  beforeLoad: () => {
    // Here we will eventually check auth status
    // if (!isAuthenticated) {
    //   throw redirect({ to: '/login' })
    // }
  },
  component: AuthenticatedLayout,
})

function AuthenticatedLayout() {
  const [isMenuOpen, setIsMenuOpen] = useState(false)

  return (
    <div className="app-layout">
      {/* Navbar Premium */}
      <nav className="navbar-premium">
        <div className="navbar-container">
          <Link to="/" className="navbar-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-primary mr-2">
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
            Control CPUs
          </Link>

          <button className="mobile-menu-btn" onClick={() => setIsMenuOpen(!isMenuOpen)}>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>

          <div className={`nav-links ${isMenuOpen ? 'open' : ''}`}>
            <Link to="/" className="nav-link [&.active]:active-link">Dashboard</Link>
            <Link to="/equipos" className="nav-link [&.active]:active-link">Diagnóstico CPU</Link>
            <Link to="/monitores" className="nav-link [&.active]:active-link">Monitores</Link>
            
            <div className="nav-item-dropdown" style={{ position: 'relative' }}>
              <span className="nav-link">Portátiles <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{marginLeft: '4px'}}><path d="M6 9l6 6 6-6"/></svg></span>
              <div className="dropdown-menu-premium" style={{ display: 'none', position: 'absolute', top: '100%', left: 0, background: 'var(--bg-dark)', border: '1px solid var(--border-color)', borderRadius: '8px', padding: '8px', minWidth: '150px', zIndex: 10 }}>
                <Link to="/portatiles" className="nav-link" style={{ display: 'block', padding: '8px 12px' }}>Bitácora</Link>
                <Link to="/portatiles/evidencia" className="nav-link" style={{ display: 'block', padding: '8px 12px' }}>Subir Evidencia</Link>
              </div>
            </div>
            
            <Link to="/soplado" className="nav-link [&.active]:active-link">Soplado</Link>
            <Link to="/diademas" className="nav-link [&.active]:active-link">Diademas</Link>
            
            <Link to="/inventario" className="nav-link [&.active]:active-link">Inventario</Link>
            <Link to="/cargue_masivo" className="nav-link [&.active]:active-link">Cargue Masivo</Link>
            <Link to="/trazabilidad" className="nav-link [&.active]:active-link">Trazabilidad</Link>
            <Link to="/usuarios" className="nav-link [&.active]:active-link">Usuarios</Link>
            
            <div className="nav-user-menu">
               <span className="nav-link user-pill">
                 Admin <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{marginLeft: '4px'}}><path d="M6 9l6 6 6-6"/></svg>
               </span>
            </div>
          </div>
        </div>
      </nav>

      {/* Main Content Area */}
      <main className="main-content">
        <div className="container">
          <Outlet />
        </div>
      </main>
    </div>
  )
}
