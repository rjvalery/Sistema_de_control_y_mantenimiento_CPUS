# Especificación Funcional y Operativa Oficial de Módulos
**Proyecto:** Sistema de Control y Mantenimiento de CPUs

Esta documentación describe la estructura, trazabilidad y flujo operativo físico en el taller de los diferentes módulos que conforman la arquitectura Laravel del sistema.

---

### Módulo: Inventario General
**(Ruta: `/inventario`, Controlador: `InventarioController`)**

**1. Características de la Pantalla y UI:**
- **Tabla principal:** Lista los registros base de los equipos desde la tabla `inventario_general`. Expone información como identificadores únicos (placa, serial), referencia, tipo de equipo, ubicación origen, etc.
- **Paginación y Búsqueda:** Soporta paginación dinámica (hasta 1000 registros, default 250) y motor de búsqueda para encontrar equipos por placa, serial, identificador 1 o 2, referencia principal o descripción.
- **Filtros:** Permite filtrar los registros por estado ("pendientes" o "agregados/intervenidos") y por número de traslado específico.
- **Acciones:** Cuenta con botones para ver el detalle en profundidad del equipo (ruta show) y un botón clave para forzar la **sincronización** del inventario.

**2. Histórico, Trazabilidad y Exportaciones:**
- La base de datos asocia históricamente a cada registro las interacciones posteriores de los técnicos usando la `placa_id` y el `serial` del equipo (contemplados en el Cargue Masivo como `identificador_1` e `identificador_2`).
- Se registra a nivel histórico qué técnico (`analista_intervencion`), en qué módulo (`modulo_intervencion`) y en qué fecha (`fecha_intervencion`) validó y trabajó la máquina que ingresó al inventario.
- Permite conciliar el inventario sincronizando datos del historial de intervenciones técnicas en el sistema.

**3. Flujo Operativo en Taller y Transición de Estados:**
- **Proceso Físico:** Este módulo representa la "recepción administrativa" en el almacén. Es la lista maestra contra la cual el taller se rige.
- **Transición de Estados:** En la base de datos, el punto de mutación clave ocurre cuando los módulos técnicos (Soplado, CPU, Portátiles) registran la gestión de un equipo; en ese momento, el campo `intervenido` muta de `0` a `1` a nivel del Inventario General.
- **Permisos:** Requiere ser `admin` o poseer rol/permiso de analista para visualizar (`inventario.ver`). La sincronización requiere rol administrativo.

---

### Módulo: Cargue Masivo
**(Ruta: `/cargue-masivo`, Controlador: `CargueMasivoController`)**

**1. Características de la Pantalla y UI:**
- **Interfaz Principal:** Formulario simplificado que pide el Número de Traslado (lote de recepción) y un selector de archivo.
- **Tipos de archivo:** Acepta listados en formatos `.xlsx`, `.xls`, `.csv` o `.txt`.
- **Acciones:** Incluye un botón dedicado para la descarga de la plantilla oficial de Excel.

**2. Histórico, Trazabilidad y Exportaciones:**
- El módulo alimenta masivamente la tabla de `inventario_general`, estampando sobre todos los registros inyectados un histórico base: quién realizó la carga (`usuario_cargue`) y el lote físico del que provienen (`num_traslado`).
- Garantiza que cualquier máquina en el sistema conserve registro del archivo de origen físico que la ingresó a las bodegas.

**3. Flujo Operativo en Taller y Transición de Estados:**
- **Proceso Físico:** Se ejecuta cuando llega al muelle de la empresa un lote o camión con activos. Un supervisor toma el "Manifiesto de Carga" (Excel) y lo inyecta en el sistema.
- **Transición de Estados:** Al momento de "Procesar", se realiza un mapeo inteligente de columnas y todos los equipos se crean en base de datos en estado bruto (`intervenido = 0`).
- **Permisos:** Uso exclusivo para roles administrativos o usuarios con el permiso estricto de `cargue_masivo.ejecutar`.

---

### Módulo: Mantenimiento Preventivo / Soplado
**(Ruta: `/soplado`, Controlador: `SopladoController`)**

**1. Características de la Pantalla y UI:**
- **Bitácora (Tabla):** Muestra los registros históricos cargados, con paginación y búsqueda cruzada por placa, traslado y nombre del analista.
- **Formulario de Registro:** Una UI específica para reportar estado físico preliminar. Campos de selección sobre: si el equipo energiza, si da video, si permite ingresar a la BIOS, tipo de suciedad interna (si contiene polvo o cucarachas) y si se aplicó pasta térmica.
- **Acciones:** Captura o carga de imagen/fotografía para documentar la evidencia del interior de la máquina antes del mantenimiento.

**2. Histórico, Trazabilidad y Exportaciones:**
- Registra cada acción en la tabla `soplado_registros`. Toda limpieza se ata a la `placa_id` de la máquina.
- Deja una estampa histórica de quién hizo la limpieza primaria y adjunta la ruta de la evidencia fotográfica (`foto_ruta`).

**3. Flujo Operativo en Taller y Transición de Estados:**
- **Proceso Físico:** Este módulo representa la **última transición** antes de que el equipo cumpla su flujo de proceso. La máquina llega a esta área solo si desde la estación de Diagnóstico el analista determina que requiere soplado o mantenimiento físico.
- **Transición de Estados:** Al dispararse el guardado, se crea el registro de soplado, y esto automáticamente **muta el inventario general**, marcando el equipo como "intervenido = 1" e indicando que pasó por el módulo "soplado".
- **Permisos:** Se autoriza a analistas o roles con permisos `soplado.ver_bitacora` y `soplado.registrar`.

---

### Módulo: Diagnóstico / Intervención de CPUs
**(Ruta: `/equipos`, Controlador: `EquiposController`)**

**1. Características de la Pantalla y UI:**
- **Bitácora (Tabla):** Lista todas las intervenciones de CPU con filtros para facilitar la búsqueda por placa, traslado, estado de la gestión y técnico.
- **Formulario Clínico:** Formulario donde se declara el "Tipo de Gestión", diagnóstico eléctrico (energiza, da video), estado final del equipo, origen de piezas utilizadas, motivo en caso de que la máquina deba darse de baja, y observaciones detalladas de la novedad técnica.
- **Acciones:** Posibilidad de subir una fotografía documentando el hardware dañado o la intervención (`foto_equipo`).

**2. Histórico, Trazabilidad y Exportaciones:**
- La tabla subyacente es `equipos`. Representa la bitácora de intervenciones profundas.
- Ata el dictamen de ingeniería con el equipo y permite cruzar esto con posibles garantías.

**3. Flujo Operativo en Taller y Transición de Estados:**
- **Proceso Físico:** Esta es la **primera estación** del proceso desde que el equipo entra al área de servicio o garantías. El técnico diagnostica la máquina y decide el flujo: si la máquina está limpia y tiene su pasta térmica, el proceso se cierra aquí; de lo contrario, se envía a Soplado.
- **Transición de Estados:** En sistema, cuando se procesa el formulario de CPU, el registro se graba y un trigger en el software altera la tabla de inventario asumiendo que ya fue "intervenido" bajo la línea de "Diagnóstico CPU".
- **Permisos:** Funciones regidas bajo `cpus.ver_bitacora` (leer) y `cpus.registrar` (crear), separando lógicamente esta estación de trabajo.

---

### Módulo: Portátiles y Garantías
**(Ruta: `/portatiles`, Controlador: `PortatilesController`)**

**1. Características de la Pantalla y UI:**
- **Bitácora (Tabla):** Presenta el listado histórico de laptops procesadas.
- **Formulario Especializado:** Añade filtros y botones que son vitales solo en laptops: Realizó el "Test de Lenovo", captura del Serial de Disco, especificación de número de "Ticket" del proveedor, e identificación de partes FRU y piezas a solicitar.
- **Acciones:** Cuenta con una interfaz paralela y modal exclusivo para adjuntar fotos de evidencia de manera asíncrona post-diagnóstico.

**2. Histórico, Trazabilidad y Exportaciones:**
- Los datos viven en `garantia_portatil`.
- Provee un puente crítico de trazabilidad entre el taller local y los proveedores externos de garantías.
- Registra no solo quién analizó, sino qué piezas se tramitaron en reposición y sus seriales atados.

**3. Flujo Operativo en Taller y Transición de Estados:**
- **Proceso Físico:** Al igual que con los equipos de escritorio, esta es la **primera estación** para laptops al ingresar al área de garantías. Se diagnostica la máquina, se corren pruebas del fabricante y se decide si el proceso culmina aquí o se transiciona a Soplado.
- **Transición de Estados:** Igual que en CPU, al completar el formulario técnico, la máquina es listada como "intervenida" en la maestra central por "Diagnóstico Portátiles". Además, en esta UI, el estado de la máquina frecuentemente muta a "Reparado" especificando por quién (Ej. un técnico del fabricante).
- **Permisos:** Acceso mediante `portatiles.ver_bitacora` y `portatiles.registrar`.

---

### Módulo: Trazabilidad / Hoja de Vida
**(Ruta: `/trazabilidad`, Controlador: `TrazabilidadController`)**

**1. Características de la Pantalla y UI:**
- **Interfaz:** Funciona primordialmente como un buscador de línea de tiempo ("Timeline"). No cuenta con tabla general; cuenta con un campo único que dispara consultas AJAX mediante la placa o serial.
- **Presentación Visual:** Construye dinámicamente un árbol o historia de vida del equipo según todos los puntos por los que ha pasado.

**2. Histórico, Trazabilidad y Exportaciones:**
- No es un módulo generador de data, sino agregador.
- Recoge información de `InventarioGeneral`, `SopladoRegistro`, `Equipo` y `GarantiaPortatil`, compilando eventos cronológicamente.

**3. Flujo Operativo en Taller y Transición de Estados:**
- **Proceso Físico:** Sirve de consulta rápida para técnicos atascados en un caso y para supervisores investigando pérdidas o daños no reportados; les permite saber en qué mesas de trabajo previas fue registrada la máquina.
- **Transición de Estados:** 100% solo-lectura; no altera ningún estado.
- **Permisos:** Se encuentra protegido mediante el permiso `trazabilidad.ver`.

---

### Dashboard de Control Operativo
**(Ruta: `/dashboard`, Controlador: `DashboardController`)**

**1. Características de la Pantalla y UI:**
- **Tablero de Mandos:** Componentes visuales de tipo tarjeta y gráficos que compilan métricas rápidas.
- **Filtros Temporales:** Permite segmentar operaciones en el día, semana, mes o año.
- **Acciones:** Incorpora el botón estelar para Exportar Bitácora.

**2. Histórico, Trazabilidad y Exportaciones:**
- **Exportación:** Posee capacidades de exportación a CSV de las bitácoras filtradas. El formato `.csv` generado cruza la maestra de inventario con las intervenciones técnicas e incluye columnas sólidas: ID/Radicado, Placa, Serial, Módulo Atendido, Marca, Modelo, Condición y Analista interviniente.

**3. Flujo Operativo en Taller y Transición de Estados:**
- **Proceso Físico:** Es utilizado como un monitor de pared o por el jefe de laboratorio en su computadora para monitorear en tiempo real la productividad de las líneas (ej. evaluar si se cumple la meta diaria de 10 intervenciones base).
- **Transición de Estados:** Exclusivo de monitoreo de KPIs y cuellos de botella; no dispara inserciones ni mutaciones en registros.
- **Permisos:** La interfaz se adapta al rol. Si el usuario tiene un permiso de restricción como `dashboard.ver_solo_propio`, el panel y las exportaciones solo listarán su productividad técnica y no la del taller completo. Solo admins ven todo.
