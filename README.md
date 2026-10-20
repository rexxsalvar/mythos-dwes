# Mythos - Proyecto Web DWES

Aplicación de reservas y gestión para el negocio de escape rooms Mythos, en Murcia. Proyecto individual del grado de Desarrollo de Aplicaciones Web.

## Estado · 20/10/2026

Sprint 1: propuesta, Product Backlog y tablero revisados con las correcciones del profesor del 20/10. Sprint 2: base Laravel 13 con SQLite y modelo publicado el 18/10: 32 tablas de negocio (29 de núcleo y 3 de ampliaciones previstas), más las tablas de permisos, tokens e infraestructura. Se conserva su evidencia de migraciones, factories, seeders y pruebas iniciales. Las pantallas de negocio, autenticación, roles y API siguen previstas para sus sprints.

| Entregable | Archivo |
|---|---|
| Propuesta formal revisada (4 páginas) | [PDF](output/pdf/Mythos_Desarrollo_de_idea_Sprint_1.pdf) |
| Historias y tareas priorizadas | [Product Backlog](docs/backlog.md) |
| Tablero, estados y objetivo de sprint | [Tablero Scrum](docs/tablero.md) |
| Mínimos docentes y aplicación a Mythos | [Requisitos](docs/requisitos.md) |
| Fechas y objetivos de los sprints | [Calendario](docs/calendario.md) |
| Repositorio y Projects | [Configuración de GitHub](docs/github.md) |
| Correcciones docentes de S1 y criterios | [Revisión del sprint 1](docs/revision-sprint-1.md) |
| Enunciado actualizado del profesor | [Sprint 2](docs/sprint-2-enunciado.md) |
| Modelo completo y diagramas E-R por áreas | [Modelo de datos](docs/modelo-datos.md) |
| Todas las columnas, claves e índices | [Diccionario](docs/diccionario-datos.md) |
| Estructura SQLite exportada, sin datos | [Esquema SQL](docs/base-datos.sql) |
| Comprobaciones y demostración de la entrega | [Evidencia del sprint 2](docs/sprint-2-verificacion.md) |

## Alcance

Catálogo, reservas, sesiones, preparación de partidas, equipos, resultados e incidencias. Se utilizará Expedición Maldita como referencia real: 75 minutos y de 2 a 6 jugadores. Reglas académicas aprobadas: 15 minutos de bloqueo de pendientes y 30 minutos de preparación antes de cada sesión. El plazo de cancelación sigue pendiente.

Base instalada: Laravel 13, SQLite y Pest 4, con PHP 8.4. Laravel-permission 8 y Sanctum 4 aportan sus tablas oficiales y relaciones de datos; autenticación, Policies y endpoints siguen pendientes. Livewire y laravel-dompdf se incorporarán en sus sprints. Idiomas: español e inglés. La API tendrá siete recursos (User, Experience, Room, GameSession, Reservation, Rate e Incident) y cinco CRUD. El alcance corregido incluye cinco policies, siete scopes, siete clases Livewire, siete comandos, cinco pares evento-listener, cinco jobs (tres en cola), cinco correos y tres informes PDF complejos. Interfaz ES/EN y cobertura mínima del 85 % son obligatorias. La implementación continúa en sus sprints.

El mínimo docente de dos roles se cubrirá con cliente y administrador. La propuesta contempla además recepción, game master y mantenimiento. Los mínimos técnicos y las ampliaciones se distinguen en [requisitos](docs/requisitos.md).

## Estado y trabajo pendiente

- Propuesta, backlog, calendario y criterios de tarjetas revisados por las correcciones de S1. Todavía no se ha recibido corrección docente de S2.
- Impactos de datos pendientes: minutos de intervención de mantenimiento y tipos de documento diario/mantenimiento; se implementarán antes de sus funciones.
- Repositorio público: [rexxsalvar/mythos-dwes](https://github.com/rexxsalvar/mythos-dwes), rama `main`.
- Tablero público vinculado al repositorio: [Mythos - Proyecto DWES](https://github.com/users/rexxsalvar/projects/1).
- Vista de tabla: [Product Backlog](https://github.com/users/rexxsalvar/projects/1/views/2), con campos Prioridad y Sprint previsto.
- T-01 a T-06 completadas. T-07 se amplía el 18/10/2026 para cerrar el modelo previsto para todo el producto. Las tablas de valoraciones y bonos no dan por completadas HU-14/HU-15; el resto del desarrollo permanece planificado.
- Entrega oficial: **12/02/2027**. La subida a la plataforma docente y la defensa corresponden al alumno.
