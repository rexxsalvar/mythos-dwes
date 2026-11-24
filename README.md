# Mythos - Proyecto Web DWES

Aplicación de reservas y gestión para el negocio de escape rooms Mythos, en Murcia. Proyecto individual del grado de Desarrollo de Aplicaciones Web.

## Estado · 16/11/2026

Sprint 1: propuesta y planificación revisadas con las correcciones del profesor. Sprint 2: Laravel 13 con SQLite y modelo publicado de 32 tablas de negocio, más permisos, tokens e infraestructura. Sprint 3: autenticación, cinco roles, perfil validado y consultas protegidas. Sprint 4: cinco policies, siete scopes con cuatro consultas complejas, cuatro comandos y servicios reutilizables; tres comandos tienen acciones administrativas. Incluye formularios web, cancelación, precio histórico y control de concurrencia. El repositorio conserva también la interfaz y los recursos de todas las animaciones; las evidencias distinguen la verificación técnica de la entrega docente.

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
| Enunciado de acceso, rutas y validación | [Sprint 3](docs/sprint-3-enunciado.md) |
| Implementación y demostración del acceso | [Evidencia del sprint 3](docs/sprint-3-verificacion.md) |
| Revisión docente y preparación de la defensa | [Corrección del sprint 3](docs/sprint-3-correccion.md) |
| Autorización, scopes, agenda y reservas | [Evidencia del sprint 4](docs/sprint-4-verificacion.md) |
| Lista completa exigida para el sprint 4 | [Enunciado del sprint 4](docs/sprint-4-enunciado.md) |
| Modelo completo y diagramas E-R por áreas | [Modelo de datos](docs/modelo-datos.md) |
| Todas las columnas, claves e índices | [Diccionario](docs/diccionario-datos.md) |
| Estructura SQLite exportada, sin datos | [Esquema SQL](docs/base-datos.sql) |
| Comprobaciones y demostración de la entrega | [Evidencia del sprint 2](docs/sprint-2-verificacion.md) |

## Alcance

Catálogo, reservas, sesiones, preparación de partidas, equipos, resultados e incidencias. Se utilizará Expedición Maldita como referencia real: 75 minutos y de 2 a 6 jugadores. Reglas académicas aprobadas: 15 minutos de bloqueo de pendientes, 30 minutos de preparación antes de cada sesión y cancelación de confirmadas con al menos 24 horas de antelación. Las reservas nuevas conservan su versión de condiciones.

Base instalada: Laravel 13, SQLite y Pest 4, con PHP 8.4. Laravel-permission 8 aplica los cinco perfiles de acceso web. Las cinco policies y los siete scopes ya se utilizan en consultas y servicios. Cuatro de los siete comandos del inventario final están implementados; los de actividad mensual/diaria extraen datos JSON. Sanctum conserva su preparación de datos; los endpoints siguen pendientes de S6. Livewire y laravel-dompdf se incorporarán en sus sprints. La API tendrá siete recursos (User, Experience, Room, GameSession, Reservation, Rate e Incident) y cinco CRUD. Siguen previstos siete componentes Livewire, cinco pares evento-listener, cinco jobs (tres en cola), cinco correos y tres informes PDF complejos. Interfaz ES/EN y cobertura mínima del 85 % son obligatorias y están pendientes de sus sprints.

El enunciado de S3 exige y aplica los cinco roles: cliente, recepción, game master, mantenimiento y administrador. Los mínimos técnicos y las ampliaciones se distinguen en [requisitos](docs/requisitos.md).

## Estado y trabajo pendiente

- Propuesta, backlog y calendario revisados por las correcciones de S1 y el enunciado de S3. Todavía no se ha recibido corrección docente de S2.
- Evidencia histórica de HU-01 conservada en S3. En S4 se amplían validación y componentes a catálogo, salas, agenda y reservas; las comprobaciones actuales se detallan en su evidencia.
- Corrección docente de S3 recibida el 16/11: superada técnicamente y sin bloqueos del núcleo, pendiente de explicar una decisión de acceso y una validación en defensa. La valoración se refiere a las 76 pruebas y 348 comprobaciones del ZIP de S3.
- Verificación local de S4: 137 pruebas y 646 assertions, formato PHP correcto y recursos compilados. La ficha bilingüe sigue vinculada a HU-13; el resto de funciones posteriores mantiene su calendario.
- Impactos de datos pendientes: minutos de intervención de mantenimiento y tipos de documento diario/mantenimiento; se implementarán antes de sus funciones.
- Repositorio público: [rexxsalvar/mythos-dwes](https://github.com/rexxsalvar/mythos-dwes), rama `main`.
- Tablero público vinculado al repositorio: [Mythos - Proyecto DWES](https://github.com/users/rexxsalvar/projects/1).
- Vista de tabla: [Product Backlog](https://github.com/users/rexxsalvar/projects/1/views/2), con campos Prioridad y Sprint previsto.
- T-01 a T-06 completadas. T-07 se amplía el 18/10/2026 para cerrar el modelo previsto para todo el producto. Las tablas de valoraciones y bonos no dan por completadas HU-14/HU-15; el resto del desarrollo permanece planificado.
- Entrega oficial: **12/02/2027**. La subida a la plataforma docente y la defensa corresponden al alumno.
