# Requisitos y alcance

Revisión: 13/11/2026. Las [correcciones de S1](revision-sprint-1.md) y los enunciados recibidos fijan el alcance. El [enunciado completo de S4](sprint-4-enunciado.md) exige cuatro scopes complejos y adelanta cuatro comandos, dos invocados desde código. Su [evidencia](sprint-4-verificacion.md) distingue la implementación del trabajo posterior. Se conserva la evidencia histórica de S2 y S3; la planificación no equivale a calificación.

## Alcance obligatorio corregido

| Requisito | Aplicación en Mythos | Sprint / backlog |
|---|---|---|
| Propuesta, registro, Git y tablero coherentes | Nombre Mythos, exclusiones, enlaces y criterios de aceptación actualizados | 1 y revisión del 20/10; T-01 a T-06 |
| E-R, claves, índices, migraciones, factories, seeders y pruebas iniciales | Modelo publicado: 32 tablas de negocio; relaciones 1:N, N:N y pivotes con datos propios | 2; T-07 |
| Autenticación y roles con Laravel-permission | Cinco roles obligatorios de S3: cliente, administrador, recepción, game master y mantenimiento; implementados | 3; HU-01 |
| Cinco policies | ReservationPolicy, GameSessionPolicy, ExperiencePolicy, RoomPolicy e IncidentPolicy implementadas y probadas con casos permitidos y denegados | 3-4; HU-01 a HU-09 |
| Cinco CRUD | Experiencias, salas, sesiones, reservas e incidencias | Web 4-5; API 6; HU-02 a HU-10 |
| FormRequest y componentes reutilizables | S3: los cuatro formularios existentes y cinco componentes; S4: futuros formularios CRUD | 3-4; T-08 |
| Siete clases Livewire | Cinco gestores CRUD, agenda y preparación de partidas | 5; HU-06, HU-08 |
| Siete scopes | Catálogo, reservas futuras/afectadas, reservas activas, disponibilidad, incidencias, tarifas y mantenimiento por periodo; cuatro consultas complejas verificadas | 4; HU-02 a HU-09 |
| Siete recursos API | User, Experience, Room, GameSession, Reservation, Rate e Incident | 6; HU-10 |
| Autorización API | User, Reservation e Incident siempre autenticados; escrituras de experiencias y salas administrativas; mismas reglas que en web | 6; HU-10 |
| Siete comandos Artisan | S4 implementa caducidad, revisión de afectadas y extracción mensual/diaria; tres se invocan desde administración. S7 completa recordatorios, resultados y mantenimiento y conecta automatizaciones; los PDF se completan en S8 | 4 y 7-8; T-09 |
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
- Cancelación de confirmadas con al menos 24 horas de antelación. Las nuevas reservas conservan la versión de condiciones; no se aplica retroactivamente a registros sin condiciones conocidas.
- Las incidencias críticas bloquean nuevas reservas e inicio; las confirmadas afectadas se revisan. Resolver una incidencia no libera otros bloqueos activos.

Los 15 y 30 minutos fueron aprobados por el alumno el 20/10 y las 24 horas de cancelación el 13/11. Son decisiones académicas, no condiciones comerciales verificadas de Mythos. Están implementadas en los servicios de S4. La caducidad tiene entrada en el planificador; su ejecución periódica requiere mantener activo dicho proceso.

## Ampliaciones y exclusiones

Valoraciones y diploma (HU-14), bonos regalo (HU-15) y Excel (HU-16) son opcionales. El correo de resultado es obligatorio y no depende de emitir diploma. Las estructuras de valoraciones y bonos existentes no completan esas funciones.

Pagos online, QR, pasaporte de escapista, logística física de bonos y publicación en producción quedan fuera de la entrega inicial. Se registran cobros manuales; no se implementa contabilidad ni facturación fiscal. El negocio real aporta contexto, pero la cobertura académica se demuestra mediante los criterios y evidencias del proyecto.

## Decisiones e impactos pendientes

- Condiciones definitivas de cobro, inventario inicial real, criterio del diploma y penalizaciones de pistas. La confirmación manual de S4 no acredita haber cobrado.
- Comprobar el nombre Mythos en el registro externo del centro; su cambio no se da por realizado desde este repositorio.
- Antes de HU-09/HU-12: nueva migración para minutos de intervención y tipos `daily_report` y `maintenance_report` en documentos. No se cambian datos ni migraciones de S2 con esta revisión documental.
