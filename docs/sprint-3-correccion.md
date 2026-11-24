# Corrección docente del sprint 3

Fecha del informe: 16/11/2026. Fuente: revisión formativa del profesor facilitada por el alumno. La evidencia revisada corresponde a Mythos-Sprint-3 y al ZIP de cierre del 1 de noviembre, no al incremento posterior de S4.

## Resultado

**Superada técnicamente, pendiente de defensa.** El profesor no detecta bloqueos del núcleo de S3. Comprueba reconstrucción y siembra correctas, 76 pruebas con 348 comprobaciones, dos policies, cuatro FormRequest, middleware de cuenta activa, permisos y `docs/sprint-3-verificacion.md`.

| Requisito | Valoración observada |
|---|---|
| Autenticación y sesión | Registro seguro, límite de intentos, salida y cuenta activa. |
| Rutas y zonas | Áreas de cliente, personal, mantenimiento y administración. |
| Roles y permisos | Cinco perfiles con permisos y consultas filtradas. |
| Policies | ReservationPolicy protege al titular y GameSessionPolicy al game master asignado. |
| FormRequest | Registro, acceso, perfil y agenda integrados. |
| Componentes | Cinco tipos reutilizados y errores asociados. |
| Pruebas | 76 correctas y 348 comprobaciones en la entrega evaluada. |

El informe reconoce Mythos como continuidad del proyecto propio registrado originalmente como EscapeHub. No solicita renombrar la aplicación ni demuestra por sí mismo un cambio en el registro externo del centro.

## Indicaciones para los incrementos posteriores

- Mantener el control por registro en listados y detalles. El cliente accede a sus reservas, el game master a sus sesiones y el técnico a sus incidencias asignadas.
- Autorizar antes de mostrar datos y extender ese control a las escrituras. Un permiso de lectura no debe conceder edición, confirmación o cancelación automáticamente.
- Conservar las pruebas de datos manipulados, inyección de privilegios, cuentas inactivas y formatos inválidos. El registro fija el rol cliente y el perfil admite solo los campos seguros.
- Mantener las fechas de entrada en Europe/Madrid y el almacenamiento en UTC, incluidos filtros con una sola fecha, reservas, recordatorios y límites de cancelación.

No se pide completar la creación de reservas para aprobar S3. Las operaciones del dominio, mantenimiento, CRUD y documentos citados como pendientes se refieren al alcance del ZIP evaluado. El estado del incremento posterior se describe por separado en la [evidencia de S4](sprint-4-verificacion.md). Esta corrección no exige revertirlo ni presentar las 76 pruebas históricas como el total actual del proyecto.

## Preparación de la defensa

Hay que explicar una decisión de acceso y una validación. El recorrido solicitado es intentar abrir como game master una sesión ajena y explicar las tres comprobaciones.

1. El permiso general permite acceder al área de agenda, sin conceder acceso a todos sus registros.
2. El controlador filtra el listado por `game_master_id` cuando el usuario no dispone de consulta global.
3. `GameSessionPolicy::view` vuelve a comprobar la asignación al consultar el detalle. Escribir una URL ajena directamente tampoco permite acceder.

Como ejemplo de validación, `AgendaFilterRequest` comprueba formato y orden de fechas. El controlador convierte los días de Madrid a un intervalo UTC; la fecha final incluye el día completo mediante el límite exclusivo del día siguiente.

El cierre docente definitivo queda sujeto a esa defensa. Esta valoración no es una corrección ni una calificación del sprint 4.
