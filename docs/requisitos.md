# Requisitos y alcance

Referencia: calendario de sprints facilitado por el profesor. Estos requisitos están **planificados**, no implementados. Fecha de revisión: 01/10/2026.

## Mínimos docentes

| Requisito | Aplicación prevista | Sprint | Backlog |
|---|---|---|---|
| Propuesta, Git, tablero y backlog | Documentación y organización inicial | 1 | T-01 a T-06 |
| Diagrama E-R con 1:N y N:N y pivote con columnas propias | Usuarios-reservas; equipos-usuarios con función y fecha de incorporación | 2 | T-07 |
| Migraciones, factories y seeders | Modelo completo y datos de prueba realistas | 2 | T-07 |
| Autenticación y rutas públicas/privadas | Catálogo público y área de cliente/personal | 3 | HU-01 |
| Dos roles con Laravel-permission | Cliente y administrador; otros perfiles previstos para la operativa | 3 | HU-01 |
| Primera Policy con contenido real | ReservationPolicy: propiedad, acción y estado | 3 | HU-01, HU-05 |
| CRUD web | Experiencias, salas, sesiones y reservas; incidencias en la operativa | 4-5 | HU-02 a HU-09 |
| FormRequest en formularios de más de dos campos | Experiencias, sesiones, reservas e incidencias | 4 | T-08 |
| Primer scope funcional | Reservas futuras | 4 | HU-04 |
| Componentes input, fechas, select, labels y checkbox | Componentes Blade reutilizables | 4 | T-08 |
| Tres clases Livewire, dos CRUD completos | Experiencias, reservas y agenda filtrable | 5 | HU-06 |
| Segunda Policy con contenido real | GameSessionPolicy: asignación al game master y estado | 5 | HU-08 |
| Tres scopes; dos complejos | Reservas futuras; disponibilidad con reservas/bloqueos; incidencias por sala, gravedad y estado | 5 | HU-04, HU-06, HU-09 |
| API de tres modelos con dos CRUD completos | Experience y Reservation: CRUD; GameSession: consultas | 6 | HU-10 |
| Un CRUD autenticado | Reservas con Sanctum; escritura del catálogo también protegida | 6 | HU-10 |
| Documentación funcional de API | Peticiones, respuestas, errores y autenticación | 6 y 10 | HU-10, T-11 |
| Tres comandos; uno llamado desde código | Caducar reservas, preparar recordatorios y generar informe desde administración | 7 | T-09 |
| Dos eventos y sus listeners | ReservationConfirmed e IncidentReported, con confirmación y aviso interno | 7 | HU-11 |
| Dos jobs; uno con colas | Generar documento y enviar recordatorios; este último en cola | 7 | HU-11 |
| Dos emails | Confirmación y recordatorio | 7 | HU-11 |
| Dos PDF con laravel-dompdf; uno complejo | Justificante e informe mensual con filtros, agregaciones y totales | 8 | HU-12 |
| Dos idiomas | Español e inglés; requisito mínimo, no ampliación | 8 | HU-13 |
| Pest y cobertura mínima del 85 % | Suite del código de aplicación, con reglas y permisos | 9 | T-10 |
| Cierre técnico, README y revisión de mínimos | Versión candidata y documentación actualizada | 10 | T-11 |
| Integración final, demo y defensa | Preparación de entrega del 12/02/2027 | Final | T-12 |

## Compromisos de producto

La propuesta mantiene gestión de salas, preparación, resultados, equipos e incidencias para resolver un flujo real del negocio. Los cinco perfiles autenticados son una decisión de producto, aunque el mínimo docente sea de dos roles. No se interpreta que el profesor exija cinco CRUD en API, cinco clases CRUD Livewire o cinco PDF.

Las bajas de reservas conservarán el histórico. El contrato de API explicará las restricciones de borrado y cancelación. La confirmación deberá impedir reservas duplicadas incluso en peticiones simultáneas.

## Ampliaciones

Después de los mínimos se priorizan diploma (HU-14), bonos regalo (HU-15) y Excel (HU-16). Pagos online, QR, pasaporte de escapista e informes adicionales quedan como ideas opcionales, sin compromiso de entrega inicial.

## Decisiones de análisis pendientes

- Plazo de cancelación, caducidad de reservas pendientes y tiempo de preparación.
- Experiencias y ubicaciones iniciales; condiciones de confirmación y cobros manuales.
- Criterios de diploma y penalización de pistas.

Estas decisiones se resolverán antes de implementar sus reglas. No impiden entregar una propuesta inicial orientativa.
