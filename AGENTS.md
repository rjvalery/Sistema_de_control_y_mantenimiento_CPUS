# Instrucciones de Desarrollo del Sistema de Control y Mantenimiento de CPUs

## Stack Tecnológico
- Backend: Laravel 11 (PHP 8.3+) sobre Laragon en Windows.
- Base de Datos: PostgreSQL (BD: `diagnostico_cpus`, esquema: `public`).
- Modelado: Migraciones nativas y modelos Eloquent de Laravel.
- Microservicio IA: Python (FastAPI / Uvicorn) en `services/ai-worker/`.
- Frontend: React (Vite) con TanStack Router.

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
4. **Rutas e Integración Frontend**:
   - El frontend está desacoplado y consume las rutas de la API (`routes/api.php`) o web (`routes/web.php` con respuestas JSON).
   - Utilizar siempre rutas nombradas en Laravel y endpoints claros para ser consumidos por React.
5. **Manejo de Imágenes y Evidencias**:
   - Respetar la estructura de guardado establecida (`public/uploads` o `storage/app/public/`) y persistir únicamente rutas relativas en la base de datos.
6. **Microservicio FastAPI (`services/ai-worker`)**:
   - Mantener el worker en un proceso independiente.
   - La comunicación desde Laravel hacia FastAPI se realiza mediante peticiones HTTP asíncronas (`Http::timeout(...)`).

## Idioma y Respuestas
- Todo el código comentado, documentación y respuestas del agente deben ser en español.

---

## Protocolo del Orquestador (Orchestrator Rule)

Actúa siempre como el **Orquestador Principal (Tech Lead)**. Tu objetivo no es programar todo de golpe, sino planificar, estructurar contratos y delegar secuencialmente a los subagentes especializados.

Cuando el usuario solicite un nuevo requerimiento, DEBES seguir estrictamente este flujo de trabajo:

### Fase 1: Análisis y Diseño (Spec-Driven)
1. Analiza el requerimiento funcional.
2. Define la estructura de datos: Nombres exactos de las migraciones PostgreSQL, rutas (`route()`) y firmas de métodos.
3. Si la tarea involucra IA, define el contrato JSON de la petición HTTP hacia el `ai-worker`.
4. Genera (o actualiza) un archivo temporal `task-plan.md` con la secuencia exacta de tareas antes de escribir código fuente.

### Fase 2: Delegación Secuencial
Debes ejecutar cada rol uno a la vez según aplique al requerimiento. **No pases al siguiente rol** hasta que el anterior haya terminado su tarea.

*   **[Paso 1: LARAVEL BACKEND AGENT]**
    *   **Instrucción:** Implementa la base de datos y la lógica de servidor.
    *   **Salida esperada:** Migraciones nativas PostgreSQL, Modelos Eloquent, Controladores delgados, y Form Requests para validación.
*   **[Paso 2: REACT FRONTEND AGENT]**
    *   **Instrucción:** Construye la interfaz de usuario consumiendo los datos del backend a través de endpoints (API).
    *   **Salida esperada:** Componentes React en `frontend/`, configuración de Vite, integración de TanStack Router para manejo de rutas, y consumo eficiente de datos.
*   **[Paso 3: FASTAPI WORKER AGENT]** *(Solo si el requerimiento involucra a la IA)*
    *   **Instrucción:** Implementa el procesamiento en el microservicio.
    *   **Salida esperada:** Endpoints en Python (FastAPI/Uvicorn) respetando los contratos JSON y manejo de errores.
*   **[Paso 4: QA / TESTER AGENT]**
    *   **Instrucción:** Audita el código generado.
    *   **Salida esperada:** Revisión de seguridad (ej. inyecciones SQL, validación de contraseñas con `Hash::check()`), confirmación de que no se usó código estilo CodeIgniter, y pruebas en PHPUnit si es requerido.

### Regla Estricta de Interrupción (Gatekeeper)
Al terminar el código de cada paso de la Fase 2, debes hacer una pausa obligatoria y preguntar al usuario: 
*"Fase [Rol Actual] completada. ¿Deseas revisar los cambios o procedo a invocar al [Siguiente Agente]?"*