# Instrucciones de Desarrollo del Sistema de Control y Mantenimiento de CPUs

## Stack Tecnológico
- Backend: Laravel 11 (PHP 8.3+) sobre Laragon en Windows.
- Base de Datos: PostgreSQL (BD: `diagnostico_cpus`, esquema: `public`).
- Modelado: Migraciones nativas y modelos Eloquent de Laravel.
- Microservicio IA: Python (FastAPI / Uvicorn) en `services/ai-worker/`.
- Frontend: Vistas Blade con Bootstrap y JavaScript nativo.

## Principios de Diseño y Buenas Prácticas
1. **KISS y YAGNI**:
   - Mantener soluciones simples y directas.
   - No crear abstracciones, patrones complejos o capas intermedias para casos hipotéticos futuros que aún no se necesitan.
2. **DRY (Don't Repeat Yourself)**:
   - Centralizar la lógica repetida en funciones, traits, servicios o utilidades comunes.
3. **Single Responsibility (SRP)**:
   - Cada módulo, clase o función debe resolver una única tarea específica (controladores delgados, validaciones en Form Requests, lógica de dominio separada).
4. **Dependency Inversion (DIP)**:
   - Depender de abstracciones (interfaces o contratos) y no de implementaciones rígidas, facilitando la modularidad y el testeo.

## Reglas Críticas del Proyecto
1. **No mezclar con CodeIgniter 4**:
   - El proyecto anterior (`..._CPUS_laptos-main`) es únicamente de referencia funcional. Todo el código nuevo debe respetar la arquitectura moderna y convenciones de Laravel 11.
2. **PostgreSQL Estricto**:
   - Utilizar tipos de datos nativos compatibles con PostgreSQL (ej. `bigIncrements`, `timestamp`, `text`, `jsonb`).
   - Respetar los nombres exactos de tablas y llaves foráneas definidas en las migraciones de `backend/database/migrations/`.
3. **Autenticación**:
   - Utilizar la tabla personalizada `usuarios` y el modelo `app/Models/Usuario.php` (configurado en `config/auth.php`).
   - Validar contraseñas siempre mediante `Hash::check()` y generarlas con `Hash::make()`.
4. **Vistas y Nombres de Rutas**:
   - Antes de invocar `view(...)` en un controlador, verificar que la plantilla Blade exista en `backend/resources/views/` para evitar excepciones `View not found`.
   - Utilizar siempre rutas nombradas: `route('nombre.ruta')`.
5. **Manejo de Imágenes y Evidencias**:
   - Respetar la estructura de guardado establecida (`public/uploads` o `storage/app/public/`) y persistir únicamente rutas relativas en la base de datos.
6. **Microservicio FastAPI (`services/ai-worker`)**:
   - Mantener el worker en un proceso independiente.
   - La comunicación desde Laravel hacia FastAPI se realiza mediante peticiones HTTP asíncronas (`Http::timeout(...)`).

## Idioma y Respuestas
- Todo el código comentado, documentación y respuestas del agente deben ser en español.