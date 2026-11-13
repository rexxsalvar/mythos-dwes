# Sprint 4 - Autorización, consultas y servicios de aplicación

Implementación local del 13/11/2026. Periodo docente del 02/11 al 15/11/2026. El [enunciado completo](sprint-4-enunciado.md) exige cinco policies, siete scopes con cuatro consultas complejas y cuatro comandos, dos invocados desde código. Esta evidencia describe lo implementado; no acredita una entrega ni una corrección docente.

## Correspondencia con el enunciado

| Criterio | Implementación y evidencia reproducible |
|---|---|
| Tres policies restantes y cinco probadas | ExperiencePolicy, RoomPolicy e IncidentPolicy completan ReservationPolicy y GameSessionPolicy. SprintFourAuthorizationTest comprueba casos permitidos y denegados; AccessPermissionsTest prueba las rutas por perfil y los registros ajenos. |
| Siete scopes y cuatro consultas complejas | Siete scopes conservados. Disponibilidad, tarifas, reservas afectadas e incidencias que afectan a futuras confirmadas combinan relaciones y condiciones del dominio. SprintFourScopesTest incluye casos válidos, exclusiones y límites. |
| Cuatro comandos, dos invocados desde código | reservations:expire, incidents:review-reservations, reports:monthly y reports:daily. Los tres primeros se invocan desde OperationalCommandController mediante acciones POST administrativas. |
| Lógica reutilizable separada | Servicios de catálogo, agenda, reservas, revisión de afectadas y resumen de actividad. Los comandos y controladores delegan esas operaciones. |
| Pruebas y errores | OperationalCommandsTest y ActivityReportCommandsTest cubren comandos, repetición, formatos/salas inválidos, fallos de almacenamiento y acceso autorizado/denegado desde web. Se conservan las regresiones de S2 y S3. |
| FormRequest y componentes | Requests de experiencias, salas, agenda, reservas y MonthlyActivityRequest; formularios con componentes Blade, CSRF y errores visibles. |

## Funciones implementadas

- Catálogo público en `/experiencias`, con duración, aforo, tarifa vigente por grupo y sesiones ofertables durante los próximos tres meses. La disponibilidad pública no muestra datos de clientes ni de personal.
- Administración web de experiencias y salas, con alta, consulta, edición y archivo. El archivo conserva relaciones e histórico y se rechaza cuando perjudica reservas futuras activas.
- Agenda con alta, edición y cancelación de sesiones. Se comprueban instalación, sala, experiencia, idioma de juego, game master activo, bloqueos y preparación.
- Reservas con creación por el titular o recepción/administración para un cliente, consulta protegida, cambio de grupo pendiente, confirmación por personal y cancelación autorizada. El cliente no establece el precio, el estado ni el titular mediante campos adicionales.
- Los nuevos formularios con más de dos campos utilizan `ExperienceRequest`, `RoomRequest`, `ScheduleSessionRequest` y `HoldReservationRequest`. Reutilizan los componentes Blade de S3 y CSRF en escrituras.

Las bajas de reservas y sesiones son cancelaciones, y las de experiencias y salas son archivos. No se borran físicamente registros con valor histórico. Una sesión con historial de reservas no se reprograma; se crea otra. El cambio de experiencia instalada en una sala existente requiere gestionar una nueva instalación histórica y queda fuera de este formulario.

## Cinco policies

| Clase | Aplicación efectiva |
|---|---|
| `ExperiencePolicy` | Catálogo publicado y escrituras administrativas de experiencias. |
| `RoomPolicy` | Oferta visible y administración de salas. |
| `GameSessionPolicy` | Agenda por perfil/asignación y administración de sesiones según su estado. |
| `ReservationPolicy` | Titular, reserva delegada, personal autorizado y acciones según estado. |
| `IncidentPolicy` | Consulta de incidencias propias del técnico o acceso administrativo. Autoriza las futuras escrituras; el CRUD de incidencias sigue en S5. |

Se conservan los cinco roles. `AccessRolesSeeder` aplica 18 permisos, es repetible y conserva usuarios y asignaciones de rol. Las policies se descubren por la convención de Laravel y se aplican también desde los servicios de escritura.

## Siete scopes utilizados

| Scope | Consulta que lo utiliza |
|---|---|
| `Experience::published()` | Catálogo y disponibilidad. |
| `Reservation::upcoming()` | Historial propio y modo `affectedOnly` del servicio de revisión, con periodo opcional y confirmadas afectadas por incidencias críticas o bloqueos. |
| `Reservation::activeAt()` | Exclusividad, confirmación, cancelación de sesiones y protección de reservas al editar el catálogo. |
| `GameSession::availableBetween()` | Disponibilidad pública y comprobación dentro de la transacción de reserva. |
| `Rate::effectiveForGroup()` | Tarifa del grupo en la ficha y al crear o modificar una pendiente. |
| `Incident::requiringAttention()` | Exclusión de salas con incidencias críticas y modo `reservationsAfter` para identificar las que afectan a sesiones futuras confirmadas. |
| `MaintenanceAction::performedBetween()` | Filtro de actuaciones en el detalle de una incidencia autorizada. |

Las fechas se guardan en UTC. Los formularios y filtros diarios interpretan la hora de Murcia mediante `Europe/Madrid`. Los intervalos son semiabiertos; un fin exactamente igual al siguiente inicio ocupado no solapa.

Las cuatro consultas complejas se demuestran mediante usos funcionales concretos, sin contar un filtro simple como complejo por su nombre.

1. `GameSession::availableBetween` cruza experiencia, aforo, instalación, sala, responsable, incidencias, reservas activas y bloques que intersectan la preparación o el juego.
2. `Rate::effectiveForGroup` cruza experiencia, grupo y moneda con el estado y la vigencia semiabierta del plan de precios.
3. `Reservation::upcoming(..., affectedOnly: true)` selecciona confirmadas futuras y agrupa la alternativa entre incidencia crítica de su sala y bloqueo de sala o game master que solape su ocupación. Los bloques liberados y los intervalos contiguos quedan excluidos.
4. `Incident::requiringAttention(..., reservationsAfter: ...)` combina estado y gravedad con la relación sala → sesiones futuras → reservas confirmadas. Una incidencia de una sala sin reservas futuras, con pendientes solamente o con partidas pasadas no entra en este modo.

## Cuatro comandos

| Comando | Resultado real y servicio reutilizado |
|---|---|
| `php artisan reservations:expire` | Caduca pendientes con fecha vencida y registra la transición. Reejecutar no duplica eventos; conserva pendientes históricas sin plazo conocido. Usa ReservationService. |
| `php artisan incidents:review-reservations` | Marca `needs_review` en confirmadas futuras afectadas por incidencia crítica o bloqueo y registra los impactos de incidencias sin duplicarlos. Usa ReviewAffectedReservationsService. |
| `php artisan reports:monthly 2026-11 --room=1` | Extrae JSON de actividad mensual, con filtro opcional de sala. Usa ActivityReportService. |
| `php artisan reports:daily 2026-11-13 --room=1` | Extrae JSON de actividad diaria, con filtro opcional de sala. Usa el mismo servicio. |

En los dos últimos comandos se pueden omitir fecha y sala. Se utiliza el mes o día actual de Europe/Madrid. La salida incluye sesiones por estado, reservas confirmadas/completadas y participantes/importe histórico por moneda, además de minutos programados por game master. La preparación histórica desconocida se contabiliza aparte; no se inventa su duración ni se confunden importes reservados con cobros.

Los comandos de actividad generan **datos JSON**, no los PDF complejos de S8. Los tres comandos restantes del inventario final, correos y colas mantienen su planificación. Los comandos devuelven código 0 en éxito, 2 por parámetros de informe inválidos y 1 en fallos de almacenamiento tratados, sin anunciar éxito tras un fallo.

`OperationalCommandController` invoca `Artisan::call` desde tres acciones POST protegidas por autenticación, permiso administrativo y CSRF. Administración permite caducar pendientes, detectar afectadas y descargar los datos mensuales con `MonthlyActivityRequest`. El usuario no puede elegir nombres de comando ni argumentos arbitrarios. Los fallos vuelven como errores visibles, no como confirmaciones de operación.

La caducidad queda registrada cada minuto en el planificador de Laravel, con exclusión de ejecuciones simultáneas. El proceso del planificador debe estar en marcha para ejecutar esa periodicidad; el flujo de reservar conserva además su comprobación transaccional independiente.

## Servicios y reglas del dominio

`CatalogService` concentra las escrituras de experiencias/salas y la conservación del histórico. `ScheduleSession` concentra la agenda. `ReservationService` concentra bloqueo, precio, grupo, confirmación, caducidad y cancelación, con transacciones y registro de cambios antes/después en `reservation_events`.

`ReviewAffectedReservationsService` detecta problemas y marca reservas para que el personal las revise. No cancela reservas, libera bloqueos ni borra revisiones anteriores automáticamente. `ActivityReportService` es de consulta y comparte la extracción de datos entre los dos comandos de actividad.

1. Una pendiente bloquea como máximo 15 minutos y vence antes si comienza la preparación. Una nueva petición caduca las pendientes vencidas dentro de su propia transacción. La confirmación comprueba el plazo y conserva el estado `expired` si ha vencido.
2. Cada sesión nueva o reprogramada ocupa sala y game master desde 30 minutos antes del inicio hasta su fin. Los triggers existentes rechazan solapamientos de cualquiera de los dos recursos.
3. Solo puede existir una reserva `pending` o `confirmed` por sesión. SQLite usa `BEGIN IMMEDIATE`, con espera limitada y traducción del conflicto a un error de validación. El índice único parcial es la última garantía de integridad.
4. El precio se obtiene en servidor para el grupo y se guarda en céntimos, junto con moneda y tarifa. Confirmar conserva ese precio y los participantes aunque después se edite `Rate`. Los cambios de grupo solo se permiten mientras la reserva esté pendiente, por personal autorizado y con registro de antes/después.
5. El alumno aprobó el 13/11 una antelación mínima de **24 horas** para cancelar una confirmada. El límite exacto de 24 horas se admite; después se rechaza. Las pendientes pueden cancelarse mientras su bloqueo siga vigente.
6. Las nuevas reservas conservan una versión de condiciones. Se utiliza una versión activa y vigente de la experiencia; si no existe una configurada, se crea la versión académica de 24 horas, 15 minutos y 30 minutos dentro de la misma transacción. Los borradores y las condiciones de reservas anteriores se conservan.
7. Una confirmada antigua sin condiciones de cancelación conocidas no recibe retroactivamente las condiciones nuevas. Una pendiente antigua sin vencimiento sigue bloqueando de forma conservadora y no se confirma mediante el nuevo flujo. La preparación histórica nula conserva el significado de los triggers anteriores, sin reescribir esos datos.

Los estados `completed` y los resultados se gestionarán en el flujo de partidas de S5. Los eventos auditados en la base de datos no sustituyen los eventos/listeners y trabajos en cola previstos en S7. La confirmación de una reserva tampoco acredita un cobro.

## Recorrido de demostración

1. Entrar como administración y abrir gestión de experiencias o salas desde el panel. Crear o editar datos; enviar un aforo inválido y comprobar la validación.
2. Crear una sesión futura para una instalación y un game master. Intentar otra que se cruce con la preparación de la misma sala o del mismo responsable; se rechaza. Un intervalo contiguo se admite.
3. Abrir `/experiencias`, elegir grupo y reservar una sesión con tarifa vigente desde una cuenta cliente. Consultar el vencimiento y el plazo de cancelación en el detalle.
4. Entrar como recepción y modificar el grupo pendiente o confirmarlo. Un cliente no puede confirmar ni gestionar reservas ajenas.
5. Cancelar una reserva dentro de su plazo y comprobar que permanece en el historial. Intentar cancelar una confirmada con menos de 24 horas; se rechaza.
6. Probar el filtro de próximas partidas y los filtros por fecha de actuaciones. Un técnico sigue sin poder abrir la incidencia de otro técnico mediante su URL.
7. En administración, ejecutar la caducidad y la detección de reservas afectadas. Repetir y comprobar que los registros de transición e impactos no se duplican. Un cliente o visitante no puede ejecutar esas acciones.
8. Descargar los datos de actividad de un mes y una sala. Contrastar el JSON con los registros de origen; enviar un mes o sala inválidos y comprobar el error. Ejecutar también `php artisan reports:daily 2026-11-13` desde terminal.

Los datos demo de octubre pueden no ofrecer sesiones futuras en noviembre. Se debe crear una sesión de demostración desde administración; no se regeneran ni borran los datos existentes para mostrar disponibilidad.

## Verificación

- Pest completo, **137 pruebas correctas y 646 assertions**. Incluye las cinco policies, los siete scopes y sus cuatro consultas complejas, los cuatro comandos, invocaciones administrativas, formatos/salas inválidos, fallos de almacenamiento, repetición y las regresiones de S2/S3. Se comprueban también límites de días/meses en Madrid y el día de cambio horario de 23 horas.
- Formato PHP comprobado con Pint y recursos compilados mediante `npm run build`.
- Servidor local en `http://127.0.0.1:8030`. Inicio y catálogo responden HTTP 200; administración anónima redirige a acceso con HTTP 302. Los flujos autenticados y las vistas nuevas se comprueban mediante pruebas HTTP de Laravel, sin presentar esto como una revisión visual en navegador.
- Aplicación local de `AccessRolesSeeder` con copia previa en `storage/app/private/backups/before-sprint-4-permissions-20261113-190345.sqlite`. Cinco roles, 18 permisos y ninguna infracción de claves ajenas. Comparación de hashes de las otras **42 tablas**, incluidas usuarios y asignaciones de rol, sin diferencias. Solo se excluyen de esa comparación permisos, sus asignaciones a roles y la caché.

Las pruebas utilizan bases SQLite en memoria o temporales. La prueba de contención abre una segunda conexión SQLite con un bloqueo real y comprueba que reservas y agenda lo tratan como conflicto, seguido de reintento y rechazo de otra reserva activa. No es una prueba de carga ni una medición de rendimiento.

No se calcula aquí la cobertura del 85 %. Ese porcentaje requiere su informe en S9; el número de pruebas no lo acredita.

## Trabajo posterior

S5 conserva Livewire, el quinto CRUD de incidencias, equipos, preparación, resultados y minutos de intervención. S6 conserva la API. S7 completa los tres comandos restantes y los eventos, jobs y correos; los cuatro comandos adelantados en S4 se reutilizan. S8 conserva los PDF y la interfaz ES/EN. El idioma de juego validado en agenda es independiente de traducir la interfaz.

La implementación de este sprint no requiere migraciones nuevas. Los cambios visuales y las secuencias de animación que ya existían mantienen su desarrollo separado del alcance funcional de esta evidencia.
