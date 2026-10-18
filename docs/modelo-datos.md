# Base de datos de Mythos - modelo completo

Revisión: 18/10/2026. Laravel 13, PHP 8.4 y SQLite, manteniendo la elección del alumno.
Esta revisión cierra la estructura prevista del proyecto completo dentro de S2. No adelanta pantallas, endpoints, Policies, correos, documentos ni canjes.

## Cómo estudiarla

1. Leer el mapa de áreas de esta página.
2. Consultar los diagramas por bloques: [catálogo](diagramas/catalogo.md), [reservas](diagramas/reservas.md), [operativa](diagramas/operativa.md) y [documentos](diagramas/documentos.md).
3. Para revisar una columna o índice concreto, usar el [diccionario](diccionario-datos.md).
4. El [diagrama completo](diagramas/completo.md) une las áreas. [base-datos.sql](base-datos.sql) contiene todo el DDL, sin datos; las migraciones son la forma de instalar y actualizar.

## Mapa de entidades y justificación

**32 tablas de dominio: 29 del núcleo y 3 que preparan ampliaciones previstas.** Se añaden 5 tablas oficiales de Laravel-permission y 1 de Sanctum, más 8 tablas técnicas (incluida `migrations`): **46 tablas en total**, sin contar `sqlite_sequence` interna de SQLite.

Las tablas opcionales no cambian su prioridad: disponer del esquema de `reviews` y bonos no completa HU-14/HU-15. Diploma y Excel aprovechan `generated_documents`, sin inventar más tablas para informes calculables.

### Catálogo y reglas

| Tabla | Finalidad |
|---|---|
| `locations` | Locales, dirección, contacto y zona horaria. No fija el inventario real del negocio. |
| `experiences` | Juego, duración, capacidad y archivo del catálogo. Nombre/descripción base en español para compatibilidad y fallback. |
| `experience_translations` | Nombre y descripción por idioma, únicos por experiencia y locale. |
| `experience_languages` | Idiomas en que se puede jugar; distinto de traducir la ficha comercial. |
| `rooms` | Sala física, local normalizado, capacidad, estado y experiencia predeterminada actual. |
| `room_experience` | Instalaciones históricas de una experiencia en una sala, con periodo de vigencia. |
| `rate_plans` | Conjunto de tarifas versionado por vigencia, experiencia y moneda. |
| `rates` | Precio total del grupo por número de jugadores dentro de un plan. |
| `booking_policy_versions` | Versiones de condiciones de cancelación, caducidad, preparación, confirmación y cobro. Borradores sin valores inventados. |

### Personas, agenda y reservas

| Tabla | Finalidad |
|---|---|
| `users` | Identidad única de cliente o personal; teléfono, idioma preferido y activación. Roles mediante paquete oficial. |
| `game_sessions` | Sesión con instalación histórica, sala, experiencia, responsable, horarios previstos/reales, idioma, plantilla y política opcionales. |
| `availability_blocks` | Bloqueos independientes de sala o responsable, con intervalo, causa y posible incidencia de origen. |
| `reservations` | Titular, sesión, equipo opcional, participantes, importe acordado, contacto conservado, tarifa/política aplicadas, llegada y cancelación. |
| `reservation_participants` | Lista de participantes de esa reserva; admite personas sin cuenta y no altera la composición histórica de un equipo. |
| `reservation_events` | Historial de cambios con autor, momento, motivo y valores anteriores/posteriores JSON. |
| `payment_transactions` | Cobros y devoluciones manuales de una reserva o de un bono, con moneda y referencia de la operación original. |
| `teams` | Equipo estable y propietario. |
| `team_user` | Pivote de pertenencia a equipo, con función captain/member y fecha de incorporación. |

### Preparación y partidas

| Tabla | Finalidad |
|---|---|
| `preparation_templates` | Lista de preparación versionada de una experiencia. |
| `preparation_template_items` | Pasos ordenados de una plantilla y obligatoriedad. |
| `preparation_checks` | Ejecución por sesión de cada comprobación. Conserva etiqueta y obligatoriedad aunque cambie la plantilla. |
| `hints` | Catálogo de pistas de una experiencia. |
| `game_session_hint` | Pivote de utilización de pistas, con momento y penalización aplicada opcional. |
| `game_results` | Resultado único de la sesión y vínculo al grupo que realmente jugó, con éxito, tiempo y autor. |

### Incidencias y mantenimiento

| Tabla | Finalidad |
|---|---|
| `incidents` | Incidencia de sala, sesión opcional, gravedad, estado, informante y técnico. |
| `maintenance_actions` | Intervenciones sucesivas de mantenimiento. |
| `reservation_incident` | Pivote de reservas afectadas, con detección, revisión, responsable y resolución individual. |

### Avisos y documentos

| Tabla | Finalidad |
|---|---|
| `notification_deliveries` | Registro de envíos previstos o realizados y clave de idempotencia; no envía correos por sí mismo. |
| `generated_documents` | Justificantes, informes y futuros diplomas/Excel, con propietario, filtros, idioma, formato y archivo privado; no genera archivos por sí mismo. |

### Ampliaciones previstas: estructura solamente

| Tabla | Finalidad |
|---|---|
| `reviews` | Una valoración por reserva completada, de su titular, con moderación. |
| `gift_vouchers` | Bono por importe o grupo, código almacenado como hash, vigencia y anulación. |
| `gift_voucher_redemptions` | Canje único del bono en una reserva, con autor, fecha e importe aplicado. |

## Relaciones y conservación del histórico

- `User 1:N Reservation`; una reserva pertenece a un titular y puede tener participantes sin cuenta. El recuento `players` expresa el tamaño del grupo; la lista nominal puede completarse después sin superar ese tamaño.
- `Team N:N User` mantiene función y fecha de incorporación. La lista de participantes de una reserva es independiente: los cambios posteriores del equipo no alteran quién jugó.
- `Room N:N Experience` se materializa en `room_experience`, con fechas. Cada sesión referencia una instalación concreta; no se cambia su experiencia al sustituir el juego de una sala. `rooms.experience_id` es solo la elección predeterminada actual conservada del esquema inicial, no la fuente del histórico. `rooms.location` conserva la descripción previa; el local normalizado se consulta mediante `location_id` (`venue` en Eloquent).
- `RatePlan 1:N Rate` permite versiones de precio por grupo. `Reservation.total_cents/currency` conserva el importe acordado y `rate_id` la tarifa de origen; editar el catálogo no recalcula reservas históricas.
- `PreparationTemplate 1:N PreparationTemplateItem`; la sesión selecciona una versión. `PreparationCheck` conserva texto, obligatoriedad, resultado, responsable y fecha de su ejecución.
- `GameSession N:N Hint` guarda momento y penalización en el pivote. `GameResult` es único por sesión y puede referenciar la reserva que realmente jugó. El vínculo es nullable para conservar resultados históricos cuya reserva no se conoce.
- `Incident N:N Reservation` guarda detección y revisión individual. `availability_blocks` conserva cada motivo de bloqueo de forma independiente; resolver uno no obliga a liberar los demás.
- `Reservation 1:N PaymentTransaction` separa el importe reservado de cobros y devoluciones. Una devolución referencia un cobro previo; no puede superar su saldo ni cambiar moneda o destinatario económico. El registro es manual; no hay pasarela de pago.
- `GiftVoucher 1:0..1 GiftVoucherRedemption` impide el doble canje desde la clave única. Solo se conserva el hash del código. La valoración es única por reserva completada y solo de su titular.
- `NotificationDelivery.idempotency_key` permite evitar envíos duplicados en S7. `GeneratedDocument` conserva propietario, filtros, idioma y localización privada del archivo que se genere en S8.

## Claves, restricciones e índices

Las migraciones conservan claves ajenas activas, aforos, importes enteros en céntimos, estados permitidos, horarios válidos, coherencia de las referencias compuestas e índices por las consultas previstas. El índice único parcial de reservas activas se mantiene.

Las instalaciones de una sala no se solapan. Una sesión debe estar dentro de su instalación y conserva las restricciones de solapamiento de sala/game master. Los checks de preparación, resultados, tarifas y políticas no pueden apuntar a una experiencia o sesión ajena. No se pueden reducir participantes por debajo de posiciones ya registradas ni mover de sesión una reserva con resultado asociado.

El diccionario enumera índices y referencias; el SQL incluye los CHECK y triggers. Los históricos financieros y operativos tienen borrado restrictivo. `archived_at`/`is_active` permiten retirar catálogo; esto no se presenta como la ampliación A5 `SoftDeletes`.

## Permisos y API: únicamente preparación de datos

Se usan las migraciones y modelos oficiales de Laravel-permission 8 y Sanctum 4. Roles previstos y datos demo: cliente, administrador, recepción, game master y mantenimiento. Se crean sus registros y asociaciones de demostración, sin conceder permisos funcionales todavía ni emitir tokens.

Los siete modelos exigidos para la API siguen siendo Experience, Reservation, GameSession, Room, Team, Incident y MaintenanceAction. La estructura adicional no obliga a exponer todas las tablas ni siete CRUD: los dos CRUD completos previstos se mantienen para S6.

Referencias de los paquetes: [Spatie, instalación](https://spatie.be/docs/laravel-permission/v8/installation-laravel), [requisitos](https://spatie.be/docs/laravel-permission/v8/prerequisites), [Laravel Sanctum](https://github.com/laravel/docs/blob/13.x/sanctum.md).

## Decisiones pendientes, sin valores inventados

Siguen pendientes los plazos de cancelación/caducidad/preparación, confirmación y cobro, inventario inicial real, criterio de diploma, penalizaciones de pistas y condiciones definitivas de bono. `booking_policy_versions` deja esas columnas nulas en un borrador que no se asigna a reservas. `null` no significa cero ni ausencia de restricciones comerciales.

Los idiomas de juego y traducciones son conceptos distintos. Se preparan ES/EN conforme al proyecto; los textos ingleses de los seeders son demostrativos, no una traducción comercial aprobada.

Los bloqueos críticos, la revisión automática de reservas, el cálculo/confirmación de cobros, los correos y los documentos necesitan sus servicios y autorización en sprints posteriores. La base permite guardarlos y protege su integridad, pero las tablas no ejecutan esos flujos por sí solas.

## Migración y datos de demostración

Se conserva la migración inicial publicada y se añade `2026_10_18_000001_complete_mythos_data_model.php`. La ampliación crea tablas, traslada los registros iniciales a instalaciones/planes, amplía columnas y reconstruye las dos tablas cuyas claves cambian dentro de una transacción. Antes de finalizar se comprueba `foreign_key_check`; un fallo revierte el conjunto. Las tablas oficiales de permisos y tokens tienen sus propias migraciones.

Las instalaciones iniciales migradas usan 1970 como límite técnico inferior para conservar sesiones existentes; esa fecha no representa la apertura del negocio. Los planes originales mantienen sus importes y moneda. No se rellenan fechas comerciales desconocidas con hechos ficticios.

La base local se copia antes de migrar y se comprueba que los registros y valores anteriores permanecen. La reversión elimina la estructura nueva y solo es válida mientras los datos sean representables en el modelo inicial; si no lo son, la integridad impide completar la reversión. No se recomienda retroceder una base con nueva actividad real.

`MythosDemoSeeder` conserva el conjunto inicial; `CompleteDataModelSeeder` añade ejemplos relacionados para todas las áreas. Son personas ficticias con correos `@example.test`. No se envían mensajes, generan archivos ni realizan cobros. Los registros de bonos/valoraciones solo muestran su estructura futura.

Para consultar sin modificar: `php scripts/database_overview.php` y `php scripts/demo_relationships.php`. Para cargar datos demo: `php artisan db:seed`. No se ejecuta `migrate:fresh` sobre la base local existente.
