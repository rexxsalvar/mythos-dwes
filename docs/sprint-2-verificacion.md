# Evidencia del sprint 2 - T-07

Evidencia de implementación: 18/10/2026. Anotación de alcance: 20/10/2026. Periodo oficial: 05/10/2026 a 18/10/2026 (23:59). La entrega inicial `4a5b05b` se amplía por indicación del alumno para cerrar la estructura del proyecto completo.

## Correspondencia con el enunciado

| Criterio docente | Evidencia |
|---|---|
| Diagrama E-R cerrado y entidades justificadas | [Modelo completo](modelo-datos.md), [diagramas por áreas](diagramas/completo.md) y [diccionario](diccionario-datos.md): 32 tablas de negocio, distinguiendo núcleo y ampliaciones previstas. |
| Migraciones, claves, restricciones e índices | Se mantiene la migración del 16/10 y se añade `2026_10_18_000001_complete_mythos_data_model.php`, junto con migraciones oficiales de permisos y Sanctum; [DDL completo](base-datos.sql). |
| Al menos siete modelos que utilizará la API | La selección de S2 del 18/10 se actualiza por la corrección de S1: User, Experience, Room, GameSession, Reservation, Rate e Incident; todos ya tienen modelo. Endpoints y cinco CRUD pendientes de S6. |
| Factories realistas | Doce factories iniciales y dieciocho nuevas en `database/factories`, con referencias compatibles y personas ficticias. |
| Seeders reproducibles | `MythosDemoSeeder` y `CompleteDataModelSeeder`: datos ficticios en todas las áreas. Los registros de mensajes/documentos/bonos no ejecutan esas funciones. |
| Relaciones 1:N y N:N | `RelationshipsTest`: catálogo, reservas, personal, equipos, pistas e incidencias, con inversas. |
| Pivote con columnas adicionales manejado desde código | `team_user` (role/joined_at) y `game_session_hint` (used_at/penalty_seconds); attach, lectura, updateExistingPivot y detach probados. |
| Pruebas iniciales de modelos, relaciones y datos | Pest: `DatabaseConstraintsTest`, `RelationshipsTest`, `SeedersTest` y `ReconstructionTest`. |
| Reconstruir, poblar y demostrar desde código | Migraciones desde cero y seeder ejecutados; `scripts/demo_relationships.php` muestra relaciones y datos del pivote. |

## Comprobaciones realizadas

- `composer test`: **se conservan las 43 pruebas anteriores, 127 assertions**. No se añaden nuevas pruebas automatizadas en esta ampliación, por indicación del alumno. Únicamente se adapta el fixture SQL de una prueba existente al nuevo campo obligatorio de instalación. Su cobertura no se presenta como exhaustiva para las tablas nuevas.
- `php artisan migrate --no-interaction`: ampliación sobre la base existente, conservando los registros y valores iniciales (salvo timestamps modificados por el seeder). Copia SQLite previa y transacción de migración; no se vacía la base local.
- `php artisan db:seed --no-interaction`: segunda ejecución correcta; la prueba de idempotencia comprueba los mismos recuentos, valores y ausencia de duplicados.
- `php scripts/database_overview.php`: 46 tablas totales, con cero errores de claves ajenas. `php scripts/demo_relationships.php`: relaciones iniciales y vínculos nuevos de instalación, plantilla, participantes, cobros y traducciones.
- Comprobación directa de las dieciocho factories nuevas, con creación temporal y rollback de esos registros.
- `ReconstructionTest`: migra, revierte y vuelve a migrar en un archivo temporal independiente. Dos conexiones PDO compiten por una sesión: el segundo escritor se bloquea durante la primera transacción y, tras confirmar la primera, la restricción única rechaza su reintento. Solo queda una reserva activa.
- `composer validate --strict`: correcto.
- `php vendor/laravel/pint/builds/pint --test`: formato correcto.
- La compilación de recursos del esqueleto Laravel se comprobó en la entrega inicial. Esta ampliación no modifica el frontend.
- Revisión del diff y exclusión de secretos, SQLite, dependencias y temporales antes del commit.

La suite existente se configura con SQLite en memoria y conexiones temporales para reconstrucción/concurrencia; no utiliza clientes reales ni borra otras bases del equipo. No se ejecuta `migrate:fresh` sobre la base local existente. La reversión del esquema se comprobó durante la preparación y debe limitarse a datos representables en la versión anterior.

## Cómo reproducir la comprobación

Para una base local demo ya preparada, `composer test` y `php scripts/demo_relationships.php` permiten revisar pruebas y relaciones.

El workflow `.github/workflows/tests.yml` ejecuta Pest y Pint en GitHub Actions. Su resultado remoto se consulta en Actions; haber creado el workflow no demuestra por sí solo que la ejecución remota haya terminado correctamente.

## Límites de esta entrega

No se considera implementada la ampliación A5 de borrado lógico. Se conserva histórico con estados y claves restrictivas. Laravel-permission y Sanctum aportan sus tablas, modelos y asociaciones demo; no se implementan flujos de autenticación, Policies o endpoints. Los CRUD, Livewire, gestión funcional de bloqueos, correos, PDFs, valoraciones y canjes se desarrollarán en sus historias. El objetivo obligatorio de cobertura del 85 % se comprobará en S9: estas pruebas iniciales no declaran completado ese objetivo.

Las decisiones de negocio pendientes siguen identificadas en [modelo de datos](modelo-datos.md). No se ha enviado la entrega a Moodle ni se ha realizado la defensa.

## Revisión del alcance del 20/10

El alumno comunica correcciones del profesor al sprint 1; todavía no comunica corrección ni calificación de S2. Se conserva esta evidencia histórica. Los nuevos informes diario y de mantenimiento requieren añadir tipos de documento y minutos de intervención antes de sus historias. Los 15 minutos de bloqueo y 30 de preparación aprobados son reglas académicas por implementar; esta revisión documental no ejecuta migraciones ni modifica la base local. Véase la [revisión de S1](revision-sprint-1.md).

## Continuidad en S3 (26/10)

La evidencia anterior describe S2. El [sprint 3](sprint-3-verificacion.md) incorpora acceso web, roles y dos policies y amplía la suite a 76 casos (348 assertions). Las 43 pruebas iniciales se conservan; este trabajo no cambia el esquema ni constituye una corrección docente de S2.
