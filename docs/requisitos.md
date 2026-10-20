# Requisitos y alcance

Revisión: 20/10/2026. Las [correcciones del profesor al sprint 1](revision-sprint-1.md) actualizan la propuesta y prevalecen sobre sus cantidades anteriores. El [enunciado de S2](sprint-2-enunciado.md) se conserva como fue recibido; todavía no hay corrección docente de S2. La planificación no equivale a implementación ni a calificación.

## Alcance obligatorio corregido

| Requisito | Aplicación en Mythos | Sprint / backlog |
|---|---|---|
| Propuesta, registro, Git y tablero coherentes | Nombre Mythos, exclusiones, enlaces y criterios de aceptación actualizados | 1 y revisión del 20/10; T-01 a T-06 |
| E-R, claves, índices, migraciones, factories, seeders y pruebas iniciales | Modelo publicado: 32 tablas de negocio; relaciones 1:N, N:N y pivotes con datos propios | 2; T-07 |
| Autenticación y roles con Laravel-permission | Cliente y administrador como mínimo; recepción, game master y mantenimiento para la operativa | 3; HU-01 |
| Cinco policies | ReservationPolicy, GameSessionPolicy, ExperiencePolicy, RoomPolicy e IncidentPolicy | 3 a 5; HU-01 a HU-09 |
| Cinco CRUD | Experiencias, salas, sesiones, reservas e incidencias | Web 4-5; API 6; HU-02 a HU-10 |
| FormRequest y componentes reutilizables | Formularios de más de dos campos; input, fechas, select, labels y checkbox | 4; T-08 |
| Siete clases Livewire | Cinco gestores CRUD, agenda y preparación de partidas | 5; HU-06, HU-08 |
| Siete scopes | Catálogo publicado, reservas futuras, reservas activas, disponibilidad, incidencias, tarifas vigentes y mantenimiento por periodo; al menos dos complejos | 4-5; HU-02 a HU-09 |
| Siete recursos API | User, Experience, Room, GameSession, Reservation, Rate e Incident | 6; HU-10 |
| Autorización API | User, Reservation e Incident siempre autenticados; escrituras de experiencias y salas administrativas; mismas reglas que en web | 6; HU-10 |
| Siete comandos Artisan | Caducidad, recordatorios, tres informes, resultados y revisión de reservas afectadas; informe mensual invocado también desde código | 7; T-09 |
| Cinco pares evento-listener | Confirmación, cancelación, incidencia, finalización de partida y solicitud de documento | 7; HU-11 |
| Cinco jobs, tres en cola | Correo al cliente, aviso interno y documento en cola; caducidad y revisión de reservas de forma síncrona | 7; HU-11, T-09 |
| Cinco correos | Confirmación, recordatorio, cancelación, incidencia interna y resultado | 7; HU-11 |
| Tres PDF complejos con laravel-dompdf | Mensual de actividad, diario de operaciones y mantenimiento; filtros y agregados definidos en la revisión | 8; HU-12 |
| Justificante PDF simple | Documento propio de la reserva, con acceso restringido | 8; HU-12 |
| Interfaz bilingüe obligatoria | Español e inglés en navegación, formularios, validación, pantallas y correos; distinto del idioma en que se juega | 8; HU-13 |
| Pest y cobertura mínima del 85 % | Informe medido sobre el código de aplicación y pruebas de reglas, permisos, concurrencia y documentos | 9; T-10 |
| Documentación API y revisión de todos los mínimos | Contrato, errores, permisos, evidencia y versión candidata | 6 y 10; HU-10, T-11 |
| Demo, defensa y entrega | Semana final, sin crear un sprint 11 | Final; T-12; 12/02/2027 |

Los nombres de clases, informes y automatizaciones y sus criterios comprobables están en la [revisión del sprint 1](revision-sprint-1.md). Se conserva el [modelo implementado el 18/10](modelo-datos.md), con los ajustes posteriores identificados expresamente.

## Reglas académicas acordadas

- Una sesión es privada para un solo grupo: como máximo una reserva activa, pendiente o confirmada.
- Pendiente bloqueada durante **15 minutos**, con vencimiento anterior al comienzo de la preparación si este llega antes. No se aceptan nuevas reservas cuando ha comenzado la preparación.
- Preparación de **30 minutos antes del inicio**. Sala y game master se ocupan en `[inicio - 30 minutos, fin)`; se rechazan intersecciones incluso con escrituras concurrentes.
- Estados: pending, confirmed, completed, cancelled y expired. Caducidad cada minuto y comprobación transaccional al reservar o confirmar; no se depende solo del planificador.
- Al confirmar se conservan precio total del grupo, moneda y número de participantes. Editar Rate no altera el histórico de la reserva.
- Las incidencias críticas bloquean nuevas reservas e inicio; las confirmadas afectadas se revisan. Resolver una incidencia no libera otros bloqueos activos.

Los 15 y 30 minutos fueron aprobados por el alumno el 20/10 como decisiones académicas, no como condiciones reales del negocio. Su implementación funcional sigue pendiente de las historias correspondientes.

## Ampliaciones y exclusiones

Valoraciones y diploma (HU-14), bonos regalo (HU-15) y Excel (HU-16) son opcionales. El correo de resultado es obligatorio y no depende de emitir diploma. Las estructuras de valoraciones y bonos existentes no completan esas funciones.

Pagos online, QR, pasaporte de escapista, logística física de bonos y publicación en producción quedan fuera de la entrega inicial. Se registran cobros manuales; no se implementa contabilidad ni facturación fiscal. El negocio real aporta contexto, pero la cobertura académica se demuestra mediante los criterios y evidencias del proyecto.

## Decisiones e impactos pendientes

- Plazo de cancelación, condiciones definitivas de confirmación/cobro, inventario inicial real, criterio del diploma y penalizaciones de pistas.
- Comprobar el nombre Mythos en el registro externo del centro; su cambio no se da por realizado desde este repositorio.
- Antes de HU-09/HU-12: nueva migración para minutos de intervención y tipos `daily_report` y `maintenance_report` en documentos. No se cambian datos ni migraciones de S2 con esta revisión documental.
