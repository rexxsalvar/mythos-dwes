# Calendario de sprints

Fechas oficiales del profesor. Alcance revisado el 20/10/2026 por sus [correcciones al sprint 1](revision-sprint-1.md); se conservan los diez sprints y la semana final. Los resultados siguientes son objetivos, no funciones ya implementadas.

| Sprint | Fechas | Resultado y criterio de aceptación | Elementos |
|---|---|---|---|
| 1 | 21/09/2026 - 04/10/2026 | Propuesta, nombre Mythos, exclusiones, Git y tablero. Revisión del 20/10: cantidades corregidas y criterios coherentes en propuesta y backlog. | T-01 a T-06 |
| 2 | 05/10/2026 - 18/10/2026 | E-R, modelos, relaciones 1:N/N:N, pivote con datos propios, migraciones, factories, seeders y reconstrucción. Evidencia publicada; corrección docente aún no recibida. | T-07 |
| 3 | 19/10/2026 - 01/11/2026 | Autenticación, roles, rutas y ReservationPolicy. Diseñar la matriz completa de cinco policies; denegar acceso a reservas ajenas. | HU-01 |
| 4 | 02/11/2026 - 15/11/2026 | CRUD web de experiencias, salas, sesiones y reservas; FormRequest y componentes. Bloqueo de 15 minutos, preparación de 30, precio/grupo conservados y concurrencia. ExperiencePolicy y RoomPolicy; primeros scopes. | HU-02 a HU-05, T-08 |
| 5 | 16/11/2026 - 29/11/2026 | Completar cinco CRUD, cinco policies, siete scopes y siete clases Livewire; equipos, preparación, resultados e incidencias. Incorporar minutos de intervención con migración que preserve datos. | HU-06 a HU-09 |
| 6 | 30/11/2026 - 13/12/2026 | API de User, Experience, Room, GameSession, Reservation, Rate e Incident: cinco CRUD; User/Reservation/Incident autenticados; escrituras de experiencias y salas protegidas; contrato y ejemplos. | HU-10 |
| 7 | 14/12/2026 - 27/12/2026 | Siete comandos, cinco pares evento-listener, cinco jobs con tres en cola y cinco correos; uno de resultado. Informe mensual invocado también desde código. Plantillas PDF se completan en S8. | HU-11, T-09 |
| 8 | 28/12/2026 - 10/01/2027 | Tres PDF complejos (mensual, diario y mantenimiento), justificante simple e interfaz ES/EN obligatoria. Filtros/agregados verificables, autorización y tipos de documento adaptados. | HU-12, HU-13 |
| 9 | 11/01/2027 - 24/01/2027 | Pest con informe que acredite cobertura mínima del 85 % del código propio; permisos, concurrencia, precio histórico, automatizaciones e informes. Diploma/valoración solo tras los mínimos y si hay capacidad. | T-10; HU-14 opcional |
| 10 | 25/01/2027 - 07/02/2027 | Revisar todos los criterios corregidos, cerrar documentación API y preparar versión candidata. Bonos y Excel únicamente si los mínimos están cubiertos. | T-11; HU-15/HU-16 opcionales |
| Final | 08/02/2027 - 12/02/2027 | Integración, demo, defensa, enlaces y entrega oficial el viernes 12/02. | T-12 |

Los sprints 7 y 8 coinciden con Navidad y concentran más trabajo tras la corrección: se priorizan los requisitos obligatorios sobre las ampliaciones. La semana final conserva las fechas oficiales de lunes a viernes, aunque la introducción original hablase de cuatro días.

La revisión documental de S1 del 20/10 no modifica las fechas originales ni implica reimplementar S2. Los dos ajustes de esquema derivados de los nuevos informes están registrados en la [revisión](revision-sprint-1.md#8-impacto-sobre-el-sprint-2-ya-preparado) y tienen dependencia antes de sus funciones.
