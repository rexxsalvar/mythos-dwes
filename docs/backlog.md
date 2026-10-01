# Product Backlog inicial

Versión 1 · 01/10/2026. Estado actual de cada elemento: [tablero](tablero.md). P1 = necesario para mínimos o núcleo; P2 = complemento del producto; P3 = ampliación opcional. El sprint es una previsión, no un compromiso cerrado de todos los detalles.

Cada historia se descompondrá en tareas de desarrollo al planificar su sprint. Los criterios de aceptación siguientes permiten comprobar su resultado sin imponer todavía el diseño completo.

## Tareas del sprint 1

| ID | Prioridad | Entregable | Criterio de aceptación |
|---|---|---|---|
| T-01 | P1 | Elegir temática y alcance | Mythos identificado como negocio real; reserva, partida y mantenimiento delimitados. |
| T-02 | P1 | Propuesta formal | PDF breve con alcance, entidades, roles, mínimos/ampliaciones y calendario docente; maquetación revisada. |
| T-03 | P1 | Crear repositorio Git local | Rama main, README, exclusión de temporales/secretos y primer commit con los entregables. |
| T-04 | P1 | Redactar Product Backlog | Historias con objetivo, prioridad, sprint y criterios de aceptación; tareas técnicas identificadas. |
| T-05 | P1 | Configurar tablero local | Estados definidos, trabajo de S1 separado del backlog, límites y definición de terminado. |
| T-06 | P1 | Publicar en GitHub privado y Projects | Repositorio remoto privado, commit publicado, proyecto con tarjetas y estados; enlaces verificados. Depende de T-02 a T-05 y de acceso a GitHub. |

## Historias de usuario

### HU-01 · Acceso y permisos

**P1 · Sprint 3 · Depende de T-07.** Como cliente o administrador, quiero acceder a mi área para realizar solo las operaciones autorizadas.

- Registro, acceso y cierre de sesión; rutas públicas y privadas separadas.
- Cliente y administrador implementados con Laravel-permission. Se prevén perfiles adicionales para el personal.
- ReservationPolicy impide consultar o modificar reservas ajenas, salvo permiso administrativo.

### HU-02 · Catálogo de experiencias

**P1 · Sprint 4 · Depende de HU-01.** Como visitante, quiero conocer las experiencias para elegir una partida adecuada a mi grupo.

- Listado y detalle con duración, capacidad, ubicación y tarifa por participantes.
- Administración crea, consulta, modifica y da de baja experiencias; las vinculadas a histórico se archivan o restringen.
- Expedición Maldita aparece como dato real de referencia, sin clientes reales en las pruebas.

### HU-03 · Salas y sesiones

**P1 · Sprint 4 · Depende de HU-02.** Como administrador, quiero organizar salas y horarios para ofrecer sesiones que podamos atender.

- CRUD de salas y sesiones con experiencia, horario, responsable y estado.
- Se rechazan solapamientos de sala o responsable; el margen de preparación es configurable.
- Las sesiones no jugables no pueden aceptar nuevas reservas.

### HU-04 · Reservar una partida

**P1 · Sprint 4 · Depende de HU-03.** Como cliente, quiero reservar una sesión para asegurar la partida de mi grupo.

- Participantes dentro del mínimo y máximo; tarifa calculada por el servidor y guardada en la reserva.
- Dos intentos simultáneos no generan dos reservas activas de la misma sesión.
- El cliente ve próximas reservas mediante un scope funcional; las pendientes tienen una caducidad definida.

### HU-05 · Consultar y cancelar reservas

**P1 · Sprint 4 · Depende de HU-04.** Como cliente, quiero consultar mi reserva y cancelarla cuando esté permitido para gestionar cambios de planes.

- El historial y detalle se restringen al titular y al personal autorizado.
- La cancelación comprueba estado y antelación; libera la sesión solo si sigue siendo jugable y conserva el histórico.
- Recepción o administración pueden realizar cambios autorizados con nueva validación de horario y tarifa.

### HU-06 · Gestión interactiva

**P1 · Sprint 5 · Depende de HU-02 a HU-05.** Como personal, quiero gestionar el catálogo, las reservas y la agenda sin recargas completas para agilizar la atención.

- Tres clases Livewire: CRUD completo de experiencias, CRUD completo de reservas y agenda filtrable.
- Los CRUD cubren creación, listado/detalle, edición y baja con las restricciones del dominio.
- Un scope complejo combina fecha, reservas activas y bloqueos para filtrar sesiones disponibles.

### HU-07 · Equipos

**P2 · Sprint 5 · Depende de HU-04.** Como cliente, quiero identificar a mi equipo para asociar nuestras reservas y resultados.

- Relación N:N entre equipos y usuarios con función y fecha de incorporación en el pivote.
- Solo los usuarios autorizados administran un equipo; los participantes sin cuenta cuentan igualmente para el aforo.

### HU-08 · Preparación y resultados

**P1 · Sprint 5 · Depende de HU-03, HU-04.** Como game master, quiero preparar y cerrar mis partidas para registrar cómo se desarrolló cada experiencia.

- Registro de llegada y lista de preparación; comprobaciones obligatorias antes del inicio.
- GameSessionPolicy restringe las modificaciones a sesiones asignadas o a administración.
- Pistas utilizadas en N:N con momento y penalización; resultado final único con tiempo y éxito o no.

### HU-09 · Incidencias y mantenimiento

**P1 · Sprint 5 · Depende de HU-03.** Como técnico, quiero atender averías para recuperar la disponibilidad de una sala.

- CRUD de incidencias y actuaciones con responsable, gravedad y estado.
- Una incidencia crítica impide nuevas reservas e inicio de partidas; las ya confirmadas se señalan para revisión.
- Un scope complejo filtra incidencias por relación con sala, gravedad y estado. Resolver una no reabre la sala si persisten otros bloqueos.

### HU-10 · API para clientes externos

**P1 · Sprint 6 · Depende de HU-04, HU-05.** Como usuario de un cliente externo, quiero consultar experiencias y gestionar reservas mediante la API.

- Tres modelos: Experience y Reservation con dos CRUD completos, y GameSession con consulta de disponibilidad.
- Reservas autenticadas con Sanctum; escritura administrativa protegida también en experiencias. Se aplican las mismas reglas que en la web.
- Borrador de documentación con peticiones, respuestas, errores y autenticación; bajas/cancelaciones documentadas.

### HU-11 · Avisos y trabajo en segundo plano

**P1 · Sprint 7 · Depende de HU-04, HU-09.** Como cliente, quiero recibir confirmación y recordatorio para conocer los datos de mi partida.

- Dos eventos: ReservationConfirmed e IncidentReported; listeners de correo de confirmación y aviso interno al personal.
- Dos jobs: generación de documento y envío de recordatorios; al menos el segundo utiliza colas. La plantilla PDF se completará en S8.
- Dos emails diferenciados; no se envían recordatorios de reservas canceladas ni se duplican por reintentos.

### HU-12 · Documentos e informes

**P1 · Sprint 8 · Depende de HU-04, HU-11.** Como cliente o administrador, quiero descargar documentos para acreditar reservas y revisar la actividad.

- Dos PDF con laravel-dompdf: justificante de reserva e informe mensual de ocupación e importes.
- El informe complejo incluye filtros, tablas, agregaciones y totales; cobros e importes reservados se distinguen.
- El cliente no descarga justificantes de otros usuarios; el informe queda restringido a administración.

### HU-13 · Dos idiomas

**P1 · Sprint 8 · Depende de HU-06.** Como usuario, quiero usar la aplicación en español o inglés para comprender sus contenidos.

- Selector y traducciones en ES/EN para navegación, formularios, validación y pantallas principales.
- La elección se conserva durante la sesión y no cambia permisos ni datos de reserva.

### HU-14 · Diploma y valoración

**P3 · Sprint 9 · Depende de HU-08, HU-12 y mínimos verificados.** Como cliente, quiero recordar y valorar mi partida después de jugar.

- Diploma con datos de resultado; criterio de emisión definido antes de implementar.
- Una valoración por reserva completada, realizada por su titular; moderación administrativa.

### HU-15 · Bono regalo

**P3 · Sprint 10 · Depende de HU-04 y mínimos verificados.** Como cliente, quiero canjear un bono para reservar una experiencia regalada.

- Código con vigencia y uso único; no puede consumirse dos veces por peticiones simultáneas.
- El importe o grupo cubierto se valida al reservar. Pago online y logística del bono físico no se incluyen automáticamente.

### HU-16 · Exportación de actividad

**P3 · Sprint 10 · Depende de HU-12 y mínimos verificados.** Como administrador, quiero exportar informes para analizarlos fuera de la aplicación.

- Exportación Excel con los mismos filtros y totales que el informe.
- Acceso administrativo y ausencia de columnas personales innecesarias.

## Tareas técnicas posteriores

| ID | Prioridad / sprint | Trabajo | Criterio de aceptación |
|---|---|---|---|
| T-07 | P1 / 2 | E-R, migraciones, factories y seeders | Diagrama con 1:N y N:N; pivote con datos propios; claves y restricciones; carga de datos de prueba reproducible. |
| T-08 | P1 / 4 | Validación y componentes | FormRequest en formularios de más de dos campos; componentes Blade de input, fechas, select, labels y checkbox utilizados en pantallas reales. |
| T-09 | P1 / 7 | Tres comandos Artisan | Caducar reservas, preparar recordatorios y generar informe; el último se llama también desde código autorizado. Comandos verificables sin efectos duplicados. |
| T-10 | P1 / 9 | Pest y correcciones | Cobertura mínima del 85 % del código de aplicación; pruebas de permisos, aforo, concurrencia, cancelaciones, bloqueos y documentos; errores corregidos. |
| T-11 | P1 / 10 | Cierre técnico | Documentación API y README completos, mínimos comprobados y versión candidata. Revisar Git sin reescribir ni borrar el historial como tarea rutinaria. |
| T-12 | P1 / final | Demo y entrega | Integración final comprobada, guion de demo y material de defensa; entrega oficial del alumno el 12/02/2027. |

## Definición de terminado

Una tarjeta pasa a **Hecho** cuando cumple sus criterios, tiene evidencia (archivo, commit o resultado verificable) y no presenta defectos pendientes que impidan su uso. Para código se ejecutan las pruebas relevantes; para documentación se revisan contenido, enlaces y formato. Preparar la documentación no equivale a implementar las funciones que describe.

