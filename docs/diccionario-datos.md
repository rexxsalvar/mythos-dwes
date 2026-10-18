# Diccionario de la base de datos de Mythos

Esquema de referencia del 18/10/2026, extraído de la base migrada. Incluye estructura, nunca datos de usuarios.

El campo `id` es la clave primaria salvo las claves compuestas de los pivotes y las tablas técnicas. `NULL` significa opcional; los valores de negocio pendientes no se sustituyen por cero. Importes en céntimos enteros, fechas en UTC. Los CHECK e índices completos se consultan en [base-datos.sql](base-datos.sql).

## Catálogo y reglas

### locations

Locales, dirección, contacto y zona horaria. No fija el inventario real del negocio.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `name` | TEXT | no | — | — |
| `code` | TEXT | no | — | — |
| `address` | TEXT | sí | — | — |
| `city` | TEXT | sí | — | — |
| `timezone` | TEXT | no | — | 'Europe/Madrid' |
| `contact_email` | TEXT | sí | — | — |
| `contact_phone` | TEXT | sí | — | — |
| `is_active` | INTEGER | no | — | 1 |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (code).

### experiences

Juego, duración, capacidad y archivo del catálogo. Nombre/descripción base en español para compatibilidad y fallback.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `name` | VARCHAR(255) | no | — | — |
| `slug` | VARCHAR(255) | no | — | — |
| `description` | TEXT | no | — | — |
| `duration_minutes` | INTEGER | no | — | — |
| `min_players` | INTEGER | no | — | — |
| `max_players` | INTEGER | no | — | — |
| `is_active` | INTEGER | no | — | 1 |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |
| `archived_at` | DATETIME | sí | — | — |
| `cover_image_path` | TEXT | sí | — | — |

Índices: UNIQUE (slug).

### experience_translations

Nombre y descripción por idioma, únicos por experiencia y locale.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `experience_id` | INTEGER | no |  / experiences.id (CASCADE) | — |
| `locale` | TEXT | no | — | — |
| `title` | TEXT | no | — | — |
| `description` | TEXT | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (experience_id, locale).

### experience_languages

Idiomas en que se puede jugar; distinto de traducir la ficha comercial.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `experience_id` | INTEGER | no |  / experiences.id (CASCADE) | — |
| `locale` | TEXT | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (experience_id, locale).

### rooms

Sala física, local normalizado, capacidad, estado y experiencia predeterminada actual.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `experience_id` | INTEGER | no |  / experiences.id (RESTRICT) | — |
| `name` | VARCHAR(255) | no | — | — |
| `location` | VARCHAR(255) | no | — | — |
| `capacity` | INTEGER | no | — | — |
| `status` | VARCHAR(30) | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |
| `location_id` | INTEGER | sí |  / locations.id (RESTRICT) | — |
| `archived_at` | DATETIME | sí | — | — |

Índices: (location_id, status); (experience_id, status); UNIQUE (id, experience_id); UNIQUE (name).

### room_experience

Instalaciones históricas de una experiencia en una sala, con periodo de vigencia.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `room_id` | INTEGER | no |  / rooms.id (RESTRICT) | — |
| `experience_id` | INTEGER | no |  / experiences.id (RESTRICT) | — |
| `installed_from` | DATETIME | no | — | — |
| `installed_until` | DATETIME | sí | — | — |
| `notes` | TEXT | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (experience_id); (room_id, installed_from, installed_until); UNIQUE (id, room_id, experience_id); UNIQUE (room_id, installed_from).

### rate_plans

Conjunto de tarifas versionado por vigencia, experiencia y moneda.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `experience_id` | INTEGER | no |  / experiences.id (RESTRICT) | — |
| `code` | TEXT | no | — | — |
| `name` | TEXT | no | — | — |
| `currency` | TEXT | no | — | — |
| `valid_from` | DATETIME | no | — | — |
| `valid_until` | DATETIME | sí | — | — |
| `status` | TEXT | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (experience_id, status, valid_from, valid_until); UNIQUE (id, experience_id, currency); UNIQUE (experience_id, code).

### rates

Precio total del grupo por número de jugadores dentro de un plan.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `rate_plan_id` | INTEGER | no |  / rate_plans.id (RESTRICT), rate_plans.id (RESTRICT) | — |
| `experience_id` | INTEGER | no |  / rate_plans.experience_id (RESTRICT), experiences.id (RESTRICT) | — |
| `players` | INTEGER | no | — | — |
| `total_cents` | INTEGER | no | — | — |
| `currency` | VARCHAR(3) | no |  / rate_plans.currency (RESTRICT) | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (rate_plan_id, players).

### booking_policy_versions

Versiones de condiciones de cancelación, caducidad, preparación, confirmación y cobro. Borradores sin valores inventados.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `experience_id` | INTEGER | no |  / experiences.id (RESTRICT) | — |
| `version` | INTEGER | no | — | — |
| `name` | TEXT | no | — | — |
| `cancellation_notice_minutes` | INTEGER | sí | — | — |
| `pending_expiry_minutes` | INTEGER | sí | — | — |
| `preparation_minutes` | INTEGER | sí | — | — |
| `confirmation_conditions` | TEXT | sí | — | — |
| `payment_conditions` | TEXT | sí | — | — |
| `valid_from` | DATETIME | sí | — | — |
| `valid_until` | DATETIME | sí | — | — |
| `status` | TEXT | no | — | 'draft' |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (experience_id, status); UNIQUE (id, experience_id); UNIQUE (experience_id, version).

## Personas, agenda y reservas

### users

Identidad única de cliente o personal; teléfono, idioma preferido y activación. Roles mediante paquete oficial.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `name` | varchar | no | — | — |
| `email` | varchar | no | — | — |
| `email_verified_at` | datetime | sí | — | — |
| `password` | varchar | no | — | — |
| `remember_token` | varchar | sí | — | — |
| `created_at` | datetime | sí | — | — |
| `updated_at` | datetime | sí | — | — |
| `phone` | TEXT | sí | — | — |
| `preferred_locale` | TEXT | no | — | 'es' |
| `is_active` | INTEGER | no | — | 1 |

Índices: UNIQUE (email).

### game_sessions

Sesión con instalación histórica, sala, experiencia, responsable, horarios previstos/reales, idioma, plantilla y política opcionales.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `room_experience_id` | INTEGER | no |  / room_experience.id (RESTRICT), room_experience.id (RESTRICT) | — |
| `booking_policy_version_id` | INTEGER | sí |  / booking_policy_versions.id (RESTRICT), booking_policy_versions.id (RESTRICT) | — |
| `preparation_template_id` | INTEGER | sí |  / preparation_templates.id (RESTRICT), preparation_templates.id (RESTRICT) | — |
| `locale` | TEXT | no | — | 'es' |
| `actual_started_at` | DATETIME | sí | — | — |
| `actual_ended_at` | DATETIME | sí | — | — |
| `experience_id` | INTEGER | no |  / preparation_templates.experience_id (RESTRICT), booking_policy_versions.experience_id (RESTRICT), room_experience.experience_id (RESTRICT), experiences.id (RESTRICT) | — |
| `room_id` | INTEGER | no |  / room_experience.room_id (RESTRICT), rooms.id (RESTRICT) | — |
| `game_master_id` | INTEGER | no |  / users.id (RESTRICT) | — |
| `starts_at` | DATETIME | no | — | — |
| `ends_at` | DATETIME | no | — | — |
| `preparation_minutes` | INTEGER | sí | — | — |
| `status` | VARCHAR(30) | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (experience_id, status, starts_at); (game_master_id, starts_at); (room_id, starts_at); UNIQUE (id, experience_id); UNIQUE (id, room_id).

### availability_blocks

Bloqueos independientes de sala o responsable, con intervalo, causa y posible incidencia de origen.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `room_id` | INTEGER | sí |  / rooms.id (RESTRICT) | — |
| `user_id` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `incident_id` | INTEGER | sí |  / incidents.id (RESTRICT) | — |
| `created_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `released_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `starts_at` | DATETIME | no | — | — |
| `ends_at` | DATETIME | sí | — | — |
| `released_at` | DATETIME | sí | — | — |
| `reason` | TEXT | no | — | — |
| `kind` | TEXT | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (incident_id); (user_id, starts_at, ends_at, released_at); (room_id, starts_at, ends_at, released_at).

### reservations

Titular, sesión, equipo opcional, participantes, importe acordado, contacto conservado, tarifa/política aplicadas, llegada y cancelación.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `reference` | VARCHAR(36) | no | — | — |
| `user_id` | INTEGER | no |  / users.id (RESTRICT) | — |
| `game_session_id` | INTEGER | no |  / game_sessions.id (RESTRICT) | — |
| `team_id` | INTEGER | sí |  / teams.id (RESTRICT) | — |
| `players` | INTEGER | no | — | — |
| `total_cents` | INTEGER | no | — | — |
| `currency` | VARCHAR(3) | no | — | — |
| `status` | VARCHAR(30) | no | — | — |
| `expires_at` | DATETIME | sí | — | — |
| `confirmed_at` | DATETIME | sí | — | — |
| `cancelled_at` | DATETIME | sí | — | — |
| `needs_review` | INTEGER | no | — | 0 |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |
| `rate_id` | INTEGER | sí |  / rates.id (RESTRICT) | — |
| `booking_policy_version_id` | INTEGER | sí |  / booking_policy_versions.id (RESTRICT) | — |
| `booked_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `contact_name` | TEXT | sí | — | — |
| `contact_email` | TEXT | sí | — | — |
| `contact_phone` | TEXT | sí | — | — |
| `locale` | TEXT | no | — | 'es' |
| `checked_in_at` | DATETIME | sí | — | — |
| `checked_in_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `cancelled_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `cancellation_reason` | TEXT | sí | — | — |
| `notes` | TEXT | sí | — | — |

Índices: UNIQUE (game_session_id) parcial; (status, expires_at); (team_id); (game_session_id); (user_id, status); UNIQUE (reference).

### reservation_participants

Lista de participantes de esa reserva; admite personas sin cuenta y no altera la composición histórica de un equipo.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `reservation_id` | INTEGER | no |  / reservations.id (RESTRICT) | — |
| `user_id` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `position` | INTEGER | no | — | — |
| `display_name` | TEXT | sí | — | — |
| `arrived_at` | DATETIME | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (user_id); UNIQUE (reservation_id, user_id); UNIQUE (reservation_id, position).

### reservation_events

Historial de cambios con autor, momento, motivo y valores anteriores/posteriores JSON.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `reservation_id` | INTEGER | no |  / reservations.id (RESTRICT) | — |
| `actor_id` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `action` | TEXT | no | — | — |
| `occurred_at` | DATETIME | no | — | — |
| `reason` | TEXT | sí | — | — |
| `before_values` | TEXT | sí | — | — |
| `after_values` | TEXT | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (reservation_id, occurred_at).

### payment_transactions

Cobros y devoluciones manuales de una reserva o de un bono, con moneda y referencia de la operación original.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `reference` | TEXT | no | — | — |
| `reservation_id` | INTEGER | sí |  / reservations.id (RESTRICT) | — |
| `gift_voucher_id` | INTEGER | sí |  / gift_vouchers.id (RESTRICT) | — |
| `parent_id` | INTEGER | sí |  / payment_transactions.id (RESTRICT) | — |
| `recorded_by` | INTEGER | no |  / users.id (RESTRICT) | — |
| `kind` | TEXT | no | — | — |
| `amount_cents` | INTEGER | no | — | — |
| `currency` | TEXT | no | — | — |
| `method` | TEXT | no | — | — |
| `status` | TEXT | no | — | — |
| `paid_at` | DATETIME | sí | — | — |
| `notes` | TEXT | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (parent_id); (gift_voucher_id, status); (reservation_id, status, paid_at); UNIQUE (reference).

### teams

Equipo estable y propietario.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `owner_id` | INTEGER | no |  / users.id (RESTRICT) | — |
| `name` | VARCHAR(255) | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (owner_id).

### team_user

Pivote de pertenencia a equipo, con función captain/member y fecha de incorporación.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `team_id` | INTEGER | no | PK / teams.id (CASCADE) | — |
| `user_id` | INTEGER | no | PK / users.id (RESTRICT) | — |
| `role` | VARCHAR(20) | no | — | — |
| `joined_at` | DATETIME | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (user_id); UNIQUE (team_id, user_id).

## Preparación y partidas

### preparation_templates

Lista de preparación versionada de una experiencia.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `experience_id` | INTEGER | no |  / experiences.id (RESTRICT) | — |
| `name` | TEXT | no | — | — |
| `version` | INTEGER | no | — | — |
| `is_active` | INTEGER | no | — | 1 |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (id, experience_id); UNIQUE (experience_id, name, version).

### preparation_template_items

Pasos ordenados de una plantilla y obligatoriedad.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `preparation_template_id` | INTEGER | no |  / preparation_templates.id (RESTRICT) | — |
| `position` | INTEGER | no | — | — |
| `label` | TEXT | no | — | — |
| `is_required` | INTEGER | no | — | 1 |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (preparation_template_id, position).

### preparation_checks

Ejecución por sesión de cada comprobación. Conserva etiqueta y obligatoriedad aunque cambie la plantilla.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `game_session_id` | INTEGER | no |  / game_sessions.id (RESTRICT) | — |
| `checked_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `label` | VARCHAR(255) | no | — | — |
| `is_required` | INTEGER | no | — | 1 |
| `passed` | INTEGER | sí | — | — |
| `checked_at` | DATETIME | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |
| `preparation_template_item_id` | INTEGER | sí |  / preparation_template_items.id (RESTRICT) | — |

Índices: (preparation_template_item_id); (game_session_id).

### hints

Catálogo de pistas de una experiencia.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `experience_id` | INTEGER | no |  / experiences.id (RESTRICT) | — |
| `code` | VARCHAR(50) | no | — | — |
| `description` | TEXT | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (id, experience_id); UNIQUE (experience_id, code).

### game_session_hint

Pivote de utilización de pistas, con momento y penalización aplicada opcional.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `game_session_id` | INTEGER | no | PK / game_sessions.id (RESTRICT) | — |
| `hint_id` | INTEGER | no | PK / hints.id (RESTRICT) | — |
| `used_at` | DATETIME | no | — | — |
| `penalty_seconds` | INTEGER | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (hint_id); UNIQUE (game_session_id, hint_id).

### game_results

Resultado único de la sesión y vínculo al grupo que realmente jugó, con éxito, tiempo y autor.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `game_session_id` | INTEGER | no |  / game_sessions.id (RESTRICT) | — |
| `recorded_by` | INTEGER | no |  / users.id (RESTRICT) | — |
| `escaped` | INTEGER | no | — | — |
| `elapsed_seconds` | INTEGER | no | — | — |
| `notes` | TEXT | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |
| `reservation_id` | INTEGER | sí |  / reservations.id (RESTRICT) | — |

Índices: UNIQUE (reservation_id) parcial; UNIQUE (game_session_id).

## Incidencias y mantenimiento

### incidents

Incidencia de sala, sesión opcional, gravedad, estado, informante y técnico.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `room_id` | INTEGER | no |  / game_sessions.room_id (RESTRICT), rooms.id (RESTRICT) | — |
| `game_session_id` | INTEGER | sí |  / game_sessions.id (RESTRICT), game_sessions.id (RESTRICT) | — |
| `reported_by` | INTEGER | no |  / users.id (RESTRICT) | — |
| `assigned_to` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `title` | VARCHAR(255) | no | — | — |
| `description` | TEXT | no | — | — |
| `severity` | VARCHAR(20) | no | — | — |
| `status` | VARCHAR(20) | no | — | — |
| `resolved_at` | DATETIME | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (assigned_to); (game_session_id); (room_id, severity, status).

### maintenance_actions

Intervenciones sucesivas de mantenimiento.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `incident_id` | INTEGER | no |  / incidents.id (RESTRICT) | — |
| `technician_id` | INTEGER | no |  / users.id (RESTRICT) | — |
| `description` | TEXT | no | — | — |
| `performed_at` | DATETIME | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (technician_id); (incident_id, performed_at).

### reservation_incident

Pivote de reservas afectadas, con detección, revisión, responsable y resolución individual.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `reservation_id` | INTEGER | no |  / reservations.id (RESTRICT) | — |
| `incident_id` | INTEGER | no |  / incidents.id (RESTRICT) | — |
| `detected_at` | DATETIME | no | — | — |
| `reviewed_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `reviewed_at` | DATETIME | sí | — | — |
| `resolution_note` | TEXT | sí | — | — |
| `status` | TEXT | no | — | 'pending' |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (incident_id, status); UNIQUE (reservation_id, incident_id).

## Avisos y documentos

### notification_deliveries

Registro de envíos previstos o realizados y clave de idempotencia; no envía correos por sí mismo.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `idempotency_key` | TEXT | no | — | — |
| `user_id` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `reservation_id` | INTEGER | sí |  / reservations.id (RESTRICT) | — |
| `incident_id` | INTEGER | sí |  / incidents.id (RESTRICT) | — |
| `purpose` | TEXT | no | — | — |
| `channel` | TEXT | no | — | 'email' |
| `recipient` | TEXT | no | — | — |
| `locale` | TEXT | no | — | — |
| `status` | TEXT | no | — | — |
| `scheduled_for` | DATETIME | sí | — | — |
| `sent_at` | DATETIME | sí | — | — |
| `attempts` | INTEGER | no | — | 0 |
| `last_error` | TEXT | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (reservation_id); (status, scheduled_for); UNIQUE (idempotency_key).

### generated_documents

Justificantes, informes y futuros diplomas/Excel, con propietario, filtros, idioma, formato y archivo privado; no genera archivos por sí mismo.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `reference` | TEXT | no | — | — |
| `owner_id` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `requested_by` | INTEGER | no |  / users.id (RESTRICT) | — |
| `reservation_id` | INTEGER | sí |  / reservations.id (RESTRICT) | — |
| `game_result_id` | INTEGER | sí |  / game_results.id (RESTRICT) | — |
| `kind` | TEXT | no | — | — |
| `format` | TEXT | no | — | — |
| `locale` | TEXT | no | — | — |
| `parameters` | TEXT | sí | — | — |
| `status` | TEXT | no | — | — |
| `disk` | TEXT | no | — | 'local' |
| `path` | TEXT | sí | — | — |
| `generated_at` | DATETIME | sí | — | — |
| `checksum` | TEXT | sí | — | — |
| `last_error` | TEXT | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (reservation_id); (owner_id, kind, status); UNIQUE (reference).

## Ampliaciones previstas: estructura solamente

### reviews

Una valoración por reserva completada, de su titular, con moderación.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `reservation_id` | INTEGER | no |  / reservations.id (RESTRICT) | — |
| `user_id` | INTEGER | no |  / users.id (RESTRICT) | — |
| `rating` | INTEGER | no | — | — |
| `comment` | TEXT | sí | — | — |
| `status` | TEXT | no | — | 'pending' |
| `moderated_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `moderated_at` | DATETIME | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (status, created_at); UNIQUE (reservation_id).

### gift_vouchers

Bono por importe o grupo, código almacenado como hash, vigencia y anulación.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `code_hash` | TEXT | no | — | — |
| `experience_id` | INTEGER | sí |  / experiences.id (RESTRICT) | — |
| `purchased_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `issued_by` | INTEGER | sí |  / users.id (RESTRICT) | — |
| `kind` | TEXT | no | — | — |
| `amount_cents` | INTEGER | sí | — | — |
| `players` | INTEGER | sí | — | — |
| `currency` | TEXT | no | — | — |
| `issued_at` | DATETIME | no | — | — |
| `expires_at` | DATETIME | sí | — | — |
| `voided_at` | DATETIME | sí | — | — |
| `recipient_name` | TEXT | sí | — | — |
| `message` | TEXT | sí | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: UNIQUE (code_hash).

### gift_voucher_redemptions

Canje único del bono en una reserva, con autor, fecha e importe aplicado.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `gift_voucher_id` | INTEGER | no |  / gift_vouchers.id (RESTRICT) | — |
| `reservation_id` | INTEGER | no |  / reservations.id (RESTRICT) | — |
| `redeemed_by` | INTEGER | no |  / users.id (RESTRICT) | — |
| `redeemed_at` | DATETIME | no | — | — |
| `applied_cents` | INTEGER | no | — | — |
| `created_at` | DATETIME | sí | — | — |
| `updated_at` | DATETIME | sí | — | — |

Índices: (reservation_id); UNIQUE (gift_voucher_id).

## Infraestructura de Laravel y paquetes

### roles

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `name` | varchar | no | — | — |
| `guard_name` | varchar | no | — | — |
| `created_at` | datetime | sí | — | — |
| `updated_at` | datetime | sí | — | — |

Índices: UNIQUE (name, guard_name).

### permissions

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `name` | varchar | no | — | — |
| `guard_name` | varchar | no | — | — |
| `created_at` | datetime | sí | — | — |
| `updated_at` | datetime | sí | — | — |

Índices: UNIQUE (name, guard_name).

### model_has_roles

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `role_id` | INTEGER | no | PK / roles.id (CASCADE) | — |
| `model_type` | varchar | no | PK | — |
| `model_id` | INTEGER | no | PK | — |

Índices: (model_id, model_type); UNIQUE (role_id, model_id, model_type).

### model_has_permissions

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `permission_id` | INTEGER | no | PK / permissions.id (CASCADE) | — |
| `model_type` | varchar | no | PK | — |
| `model_id` | INTEGER | no | PK | — |

Índices: (model_id, model_type); UNIQUE (permission_id, model_id, model_type).

### role_has_permissions

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `permission_id` | INTEGER | no | PK / permissions.id (CASCADE) | — |
| `role_id` | INTEGER | no | PK / roles.id (CASCADE) | — |

Índices: UNIQUE (permission_id, role_id).

### personal_access_tokens

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `tokenable_type` | varchar | no | — | — |
| `tokenable_id` | INTEGER | no | — | — |
| `name` | TEXT | no | — | — |
| `token` | varchar | no | — | — |
| `abilities` | TEXT | sí | — | — |
| `last_used_at` | datetime | sí | — | — |
| `expires_at` | datetime | sí | — | — |
| `created_at` | datetime | sí | — | — |
| `updated_at` | datetime | sí | — | — |

Índices: (expires_at); UNIQUE (token); (tokenable_type, tokenable_id).

### password_reset_tokens

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `email` | varchar | no | PK | — |
| `token` | varchar | no | — | — |
| `created_at` | datetime | sí | — | — |

Índices: UNIQUE (email).

### sessions

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | varchar | no | PK | — |
| `user_id` | INTEGER | sí | — | — |
| `ip_address` | varchar | sí | — | — |
| `user_agent` | TEXT | sí | — | — |
| `payload` | TEXT | no | — | — |
| `last_activity` | INTEGER | no | — | — |

Índices: (last_activity); (user_id); UNIQUE (id).

### cache

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `key` | varchar | no | PK | — |
| `value` | TEXT | no | — | — |
| `expiration` | INTEGER | no | — | — |

Índices: (expiration); UNIQUE (key).

### cache_locks

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `key` | varchar | no | PK | — |
| `owner` | varchar | no | — | — |
| `expiration` | INTEGER | no | — | — |

Índices: (expiration); UNIQUE (key).

### jobs

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `queue` | varchar | no | — | — |
| `payload` | TEXT | no | — | — |
| `attempts` | INTEGER | no | — | — |
| `reserved_at` | INTEGER | sí | — | — |
| `available_at` | INTEGER | no | — | — |
| `created_at` | INTEGER | no | — | — |

Índices: (queue).

### job_batches

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | varchar | no | PK | — |
| `name` | varchar | no | — | — |
| `total_jobs` | INTEGER | no | — | — |
| `pending_jobs` | INTEGER | no | — | — |
| `failed_jobs` | INTEGER | no | — | — |
| `failed_job_ids` | TEXT | no | — | — |
| `options` | TEXT | sí | — | — |
| `cancelled_at` | INTEGER | sí | — | — |
| `created_at` | INTEGER | no | — | — |
| `finished_at` | INTEGER | sí | — | — |

Índices: UNIQUE (id).

### failed_jobs

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `uuid` | varchar | no | — | — |
| `connection` | TEXT | no | — | — |
| `queue` | TEXT | no | — | — |
| `payload` | TEXT | no | — | — |
| `exception` | TEXT | no | — | — |
| `failed_at` | datetime | no | — | CURRENT_TIMESTAMP |

Índices: UNIQUE (uuid).

### migrations

Tabla técnica de Laravel, Laravel-permission o Sanctum; no equivale a funcionalidad implementada.

| Columna | Tipo SQLite | Nulo | Clave / referencia | Valor por defecto |
|---|---|---|---|---|
| `id` | INTEGER | no | PK | — |
| `migration` | varchar | no | — | — |
| `batch` | INTEGER | no | — | — |
