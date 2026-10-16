# Evidencia del sprint 2 - T-07

Fecha: 16/10/2026. Periodo oficial: 05/10/2026 a 18/10/2026 (23:59).

## Correspondencia con el enunciado

| Criterio docente | Evidencia |
|---|---|
| Diagrama E-R cerrado y entidades justificadas | [Modelo de datos](modelo-datos.md), diagrama Mermaid y justificación de las doce entidades. |
| Migraciones, claves, restricciones e índices | `database/migrations/2026_10_16_000001_create_mythos_domain.php`; claves ajenas, CHECK, índices compuestos y unicidad parcial. |
| Al menos siete modelos que utilizará la API | Experience, Room, GameSession, Reservation, Team, Incident y MaintenanceAction, con relaciones Eloquent. API funcional pendiente de S6. |
| Factories realistas | Doce factories en `database/factories`, con relaciones válidas y personas ficticias. |
| Seeders reproducibles | `MythosDemoSeeder`: datos fijos e idempotencia probada al ejecutarlo dos veces. |
| Relaciones 1:N y N:N | `RelationshipsTest`: catálogo, reservas, personal, equipos, pistas e incidencias, con inversas. |
| Pivote con columnas adicionales manejado desde código | `team_user` (role/joined_at) y `game_session_hint` (used_at/penalty_seconds); attach, lectura, updateExistingPivot y detach probados. |
| Pruebas iniciales de modelos, relaciones y datos | Pest: `DatabaseConstraintsTest`, `RelationshipsTest`, `SeedersTest` y `ReconstructionTest`. |
| Reconstruir, poblar y demostrar desde código | Migraciones desde cero y seeder ejecutados; `scripts/demo_relationships.php` muestra relaciones y datos del pivote. |

## Comprobaciones realizadas

- `composer test`: **43 pruebas correctas, 127 assertions**.
- `php artisan migrate:fresh --seed --no-interaction`: reconstrucción correcta en la base local creada para esta tarea, exclusivamente con datos de demostración.
- `php artisan db:seed --no-interaction`: segunda ejecución correcta; la prueba de idempotencia comprueba los mismos recuentos, valores y ausencia de duplicados.
- `php scripts/demo_relationships.php`: muestra cinco tarifas, cinco reservas del titular demo, dos miembros con su función/fecha, una pista usada, tres comprobaciones, un resultado y una intervención.
- `ReconstructionTest`: migra, revierte y vuelve a migrar en un archivo temporal independiente. Dos conexiones PDO compiten por una sesión: el segundo escritor se bloquea durante la primera transacción y, tras confirmar la primera, la restricción única rechaza su reintento. Solo queda una reserva activa.
- `composer validate --strict`: correcto.
- `php vendor/laravel/pint/builds/pint --test`: formato correcto.
- `npm run build`: compilación correcta de los recursos del esqueleto Laravel.
- Revisión del diff y exclusión de secretos, SQLite, dependencias y temporales antes del commit.

La suite se configura con SQLite en memoria y conexiones temporales para reconstrucción/concurrencia; no utiliza clientes reales ni borra otras bases del equipo. `migrate:fresh` elimina las tablas de la conexión elegida: solo debe repetirse en una base local descartable, nunca sobre datos que haya que conservar.

## Cómo reproducir la comprobación

En una copia recién clonada: ejecutar los pasos de [README](../README.md). Para una base local demo ya preparada, `composer test` y `php scripts/demo_relationships.php` permiten revisar pruebas y relaciones.

El workflow `.github/workflows/tests.yml` ejecuta Pest y Pint en GitHub Actions. Su resultado remoto se consulta en Actions; haber creado el workflow no demuestra por sí solo que la ejecución remota haya terminado correctamente.

## Límites de esta entrega

No se considera implementada la ampliación A5 de borrado lógico. Se conserva histórico con estados y claves restrictivas. Las Policies, Laravel-permission, endpoints, CRUD, Livewire, bloqueos críticos y procesos automáticos se desarrollarán en sus historias. El objetivo de cobertura del 85 % pertenece a S9: estas pruebas iniciales no declaran completado ese objetivo.

Las decisiones de negocio pendientes siguen identificadas en [modelo de datos](modelo-datos.md). No se ha enviado la entrega a Moodle ni se ha realizado la defensa.
