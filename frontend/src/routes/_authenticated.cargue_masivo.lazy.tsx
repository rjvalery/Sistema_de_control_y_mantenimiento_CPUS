import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { useRef, useState } from 'react'

export const Route = createLazyFileRoute('/_authenticated/cargue_masivo')({
  component: CargueMasivoIndex,
})

function CargueMasivoIndex() {
  const [file, setFile] = useState<File | null>(null)
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [isDragActive, setIsDragActive] = useState(false)

  const handleDrag = (e: React.DragEvent) => {
    e.preventDefault()
    e.stopPropagation()
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setIsDragActive(true)
    } else if (e.type === 'dragleave') {
      setIsDragActive(false)
    }
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()
    e.stopPropagation()
    setIsDragActive(false)
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      setFile(e.dataTransfer.files[0])
    }
  }

  return (
    <div style={{ maxWidth: '1200px', margin: '0 auto' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px', flexWrap: 'wrap', gap: '16px' }}>
        <div>
          <h1 style={{ fontSize: '1.5rem', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px', margin: 0, color: 'var(--primary)' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path>
              <path d="M12 12v9"></path>
              <path d="m8 17 4-4 4 4"></path>
            </svg>
            Cargue Masivo de Equipos
          </h1>
          <p style={{ color: 'var(--text-muted)', margin: '4px 0 0 0', fontSize: '0.9rem' }}>
            Importación masiva de planillas Excel/CSV a <code>inventario_general</code>.
          </p>
        </div>
        <div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
          <button className="btn-premium" style={{ width: 'auto', background: 'var(--success)' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="16" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Plantilla Excel/CSV
          </button>
          <Link to="/inventario" className="btn-premium" style={{ width: 'auto', background: 'transparent', border: '1px solid var(--border-color)' }}>
            Ver Inventario General
          </Link>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '24px' }}>
        
        {/* Upload Column */}
        <div className="card-premium">
          <div className="card-header-premium">
            <h2 style={{ fontSize: '1.1rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path><path d="M12 12v9"></path><path d="m8 17 4-4 4 4"></path></svg>
              Subir Archivo para Inserción en Base de Datos
            </h2>
          </div>
          <div className="card-body-premium">
            <div style={{ marginBottom: '24px' }}>
              <label className="input-label">Archivo Excel (.xlsx, .xls) o CSV (.csv) *</label>
              <div 
                onDragEnter={handleDrag}
                onDragLeave={handleDrag}
                onDragOver={handleDrag}
                onDrop={handleDrop}
                onClick={() => fileInputRef.current?.click()}
                style={{
                  border: `2px dashed ${isDragActive ? 'var(--success)' : 'var(--primary)'}`,
                  borderRadius: '12px',
                  padding: '40px 20px',
                  textAlign: 'center',
                  cursor: 'pointer',
                  background: isDragActive ? 'rgba(16, 185, 129, 0.05)' : 'rgba(59, 130, 246, 0.05)',
                  transition: 'all 0.2s ease'
                }}
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke={file ? 'var(--success)' : 'var(--primary)'} strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" style={{ marginBottom: '16px' }}><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                {file ? (
                  <>
                    <h4 style={{ margin: '0 0 8px 0', color: 'var(--success)' }}>Archivo seleccionado: {file.name}</h4>
                    <p style={{ margin: 0, color: 'var(--text-muted)', fontSize: '0.85rem' }}>
                      {(file.size / 1024).toFixed(1)} KB - Listo para procesar
                    </p>
                  </>
                ) : (
                  <>
                    <h4 style={{ margin: '0 0 8px 0' }}>Haz clic o arrastra para seleccionar tu archivo</h4>
                    <p style={{ margin: 0, color: 'var(--text-muted)', fontSize: '0.85rem' }}>
                      Admite libros de Excel (.xlsx, .xls) y archivos delimitados (.csv, .txt).
                    </p>
                  </>
                )}
                <input 
                  type="file" 
                  ref={fileInputRef} 
                  style={{ display: 'none' }} 
                  accept=".xlsx, .xls, .csv, .txt" 
                  onChange={(e) => {
                    if (e.target.files && e.target.files[0]) {
                      setFile(e.target.files[0])
                    }
                  }}
                />
              </div>
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                Tabla de destino: <code style={{ color: 'var(--info)' }}>inventario_general</code>
              </div>
              <button className="btn-premium" style={{ width: 'auto' }} disabled={!file}>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Importar a Base de Datos
              </button>
            </div>
          </div>
        </div>

        {/* Instructions Column */}
        <div className="card-premium">
          <div className="card-header-premium" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h2 style={{ fontSize: '1.1rem', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
              Estructura de Columnas
            </h2>
          </div>
          <div className="card-body-premium">
            <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '16px' }}>
              Estructura de 8 columnas adaptada a la plantilla oficial <strong>Formato en Cubic</strong>:
            </p>
            <div className="table-container">
              <table className="table-premium" style={{ fontSize: '0.85rem' }}>
                <thead>
                  <tr>
                    <th>Columna (Excel/CSV)</th>
                    <th>Tipo</th>
                    <th>Ejemplo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><code>Identificador 1</code></td>
                    <td><span className="badge" style={{ background: 'rgba(239, 68, 68, 0.2)', color: '#f87171' }}>Requerido*</span></td>
                    <td>ACT-10021</td>
                  </tr>
                  <tr>
                    <td><code>Identificador 2</code></td>
                    <td><span className="badge" style={{ background: 'rgba(239, 68, 68, 0.2)', color: '#f87171' }}>Requerido*</span></td>
                    <td>SN-MBP99201</td>
                  </tr>
                  <tr>
                    <td><code>Ref. Principal</code></td>
                    <td><span className="badge badge-secondary">Opcional</span></td>
                    <td>MacBook Pro 16 M1</td>
                  </tr>
                  <tr>
                    <td><code>Descripción</code></td>
                    <td><span className="badge badge-secondary">Opcional</span></td>
                    <td>Laptop Apple Corporativo</td>
                  </tr>
                  <tr>
                    <td><code>Zona Origen</code></td>
                    <td><span className="badge badge-secondary">Opcional</span></td>
                    <td>Sede Central</td>
                  </tr>
                  <tr>
                    <td><code>Ubicación Origen</code></td>
                    <td><span className="badge badge-secondary">Opcional</span></td>
                    <td>Piso 3 - Puesto 302</td>
                  </tr>
                  <tr>
                    <td><code>Verificado</code></td>
                    <td><span className="badge badge-secondary">Opcional</span></td>
                    <td>Verificado / Pendiente</td>
                  </tr>
                  <tr>
                    <td><code>Observaciones</code></td>
                    <td><span className="badge badge-secondary">Opcional</span></td>
                    <td>Equipo en buen estado</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div style={{ marginTop: '16px', fontSize: '0.8rem', color: 'var(--text-muted)', fontStyle: 'italic' }}>
              * Se requiere al menos Identificador 1 o Identificador 2 para procesar la fila.
            </div>
          </div>
        </div>

      </div>
    </div>
  )
}
