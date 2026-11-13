# Tablero Scrum - Mythos

Revisión local: 13/11/2026. Sprint activo: **4, del 02/11 al 15/11**. Objetivo: cinco policies, siete scopes con cuatro consultas complejas, cuatro comandos y servicios de aplicación, conforme al [enunciado completo](sprint-4-enunciado.md). [Evidencia S4](sprint-4-verificacion.md).

Tablero existente: [GitHub Projects](https://github.com/users/rexxsalvar/projects/1). Se mantienen las 28 tarjetas. Criterios: [backlog](backlog.md), [revisión S1](revision-sprint-1.md) y [evidencia S3](sprint-3-verificacion.md).

| Backlog | Por hacer | En curso | En revisión | Bloqueado | Hecho |
|---|---|---|---|---|---|
| HU-06 a HU-16; T-09 a T-12 | — | — | HU-02: núcleo web verificado; traducción de ficha pendiente con HU-13 | — | T-01 a T-06: entregables de S1 |
| | | | | | T-07: modelo y comprobaciones S2 |
| | | | | | HU-01: acceso, permisos y flujo validado S3 |
| | | | | | HU-03, HU-04, HU-05 y T-08: agenda, reservas y validación S4 |

Resultado local: doce elementos con evidencia, uno en revisión y quince en backlog. HU-02 conserva pendiente la traducción comprometida para S8; sus operaciones web de S4 están verificadas. HU-08 conserva preparación y resultados, reutilizando las autorizaciones previstas en GameSessionPolicy. Esta tabla no acredita cambios en las tarjetas remotas de GitHub Projects.

T-09 conserva el cierre global en S7, con cuatro de sus siete comandos ya implementados en S4. Caducidad, revisión y extracción mensual tienen acciones administrativas; el resumen diario se ejecuta desde Artisan. Sus pruebas están en OperationalCommandsTest y ActivityReportCommandsTest.

## Reglas de uso

- Backlog: trabajo pendiente de selección.
- Por hacer: seleccionado para el sprint activo.
- En curso: máximo una tarjeta en el proyecto individual.
- En revisión: máximo dos tarjetas, con evidencia pendiente de comprobar.
- Bloqueado: registrar causa y siguiente acción.
- Hecho: criterios cumplidos con implementación y evidencia. Una consulta no completa un CRUD y documentar no implementa una función.

Se conservan las fechas oficiales y la semana final, sin sprint 11. HU-14/HU-15/HU-16 siguen opcionales. No hay confirmación de entrega en Moodle ni de cambio del nombre en el registro del centro; no se afirman acciones externas no comprobadas.
