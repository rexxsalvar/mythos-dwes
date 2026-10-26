# Product Backlog

Revisión: 26/10/2026, por las [correcciones de S1](revision-sprint-1.md) y el [enunciado de S3](sprint-3-enunciado.md). Se conservan 16 historias y 12 tareas, sus identificadores y las fechas oficiales. P1 = obligatorio o núcleo; P2 = complemento de producto; P3 = ampliación opcional. El [tablero](tablero.md) refleja el seguimiento; HU-01 tiene evidencia de implementación y las demás historias siguen pendientes.

## Tareas del sprint 1

| ID | Prioridad | Entregable | Criterio de aceptación |
|---|---|---|---|
| T-01 | P1 | Tema y alcance | Mythos como nombre definitivo; reglas académicas y exclusiones diferenciadas de las condiciones reales del negocio. EscapeHub solo como origen del material. |
| T-02 | P1 | Propuesta formal | PDF breve revisado con siete recursos API, cinco CRUD/policies, siete scopes/Livewire/comandos, cinco pares evento-listener/jobs/correos, tres informes complejos, ES/EN y 85 % obligatorio. |
| T-03 | P1 | Git local | Rama main, historial conservado y exclusión de secretos, dependencias y temporales. |
| T-04 | P1 | Backlog | 28 elementos sin duplicados, criterios comprobables y dependencias actualizadas con las correcciones. |
| T-05 | P1 | Tablero local | Seis estados, límites y separación entre planificación e implementación. |
| T-06 | P1 | GitHub y Projects | Repositorio y tablero existentes, enlaces públicos, rama main y criterios de las tarjetas coherentes con la revisión; no crear otro proyecto. |

## Historias de usuario

### HU-01 · Acceso y permisos

**P1 · Sprint 3 · Depende de T-07. Implementada el 26/10/2026 según el nuevo enunciado.** Como cliente o personal, quiero acceder a mi área con las operaciones de mi perfil.

- Registro, inicio y cierre de sesión; sesiones renovadas, recordar acceso y límite de intentos.
- Cinco roles efectivos con Laravel-permission: cliente, recepción, game master, mantenimiento y administrador.
- Rutas nombradas por área con middleware; anónimo redirigido a acceso (401 si solicita JSON), autenticado sin permiso recibe 403.
- ReservationPolicy para reservas propias/personal autorizado y GameSessionPolicy para partidas asignadas/personal autorizado, aplicadas en consultas reales.
- Registro siempre como cliente; perfil propio validado sin permitir cambio de rol, identidad o activación. Cuentas desactivadas sin acceso.
- FormRequest y componentes en los cuatro formularios existentes; errores junto al campo y datos válidos conservados.
- Pruebas y flujo de demostración: [evidencia S3](sprint-3-verificacion.md). API y métodos de escritura futuros se desarrollan en sus historias, sin darse por implementados aquí.

### HU-02 · Catálogo de experiencias

**P1 · Sprint 4 · Depende de HU-01.** Como visitante, quiero elegir una experiencia y conocer sus tarifas.

- Listado/detalle con duración, capacidad, ubicación y tarifas de grupo vigentes; scopes published y effectiveForGroup.
- CRUD completo de experiencias con ExperiencePolicy: escrituras administrativas e histórico conservado.
- ES/EN de la ficha e idioma disponible para jugar tratados por separado; datos demo sin clientes reales.
- Editar Rate no recalcula el precio ni los participantes de reservas confirmadas.

### HU-03 · Salas y sesiones

**P1 · Sprint 4 · Depende de HU-02.** Como administrador, quiero ofrecer sesiones que podamos atender.

- CRUD completos de salas y sesiones con experiencia, horario, game master y estado; RoomPolicy y autorización de agenda.
- Preparación académica de 30 minutos antes del inicio: ocupación de sala y responsable en [inicio - 30 minutos, fin).
- Rechazar intersecciones de sala o game master incluso con dos altas/cambios concurrentes; permitir límites contiguos.
- No ofrecer sesiones canceladas, en preparación ya iniciada o afectadas por bloqueos/incidencias críticas.

### HU-04 · Reservar una partida

**P1 · Sprint 4 · Depende de HU-03.** Como cliente, quiero reservar una sesión exclusiva para mi grupo.

- Validar aforo y calcular el precio en servidor. Una reserva pending o confirmed por sesión como máximo.
- Pending bloquea 15 minutos, hasta el inicio de preparación si llega antes; guardar expires_at. Tras vencer no se confirma.
- Reservar/caducar/confirmar dentro de transacción con bloqueo de escritura y restricción única; dos peticiones concurrentes dejan una sola activa.
- Aplicar los estados pending, confirmed, completed, cancelled y expired y sus transiciones según la revisión.
- Al confirmar conservar players, total_cents, currency, tarifa y fecha; posteriores cambios de Rate no recalculan el histórico.
- Scopes upcoming y activeAt usados en consultas funcionales. La limpieza al reservar no depende solo del comando periódico.

### HU-05 · Consultar y cancelar reservas

**P1 · Sprint 4 · Depende de HU-04.** Como cliente, quiero consultar mi reserva y cancelarla según las condiciones.

- Historial y detalle del titular o personal autorizado mediante ReservationPolicy.
- Validar estado y antelación acordada antes de cancelar; conservar la reserva y registrar la transición.
- Liberar la sesión solo si no persisten bloqueos y sigue siendo ofertable; cancelación y confirmación concurrentes no producen estados contradictorios.
- Cambios de grupo/importe por operación autorizada y con antes/después; no aplicar cambios de catálogo a reservas confirmadas automáticamente.
- El plazo concreto de cancelación sigue pendiente de acuerdo antes de implementar esa validación.

### HU-06 · Gestión interactiva

**P1 · Sprint 5 · Depende de HU-02 a HU-05 y HU-09 para su CRUD.** Como personal, quiero gestionar la actividad sin recargas completas.

- Siete clases Livewire: ExperiencesManager, RoomsManager, SessionsManager, ReservationsManager, IncidentsManager, AgendaBrowser y PreparationPanel.
- Cinco CRUD completos en los cinco gestores; agenda filtrable y panel de preparación usados en pantallas reales.
- Aplicar las cinco policies; siete scopes del inventario utilizados. Disponibilidad e incidencias incluyen relaciones y condiciones complejas.
- FormRequest y componentes Blade de T-08 reutilizados; no contar componentes Blade como clases Livewire.

### HU-07 · Equipos

**P2 · Sprint 5 · Depende de HU-04.** Como cliente, quiero asociar reservas y resultados a mi equipo.

- N:N equipos-usuarios con función y fecha de incorporación en el pivote.
- Solo usuarios autorizados gestionan el equipo; participantes sin cuenta cuentan para el aforo.
- Cambiar la composición de un equipo no modifica los participantes históricos de una reserva.

### HU-08 · Preparación y resultados

**P1 · Sprint 5 · Depende de HU-03 y HU-04.** Como game master, quiero preparar y cerrar mis partidas.

- Llegada y lista de preparación y comprobaciones obligatorias. GameSessionPolicy ya limita consultas en S3; en esta historia se amplía a las acciones de la partida asignada o administración.
- Pistas N:N con momento y penalización; resultado final único con tiempo y éxito.
- PreparationPanel permite registrar preparación y resultado; no iniciar una partida con incidencia crítica.
- La finalización registrada permite emitir GameSessionCompleted en S7, sin crear resultados automáticamente desde un comando.

### HU-09 · Incidencias y mantenimiento

**P1 · Sprint 5 · Depende de HU-03.** Como técnico, quiero resolver averías y registrar el trabajo realizado.

- CRUD completo de incidencias y gestión de actuaciones con técnico, gravedad, estado y minutos reales de intervención.
- Incorporar duration_minutes nullable mediante nueva migración, sin inventar tiempos de registros anteriores; base para el informe de mantenimiento.
- IncidentPolicy limita acceso y cambios; scopes requiringAttention y performedBetween usados por consultas e informe.
- Incidencia crítica bloquea reservas e inicio; confirmadas afectadas marcadas para revisión; resolver una no elimina otros bloqueos.

### HU-10 · API para clientes externos

**P1 · Sprint 6 · Depende de HU-02 a HU-05 y HU-09.** Como cliente externo, quiero consultar oferta y gestionar reservas con autorización.

- Siete recursos: User, Experience, Room, GameSession, Reservation, Rate e Incident.
- Cinco CRUD completos: Experience, Room, GameSession, Reservation e Incident; User y Rate ofrecen consultas según la matriz.
- User, Reservation e Incident siempre autenticados con Sanctum. Experiencias y salas con escrituras administrativas; sesiones gestionadas por personal autorizado.
- Mismas policies/reglas que en web; sin reservas ajenas ni datos personales en disponibilidad pública; errores 401/403 comprobables.
- Contrato con operaciones, filtros, paginación, ejemplos, errores, autenticación y bajas/cancelaciones; Team y MaintenanceAction quedan como modelos internos.

### HU-11 · Avisos y trabajo en segundo plano

**P1 · Sprint 7 · Depende de HU-04, HU-05, HU-08 y HU-09.** Como cliente o personal, quiero recibir los avisos del flujo de reservas y partidas.

- Cinco pares evento-listener: ReservationConfirmed/QueueReservationConfirmation; ReservationCancelled/QueueReservationCancellation; IncidentReported/HandleIncidentImpact; GameSessionCompleted/QueueSessionResult; DocumentRequested/QueueDocumentGeneration.
- Cinco jobs: SendCustomerEmail, SendIncidentAlert y GenerateDocument en cola; ExpirePendingReservations y ReviewAffectedReservations síncronos.
- Cinco correos: confirmación, recordatorio, cancelación, incidencia interna y resultado. El último no depende del diploma opcional.
- Eventos después del commit, registro de envíos y claves para evitar duplicados; comprobar errores y reintentos. Sin recordatorios de canceladas/vencidas.
- Infraestructura de documentos preparada en S7; tres plantillas complejas y justificante se completan en S8.

### HU-12 · Documentos e informes

**P1 · Sprint 8 · Depende de HU-04, HU-09 y HU-11.** Como personal, quiero revisar actividad y mantenimiento con documentos verificables.

- Tres PDF complejos con laravel-dompdf: mensual de actividad, diario de operaciones y mantenimiento, más justificante simple obligatorio.
- Mensual: mes/local/experiencia/sala/estado; sesiones ofertadas y ocupadas, porcentaje, participantes, reservado, cobrado, devoluciones y neto por día y experiencia.
- Diario: día/local/sala/experiencia/game master/estado; detalle de sesiones y participantes; sesiones y minutos de juego/preparación por sala y responsable.
- Mantenimiento: periodo/sala/gravedad/estado/técnico; incidencias y actuaciones; recuentos únicos, minutos de intervención y media de resolución.
- Aplicar filtros y agregados de la revisión sin multiplicar totales por joins; comprobar casos vacíos y varias filas relacionadas, paginación y subtotales.
- Informes para personal autorizado; justificante solo de la reserva propia o por personal autorizado. Ampliar previamente el CHECK de documentos con daily_report y maintenance_report, conservando datos.

### HU-13 · Dos idiomas

**P1 · Sprint 8 · Depende de HU-06. Obligatoria.** Como usuario, quiero usar la interfaz en español o inglés.

- Selector ES/EN; navegación, formularios, validación, pantallas y correos traducidos; elección conservada durante la sesión.
- Cambiar idioma no modifica datos ni permisos; mantener fallback para contenido sin traducción.
- Separar idioma de interfaz de idioma de juego; no dar por ofrecida una partida en inglés por traducir su ficha.

### HU-14 · Diploma y valoración

**P3 · Sprint 9 · Depende de HU-08/HU-12 y mínimos cubiertos. Ampliación opcional.** Como cliente, quiero conservar un recuerdo y valorar la partida.

- Diploma simple con criterio de emisión acordado y datos del resultado; puede adjuntarse al correo obligatorio de resultado, que funciona sin él.
- Una valoración por reserva completada, solo del titular y con moderación administrativa.
- La existencia de tablas no completa estas funciones.

### HU-15 · Bono regalo

**P3 · Sprint 10 · Depende de HU-04 y mínimos cubiertos. Ampliación opcional.** Como cliente, quiero canjear una experiencia regalada.

- Código con vigencia y uso único; dos solicitudes simultáneas no lo consumen dos veces.
- Validar importe o grupo cubierto según condiciones acordadas; pago online y logística física excluidos.

### HU-16 · Exportación de actividad

**P3 · Sprint 10 · Depende de HU-12 y mínimos cubiertos. Ampliación opcional.** Como administrador, quiero analizar la actividad fuera de la aplicación.

- Excel con los mismos filtros y totales que el informe; acceso administrativo y sin datos personales innecesarios.

## Tareas técnicas posteriores

| ID | Prioridad / sprint | Trabajo | Criterio de aceptación |
|---|---|---|---|
| T-07 | P1 / 2 | Modelo de datos | E-R justificado, claves/índices, al menos siete modelos preparados, relaciones y pivote desde código, factories, seeders, pruebas iniciales y reconstrucción. Evidencia del 18/10 conservada; impactos de la corrección de S1 anotados, no presentados como corrección docente de S2. |
| T-08 | P1 / 3-4 | Validación y componentes | Base implementada en S3 para registro, acceso, perfil y agenda, con cinco componentes y errores junto al campo. Pendiente en S4: aplicar FormRequest y componentes a los nuevos formularios CRUD; la tarea completa sigue abierta. |
| T-09 | P1 / 7 | Siete comandos Artisan | reservations:expire, reservations:remind, reports:monthly, reports:daily, reports:maintenance, sessions:send-results e incidents:review-reservations. Informe mensual invocado desde código autorizado; funcionamiento, errores y reejecución comprobados. |
| T-10 | P1 / 9 | Pest y cobertura obligatoria | Informe de al menos 85 % del código de aplicación. Comprobar policies, aforo, caducidad, concurrencia, preparación, precio/grupo históricos, cinco correos/colas, tres informes y ES/EN. No presentar un recuento de pruebas como porcentaje medido. |
| T-11 | P1 / 10 | Cierre técnico | Contrato API final y revisión del inventario corregido completo; README centrado en el proyecto y enlaces; versión candidata, historial conservado y mínimos antes que extras. |
| T-12 | P1 / Final | Demo y entrega | Integración, recorridos de permisos/reserva/informes/idiomas, guion de defensa y enlaces; entrega oficial 12/02/2027. Comprobar nombre Mythos en el registro docente. |

## Definición de terminado

Una tarjeta funcional pasa a Hecho cuando cumple sus criterios con código y evidencia, sin defectos que impidan usarla. Documentar una función no la implementa. En las tareas documentales se revisan contenido, enlaces y maquetación. Las ampliaciones esperan a los mínimos y las correcciones no crean tarjetas duplicadas.

El enunciado de S3 del 26/10 prevalece sobre la previsión de una sola policy y validación/componentes en S4. Se conserva la evidencia de S2 y no se completan otras historias por disponer de sus páginas de consulta.
