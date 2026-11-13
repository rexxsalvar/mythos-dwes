# Calendario de sprints

Fechas oficiales del profesor. Se conservan los diez sprints y la semana final, con las [correcciones de S1](revision-sprint-1.md). Los resultados posteriores a S4 son objetivos; la implementación local del 13/11 se detalla en la [evidencia S4](sprint-4-verificacion.md), conservando la evidencia histórica de S1, S2 y S3.

| Sprint | Fechas | Resultado y criterio de aceptación | Elementos |
|---|---|---|---|
| 1 | 21/09/2026 - 04/10/2026 | Propuesta, nombre Mythos, exclusiones, Git y tablero. Revisión del 20/10: cantidades corregidas y criterios coherentes en propuesta y backlog. | T-01 a T-06 |
| 2 | 05/10/2026 - 18/10/2026 | E-R, modelos, relaciones 1:N/N:N, pivote con datos propios, migraciones, factories, seeders y reconstrucción. Evidencia publicada; corrección docente aún no recibida. | T-07 |
| 3 | 19/10/2026 - 01/11/2026 | Implementado el 26/10: acceso web, cinco roles, rutas, ReservationPolicy y GameSessionPolicy de consulta, FormRequest, cinco componentes y pruebas. Flujo validado de perfil y consultas propias/asignadas. | HU-01 |
| 4 | 02/11/2026 - 15/11/2026 | Enunciado completo implementado el 13/11: cinco policies probadas, siete scopes con cuatro consultas complejas, cuatro comandos con invocación desde administración, servicios reutilizables y validación/componentes. Caducidad, preparación, cancelación, histórico y concurrencia verificados. | HU-02 a HU-05, T-08; parte de T-09 |
| 5 | 16/11/2026 - 29/11/2026 | Incorporar siete clases Livewire reutilizando las policies, scopes y servicios de S4; completar el quinto CRUD de incidencias, equipos, preparación y resultados. Añadir minutos de intervención mediante migración que preserve datos. | HU-06 a HU-09 |
| 6 | 30/11/2026 - 13/12/2026 | API de User, Experience, Room, GameSession, Reservation, Rate e Incident: cinco CRUD; User/Reservation/Incident autenticados; escrituras de experiencias y salas protegidas; contrato y ejemplos. | HU-10 |
| 7 | 14/12/2026 - 27/12/2026 | Completar los tres comandos restantes del inventario de siete y reutilizar los cuatro de S4. Cinco pares evento-listener, cinco jobs con tres en cola y cinco correos; uno de resultado. Los informes en PDF se completan en S8. | HU-11, T-09 |
| 8 | 28/12/2026 - 10/01/2027 | Tres PDF complejos (mensual, diario y mantenimiento), justificante simple e interfaz ES/EN obligatoria. Filtros/agregados verificables, autorización y tipos de documento adaptados. | HU-12, HU-13 |
| 9 | 11/01/2027 - 24/01/2027 | Pest con informe que acredite cobertura mínima del 85 % del código propio; permisos, concurrencia, precio histórico, automatizaciones e informes. Diploma/valoración solo tras los mínimos y si hay capacidad. | T-10; HU-14 opcional |
| 10 | 25/01/2027 - 07/02/2027 | Revisar todos los criterios corregidos, cerrar documentación API y preparar versión candidata. Bonos y Excel únicamente si los mínimos están cubiertos. | T-11; HU-15/HU-16 opcionales |
| Final | 08/02/2027 - 12/02/2027 | Integración, demo, defensa, enlaces y entrega oficial el viernes 12/02. | T-12 |

Los sprints 7 y 8 coinciden con Navidad y concentran más trabajo tras la corrección: se priorizan los requisitos obligatorios sobre las ampliaciones. La semana final conserva las fechas oficiales de lunes a viernes, aunque la introducción original hablase de cuatro días.

La revisión documental de S1 del 20/10 no modifica las fechas originales ni implica reimplementar S2. Los dos ajustes de esquema derivados de los nuevos informes están registrados en la [revisión](revision-sprint-1.md#8-impacto-sobre-el-sprint-2-ya-preparado) y tienen dependencia antes de sus funciones.

El enunciado de S3 recibido el 26/10 adelanta la segunda policy, validación y componentes de los formularios existentes. T-08 conserva la aplicación a futuros CRUD en S4; estos no se dan por completados con las consultas de S3.

El enunciado completo de S4 recibido el 13/11 exige cuatro consultas complejas y cuatro comandos, dos invocados desde código. La evidencia local incluye esas funciones y conserva las fechas de los sprints posteriores.
