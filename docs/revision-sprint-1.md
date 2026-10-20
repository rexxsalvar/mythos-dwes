# Revisión de la propuesta del sprint 1

Fecha: 20/10/2026. Fuente: seis correcciones del profesor comunicadas por el alumno. Sustituyen las cantidades y decisiones de la propuesta inicial que contradigan esta revisión. No se ha recibido todavía una corrección docente del sprint 2.

Este documento fija el alcance y sus criterios de aceptación. La implementación se reparte entre los diez sprints del [calendario](calendario.md); una función planificada no se considera terminada por aparecer aquí.

## 1. Correspondencia con las correcciones

| Corrección | Compromiso revisado | Evidencia de aceptación futura |
|---|---|---|
| Traducciones y pruebas | Interfaz ES/EN y Pest con cobertura mínima del 85 % son obligatorios. | Recorrido de pantallas y validaciones en ambos idiomas; informe de cobertura del código de aplicación. |
| PDF complejos | Tres informes: mensual, diario y mantenimiento, con laravel-dompdf. | Filtros, detalle, agrupaciones y totales contrastados con los registros de origen. |
| API y autorización | Siete recursos concretos, cinco CRUD, al menos tres recursos autenticados, cinco policies, siete scopes y siete clases Livewire. | Contrato API, respuestas 401/403, inventario de clases y operaciones funcionales. |
| Sesión exclusiva y pendientes | Estados, vencimiento, bloqueo temporal, preparación y concurrencia definidos antes del CRUD. | Dos solicitudes simultáneas dejan una sola reserva activa; expiración y agenda respetan los intervalos. |
| Automatizaciones e histórico | Siete comandos, cinco pares evento-listener, cinco jobs, tres en cola y cinco correos. Importe y participantes conservados al confirmar. | Inventario ejecutable; invocación desde código; reintentos; edición de Rate sin alterar reservas confirmadas. |
| Alcance académico y registro | Nombre Mythos, exclusiones explícitas, diez sprints y enlaces únicos a Git y tablero. | Propuesta, backlog, calendario y tarjetas coherentes; nombre de la ficha docente revisado por el alumno. |

## 2. Alcance académico y exclusiones

Nombre definitivo: **Mythos - Sistema de reservas y gestión de escape rooms**. EscapeHub identifica el material de partida, no otro producto ni otro repositorio. La ficha o registro del centro deberá usar Mythos y estos mismos enlaces: [Git](https://github.com/rexxsalvar/mythos-dwes), [tablero](https://github.com/users/rexxsalvar/projects/1) y [backlog](https://github.com/users/rexxsalvar/projects/1/views/2). La actualización de un registro externo del centro queda pendiente de comprobar; no se afirma haberla realizado.

Se desarrollan catálogo, salas, sesiones privadas por grupo, reservas, equipos, preparación, resultados, pistas, incidencias, mantenimiento, avisos y documentos. Los cinco CRUD son experiencias, salas, sesiones, reservas e incidencias. Se mantienen los perfiles cliente, recepción, game master, mantenimiento y administración; el visitante consulta el catálogo sin cuenta.

La interfaz bilingüe incluye navegación, formularios, validaciones, pantallas y correos del proyecto en español e inglés. El idioma en que se dirige una partida se guarda por separado: una ficha traducida no demuestra que exista personal disponible para dirigirla en inglés. La oferta real de partidas en cada idioma no se da por validada.

Pest y el **85 % mínimo de cobertura del código de aplicación** forman parte del alcance obligatorio. La cifra se medirá con un informe; el negocio real, el número de tablas o las 43 pruebas iniciales no demuestran ese porcentaje. Las dependencias externas quedan fuera del código propio medido, sin excluir reglas de negocio para alcanzar el umbral.

Son opcionales valoraciones, bonos regalo, diploma y exportación Excel. El quinto correo obligatorio comunica el resultado de la partida; podrá adjuntar el diploma si se implementa esa ampliación. Pagos online, QR, pasaporte de escapista, logística física de bonos y publicación en producción quedan excluidos de la entrega inicial. Los cobros previstos son registros manuales, no una pasarela ni un sistema contable o de facturación fiscal.

Los datos de Expedición Maldita y sus tarifas sirven como referencia histórica del análisis inicial, no como prueba de cumplimiento ni como condiciones comerciales actuales. Los tiempos académicos se distinguen de las condiciones reales del negocio.

## 3. API: siete recursos y cinco CRUD

| Recurso | Operaciones previstas | Acceso |
|---|---|---|
| `User` | Consulta del perfil propio; listado y detalle por administración. | Siempre autenticado. No hay un CRUD público de usuarios. |
| `Experience` | CRUD completo; consulta pública de experiencias publicadas. | Escritura autenticada y administrativa. |
| `Room` | CRUD completo; consulta pública limitada a salas ofertadas. | Escritura autenticada y administrativa. |
| `GameSession` | CRUD completo por personal autorizado; disponibilidad pública sin datos personales. | Escritura autenticada; la consulta pública solo devuelve información de oferta. |
| `Reservation` | CRUD completo con bajas como cancelación y conservación del histórico. | Siempre autenticado; titular o personal autorizado. |
| `Rate` | Listado y detalle de tarifas vigentes por experiencia y grupo. | Lectura pública de tarifas publicadas. Sin escritura pública. |
| `Incident` | CRUD completo con baja compatible con el histórico de mantenimiento. | Siempre autenticado; personal autorizado. |

`User`, `Reservation` e `Incident` cumplen el mínimo de tres recursos reservados a autenticación. Los endpoints privados usarán Sanctum. Los cinco CRUD cubren crear, consultar listado/detalle, actualizar y dar de baja; una baja de histórico se representa mediante estado o archivo cuando corresponda.

**Aceptación HU-10:** los siete recursos están documentados; cada CRUD tiene sus cinco operaciones; una petición anónima a un recurso privado devuelve 401; un usuario autenticado sin permiso recibe 403; no se consultan reservas ajenas; las escrituras de experiencias y salas requieren administración. `Team` y `MaintenanceAction` conservan sus modelos y funciones internas, aunque dejan de formar parte del listado de siete recursos API comprometidos.

## 4. Cinco policies, siete scopes y siete clases Livewire

| Policy | Decisión real de autorización |
|---|---|
| `ReservationPolicy` | Propiedad, rol, estado y acción permitida sobre la reserva. |
| `GameSessionPolicy` | Administración de agenda o partida asignada al game master, según acción y estado. |
| `ExperiencePolicy` | Consulta pública del catálogo publicado y escrituras administrativas. |
| `RoomPolicy` | Consulta de oferta y administración de salas; cambios operativos solo por personal autorizado. |
| `IncidentPolicy` | Acceso del personal y modificación por técnico asignado o administración. |

La consulta de `User` se limita al propio usuario o a administración mediante autorización de ruta y consulta. Los endpoints de `Rate` solo muestran tarifas publicadas. Cada policy se aplica en web, Livewire y API cuando interviene su recurso; una comprobación de rol aislada en la vista no sustituye la autorización de servidor.

| Scope | Uso y criterio |
|---|---|
| `Experience::published()` | Catálogo activo y publicado. |
| `Reservation::upcoming()` | Reservas futuras del usuario autorizado. |
| `Reservation::activeAt($instant)` | Confirmadas o pendientes no vencidas; agrupa correctamente las condiciones OR. |
| `GameSession::availableBetween(...)` | Fecha, experiencia, aforo, reservas activas, sala, responsable, bloqueos y preparación. Complejo. |
| `Incident::requiringAttention(...)` | Relación con sala, gravedad y estado. Complejo. |
| `Rate::effectiveForGroup(...)` | Experiencia, jugadores, moneda y vigencia del plan de precios. Complejo. |
| `MaintenanceAction::performedBetween(...)` | Periodo y técnico para el informe de mantenimiento. |

Las siete clases Livewire serán `ExperiencesManager`, `RoomsManager`, `SessionsManager`, `ReservationsManager`, `IncidentsManager`, `AgendaBrowser` y `PreparationPanel`. Las cinco primeras implementan los cinco CRUD; las otras dos permiten filtrar la agenda y registrar preparación, pistas y resultado. No se cuentan componentes Blade como clases Livewire.

**Aceptación HU-06/T-08:** siete clases utilizadas en pantallas reales, cinco CRUD completos, cinco policies aplicadas y siete scopes utilizados en consultas funcionales; al menos disponibilidad e incidencias combinan relaciones y condiciones. Se conservan FormRequest y los componentes input, fechas, select, labels y checkbox.

## 5. Reserva pendiente, exclusividad e histórico

### Tiempos académicos aprobados

El alumno aprueba el 20/10/2026 **15 minutos de bloqueo para una reserva pendiente** y **30 minutos de preparación antes de la sesión**. Son reglas académicas confirmadas; no se han activado esos valores en la base ni se presentan como condiciones reales de Mythos. El plazo de cancelación y las condiciones de cobro siguen pendientes de acuerdo.

El bloqueo vencerá en `min(created_at + plazo_pendiente, starts_at - preparation_minutes)`. No se ofrecerá una nueva reserva cuando ya haya comenzado el intervalo de preparación. Se guardarán horas en UTC y se mostrarán en la zona horaria del local.

| Estado | Efecto y transiciones |
|---|---|
| `pending` | Bloquea la sesión hasta `expires_at`. Puede pasar a `confirmed`, `cancelled` o `expired`. |
| `confirmed` | Ocupa la sesión de forma exclusiva. Puede pasar a `completed` tras jugar o a `cancelled` por acción autorizada. |
| `completed` | Histórico de la partida finalizada; no vuelve a pendiente. |
| `cancelled` | Histórico de cancelación; no conserva una reserva activa. La sesión solo se vuelve a ofertar si sigue disponible y jugable. |
| `expired` | Pendiente cuyo plazo venció; deja libre la reserva, sin eliminarla. |

Una sesión es privada para un solo grupo. `pending` y `confirmed` se consideran activas; nunca puede haber dos para la misma sesión. El comando de caducidad se ejecutará cada minuto, pero una petición de reserva también caducará el bloqueo vencido dentro de su transacción: no dependerá de esperar al siguiente minuto del planificador.

Con SQLite se tomará el bloqueo de escritura al comenzar la operación (`BEGIN IMMEDIATE` o equivalente del servicio de transacciones). En la misma transacción se comprobarán vencimiento, jugabilidad, aforo y disponibilidad, se liberará una pendiente vencida y se insertará la nueva reserva. El índice único parcial de reserva activa será la última garantía. Un conflicto o un reintento agotado se devolverá como conflicto de disponibilidad, no como confirmación duplicada. Las operaciones de confirmar, cancelar y caducar competirán por la misma protección.

La ocupación de sala y game master se comprobará sobre el intervalo semiabierto **`[starts_at - preparation_minutes, ends_at)`**. Se rechaza cualquier intersección para cualquiera de los dos recursos; la igualdad entre fin y siguiente comienzo ocupado es válida. Dos altas o cambios concurrentes de sesiones no pueden saltarse esa comprobación; los triggers actuales aportan la protección de integridad. La disponibilidad también consulta bloqueos e incidencias críticas.

Al confirmar se conservarán `players`, `total_cents`, `currency`, referencia a la tarifa y momento de confirmación. Estos valores describen el grupo y el precio confirmado; editar `Rate` no los recalcula. Una modificación posterior del grupo o importe requiere una operación explícita y autorizada con registro del antes y después, nunca una actualización automática por cambiar el catálogo. Los informes históricos utilizan los valores conservados de la reserva.

**Aceptación HU-03/HU-04/HU-05:** pendiente válida bloquea; al vencer puede reservar otro grupo; confirmación fuera de plazo se rechaza; cancelación conserva el histórico; dos conexiones concurrentes dejan una sola reserva activa; sala y game master no se solapan incluyendo los 30 minutos de preparación; editar una tarifa conserva precio y participantes de reservas confirmadas.

## 6. Tres informes PDF complejos

Todos usan laravel-dompdf, acceso del personal autorizado, filtros visibles en cabecera, fecha de generación, detalle, subtotales y total general. Los agregados se calculan sin multiplicar reservas o importes al unir participantes, cobros y actuaciones. Se comprobarán filtros vacíos, sin resultados y con varias filas relacionadas.

| Informe | Filtros y detalle | Agregados comprobables |
|---|---|---|
| Mensual de actividad | Mes, local, experiencia, sala y estado de reserva; detalle por día, experiencia y reserva. | Sesiones ofertadas y ocupadas; ocupación = sesiones con reserva confirmada/completada entre ofertadas; participantes e importe reservado; cobros liquidados, devoluciones y neto por separado; subtotales por experiencia y día. |
| Diario de operaciones | Día y zona horaria del local, sala, experiencia, game master y estado; sesiones, horarios, preparación, reserva y número de participantes. | Sesiones y participantes por sala y game master; minutos programados de juego y preparación por responsable; carga total de personal. No se confunden minutos programados con horas reales trabajadas. |
| Mantenimiento | Periodo, local/sala, gravedad, estado y técnico; incidencias creadas, resueltas o con actuaciones en el intervalo; actuaciones realizadas dentro del periodo. | Incidencias únicas por gravedad/estado/sala; actuaciones y minutos de intervención por técnico y sala; media de tiempo entre apertura y resolución para incidencias resueltas. Tiempos desconocidos se muestran como no registrados. |

El informe de mantenimiento utilizará **tiempos de intervención**, una de las alternativas admitidas por el profesor; no se inventan costes ni se deducen minutos de trabajo a partir de la fecha puntual de una actuación. El justificante será un PDF simple obligatorio. El diploma podrá ser simple si se realiza la ampliación.

**Aceptación HU-12:** tres documentos complejos distintos con los filtros y agregados anteriores, más el justificante; valores contrastados con un conjunto conocido de datos, paginación y totales correctos y descarga restringida. La propuesta PDF del sprint 1 no cuenta como uno de estos informes funcionales.

## 7. Automatizaciones: inventario cerrado

### Siete comandos

1. `reservations:expire`: caducar pendientes vencidas.
2. `reservations:remind`: preparar recordatorios de partidas confirmadas.
3. `reports:monthly`: solicitar el informe mensual.
4. `reports:daily`: solicitar el informe diario.
5. `reports:maintenance`: solicitar el informe de mantenimiento.
6. `sessions:send-results`: preparar el correo de resultados de partidas finalizadas.
7. `incidents:review-reservations`: revisar reservas afectadas por incidencias y bloqueos.

El comando de informe mensual se invocará también desde una acción de administración autorizada. Los tres informes pueden compartir servicios, pero tienen comandos y filtros propios. `sessions:send-results` no finaliza partidas automáticamente ni crea resultados ficticios.

### Cinco pares evento-listener

| Evento | Listener | Resultado |
|---|---|---|
| `ReservationConfirmed` | `QueueReservationConfirmation` | Programa la confirmación al cliente. |
| `ReservationCancelled` | `QueueReservationCancellation` | Programa el aviso de cancelación. |
| `IncidentReported` | `HandleIncidentImpact` | Revisa las reservas afectadas y programa el aviso interno. |
| `GameSessionCompleted` | `QueueSessionResult` | Programa el resultado y, si existe, el diploma. |
| `DocumentRequested` | `QueueDocumentGeneration` | Encola el justificante o el informe solicitado. |

Los eventos y envíos se despachan después del commit para no comunicar operaciones revertidas.

### Cinco jobs, tres en cola

| Job | Ejecución |
|---|---|
| `SendCustomerEmail` | En cola: confirmación, recordatorio, cancelación y resultado. |
| `SendIncidentAlert` | En cola: aviso interno de incidencia. |
| `GenerateDocument` | En cola: justificante y los tres informes; diploma si se implementa. |
| `ExpirePendingReservations` | Síncrona desde el comando de caducidad. |
| `ReviewAffectedReservations` | Síncrona desde comando o listener de incidencia. |

Los cinco correos distintos son **confirmación, recordatorio, cancelación, aviso interno de incidencia y resultado de partida**. Una misma clase de job puede encargarse de varios correos; no se cuentan como jobs distintos los tipos de email ni los intentos de ejecución.

**Aceptación HU-11/T-09:** siete comandos operativos, cinco eventos con su listener asociado, cinco clases de job de las que tres usan cola y cinco correos diferenciados. Se comprueban invocación desde código, errores y reintentos; las claves de `notification_deliveries` evitan programar duplicados por la misma operación. Una reserva cancelada o vencida no recibe recordatorio. El correo de resultado obligatorio funciona aunque el diploma opcional no exista.

## 8. Impacto sobre el sprint 2 ya preparado

Esto es una revisión del alcance de S1, no una corrección ni una calificación docente de S2. Se conserva la evidencia del modelo publicado el 18/10 y no se cambian migraciones, seeders o datos con esta revisión documental.

- Los siete recursos API corregidos ya tienen modelo: `User`, `Experience`, `Room`, `GameSession`, `Reservation`, `Rate` e `Incident`.
- La base ya guarda vencimiento, estado, precio, jugadores, preparación, bloqueos y registros de avisos/documentos. Tener columnas no implementa las transacciones, permisos, comandos ni el cierre del precio confirmado.
- **Ajuste de esquema pendiente antes de HU-09/HU-12:** `MaintenanceAction` no tiene minutos de intervención. Habrá que incorporarlos mediante una nueva migración y adaptar formulario y datos demo; no se inventará la duración de actuaciones antiguas.
- **Ajuste de esquema pendiente antes de HU-12:** el CHECK de `generated_documents.kind` todavía no admite `daily_report` ni `maintenance_report`. Deberá ampliarse conservando los registros existentes.
- Los tiempos aprobados no se activan automáticamente en políticas existentes. Se aplicarán al implementar el flujo correspondiente, conservando el histórico.

## 9. Seguimiento

Las mismas 28 tarjetas se refinan en lugar de duplicarse. Los criterios de implementación permanecen pendientes hasta disponer de código y evidencia. El [backlog](backlog.md) y el [calendario](calendario.md) distribuyen la corrección sin crear un sprint 11 ni cambiar la entrega del 12/02/2027.
