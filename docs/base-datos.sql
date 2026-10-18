-- Mythos: esquema SQLite. Generado desde las migraciones; sin datos.
PRAGMA foreign_keys = ON;
BEGIN TRANSACTION;

CREATE TABLE availability_blocks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NULL REFERENCES rooms(id) ON DELETE RESTRICT,
    user_id INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    incident_id INTEGER NULL REFERENCES incidents(id) ON DELETE RESTRICT,
    created_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    released_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    starts_at DATETIME NOT NULL, ends_at DATETIME NULL, released_at DATETIME NULL,
    reason TEXT NOT NULL, kind TEXT NOT NULL CHECK(kind IN ('maintenance','closure','staff_unavailable','other')),
    created_at DATETIME NULL, updated_at DATETIME NULL,
    CHECK((room_id IS NOT NULL AND user_id IS NULL) OR (room_id IS NULL AND user_id IS NOT NULL)), CHECK(ends_at IS NULL OR ends_at > starts_at), CHECK(incident_id IS NULL OR room_id IS NOT NULL), CHECK((released_by IS NULL AND released_at IS NULL) OR (released_by IS NOT NULL AND released_at IS NOT NULL))
);

CREATE TABLE booking_policy_versions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
    version INTEGER NOT NULL CHECK(version > 0), name TEXT NOT NULL,
    cancellation_notice_minutes INTEGER NULL CHECK(cancellation_notice_minutes >= 0),
    pending_expiry_minutes INTEGER NULL CHECK(pending_expiry_minutes > 0),
    preparation_minutes INTEGER NULL CHECK(preparation_minutes >= 0),
    confirmation_conditions TEXT NULL, payment_conditions TEXT NULL,
    valid_from DATETIME NULL, valid_until DATETIME NULL,
    status TEXT NOT NULL DEFAULT 'draft' CHECK(status IN ('draft','active','archived')),
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(experience_id,version), UNIQUE(id,experience_id), CHECK(valid_until IS NULL OR (valid_from IS NOT NULL AND valid_until > valid_from))
);

CREATE TABLE "cache" ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE experience_languages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE CASCADE,
    locale TEXT NOT NULL CHECK(locale IN ('es','en')),
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(experience_id,locale)
);

CREATE TABLE experience_translations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE CASCADE,
    locale TEXT NOT NULL CHECK(locale IN ('es','en')), title TEXT NOT NULL, description TEXT NOT NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(experience_id,locale)
);

CREATE TABLE experiences (
id INTEGER PRIMARY KEY AUTOINCREMENT,
name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL UNIQUE, description TEXT NOT NULL,
duration_minutes INTEGER NOT NULL CHECK(typeof(duration_minutes) = 'integer' AND duration_minutes > 0),
min_players INTEGER NOT NULL CHECK(typeof(min_players) = 'integer' AND min_players > 0),
max_players INTEGER NOT NULL CHECK(typeof(max_players) = 'integer' AND max_players >= min_players),
is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0,1)),
created_at DATETIME NULL, updated_at DATETIME NULL
, archived_at DATETIME NULL, cover_image_path TEXT NULL);

CREATE TABLE "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);

CREATE TABLE game_results (
id INTEGER PRIMARY KEY AUTOINCREMENT,
game_session_id INTEGER NOT NULL UNIQUE REFERENCES game_sessions(id) ON DELETE RESTRICT,
recorded_by INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
escaped INTEGER NOT NULL CHECK(escaped IN (0,1)), elapsed_seconds INTEGER NOT NULL CHECK(typeof(elapsed_seconds) = 'integer' AND elapsed_seconds >= 0),
notes TEXT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL
, reservation_id INTEGER NULL REFERENCES reservations(id) ON DELETE RESTRICT);

CREATE TABLE game_session_hint (
game_session_id INTEGER NOT NULL REFERENCES game_sessions(id) ON DELETE RESTRICT,
hint_id INTEGER NOT NULL REFERENCES hints(id) ON DELETE RESTRICT,
used_at DATETIME NOT NULL, penalty_seconds INTEGER NULL CHECK(penalty_seconds IS NULL OR penalty_seconds >= 0),
created_at DATETIME NULL, updated_at DATETIME NULL,
PRIMARY KEY(game_session_id, hint_id)
);

CREATE TABLE "game_sessions" (
id INTEGER PRIMARY KEY AUTOINCREMENT,
room_experience_id INTEGER NOT NULL REFERENCES room_experience(id) ON DELETE RESTRICT,
booking_policy_version_id INTEGER NULL REFERENCES booking_policy_versions(id) ON DELETE RESTRICT,
preparation_template_id INTEGER NULL REFERENCES preparation_templates(id) ON DELETE RESTRICT,
locale TEXT NOT NULL DEFAULT 'es' CHECK(locale IN ('es','en')),
actual_started_at DATETIME NULL, actual_ended_at DATETIME NULL CHECK(actual_ended_at IS NULL OR (actual_started_at IS NOT NULL AND actual_ended_at >= actual_started_at)),
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
room_id INTEGER NOT NULL REFERENCES rooms(id) ON DELETE RESTRICT,
game_master_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
starts_at DATETIME NOT NULL CHECK(datetime(starts_at) IS NOT NULL AND datetime(starts_at) = starts_at), ends_at DATETIME NOT NULL CHECK(datetime(ends_at) IS NOT NULL AND datetime(ends_at) = ends_at AND ends_at > starts_at),
preparation_minutes INTEGER NULL CHECK(preparation_minutes IS NULL OR preparation_minutes >= 0),
status VARCHAR(30) NOT NULL CHECK(status IN ('scheduled','preparing','in_progress','completed','cancelled')),
created_at DATETIME NULL, updated_at DATETIME NULL,
FOREIGN KEY(room_experience_id,room_id,experience_id) REFERENCES room_experience(id,room_id,experience_id) ON DELETE RESTRICT,
FOREIGN KEY(booking_policy_version_id,experience_id) REFERENCES booking_policy_versions(id,experience_id) ON DELETE RESTRICT,
FOREIGN KEY(preparation_template_id,experience_id) REFERENCES preparation_templates(id,experience_id) ON DELETE RESTRICT,
UNIQUE(id, room_id), UNIQUE(id, experience_id)
);

CREATE TABLE generated_documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    owner_id INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    requested_by INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    reservation_id INTEGER NULL REFERENCES reservations(id) ON DELETE RESTRICT,
    game_result_id INTEGER NULL REFERENCES game_results(id) ON DELETE RESTRICT,
    kind TEXT NOT NULL CHECK(kind IN ('reservation_receipt','monthly_report','diploma','activity_export')),
    format TEXT NOT NULL CHECK(format IN ('pdf','xlsx')), locale TEXT NOT NULL CHECK(locale IN ('es','en')),
    parameters TEXT NULL CHECK(parameters IS NULL OR json_valid(parameters)),
    status TEXT NOT NULL CHECK(status IN ('queued','processing','ready','failed')),
    disk TEXT NOT NULL DEFAULT 'local', path TEXT NULL, generated_at DATETIME NULL,
    checksum TEXT NULL, last_error TEXT NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    CHECK(status <> 'ready' OR (path IS NOT NULL AND generated_at IS NOT NULL)), CHECK(kind <> 'reservation_receipt' OR reservation_id IS NOT NULL), CHECK(kind <> 'diploma' OR game_result_id IS NOT NULL)
);

CREATE TABLE gift_voucher_redemptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gift_voucher_id INTEGER NOT NULL UNIQUE REFERENCES gift_vouchers(id) ON DELETE RESTRICT,
    reservation_id INTEGER NOT NULL REFERENCES reservations(id) ON DELETE RESTRICT,
    redeemed_by INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    redeemed_at DATETIME NOT NULL, applied_cents INTEGER NOT NULL CHECK(typeof(applied_cents)='integer' AND applied_cents >= 0),
    created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE TABLE gift_vouchers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code_hash TEXT NOT NULL UNIQUE CHECK(length(code_hash)=64),
    experience_id INTEGER NULL REFERENCES experiences(id) ON DELETE RESTRICT,
    purchased_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    issued_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    kind TEXT NOT NULL CHECK(kind IN ('amount','group')),
    amount_cents INTEGER NULL CHECK(amount_cents IS NULL OR (typeof(amount_cents)='integer' AND amount_cents > 0)),
    players INTEGER NULL CHECK(players IS NULL OR (typeof(players)='integer' AND players > 0)),
    currency TEXT NOT NULL CHECK(length(currency)=3),
    issued_at DATETIME NOT NULL, expires_at DATETIME NULL, voided_at DATETIME NULL,
    recipient_name TEXT NULL, message TEXT NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    CHECK((kind='amount' AND amount_cents IS NOT NULL AND players IS NULL) OR (kind='group' AND players IS NOT NULL AND experience_id IS NOT NULL AND amount_cents IS NULL)), CHECK(expires_at IS NULL OR expires_at > issued_at)
);

CREATE TABLE hints (
id INTEGER PRIMARY KEY AUTOINCREMENT,
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
code VARCHAR(50) NOT NULL, description TEXT NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL,
UNIQUE(experience_id, code), UNIQUE(id, experience_id)
);

CREATE TABLE incidents (
id INTEGER PRIMARY KEY AUTOINCREMENT,
room_id INTEGER NOT NULL REFERENCES rooms(id) ON DELETE RESTRICT,
game_session_id INTEGER NULL REFERENCES game_sessions(id) ON DELETE RESTRICT,
reported_by INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
assigned_to INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
title VARCHAR(255) NOT NULL, description TEXT NOT NULL,
severity VARCHAR(20) NOT NULL CHECK(severity IN ('low','medium','high','critical')),
status VARCHAR(20) NOT NULL CHECK(status IN ('open','in_progress','resolved')),
resolved_at DATETIME NULL,
created_at DATETIME NULL, updated_at DATETIME NULL,
FOREIGN KEY(game_session_id, room_id) REFERENCES game_sessions(id, room_id) ON DELETE RESTRICT,
CHECK((status = 'resolved' AND resolved_at IS NOT NULL) OR (status <> 'resolved' AND resolved_at IS NULL))
);

CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));

CREATE TABLE "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);

CREATE TABLE locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL, code TEXT NOT NULL UNIQUE,
    address TEXT NULL, city TEXT NULL, timezone TEXT NOT NULL DEFAULT 'Europe/Madrid',
    contact_email TEXT NULL, contact_phone TEXT NULL, is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0,1)),
    created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE TABLE maintenance_actions (
id INTEGER PRIMARY KEY AUTOINCREMENT,
incident_id INTEGER NOT NULL REFERENCES incidents(id) ON DELETE RESTRICT,
technician_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
description TEXT NOT NULL, performed_at DATETIME NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE TABLE "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

CREATE TABLE "model_has_permissions" ("permission_id" integer not null, "model_type" varchar not null, "model_id" integer not null, foreign key("permission_id") references "permissions"("id") on delete cascade, primary key ("permission_id", "model_id", "model_type"));

CREATE TABLE "model_has_roles" ("role_id" integer not null, "model_type" varchar not null, "model_id" integer not null, foreign key("role_id") references "roles"("id") on delete cascade, primary key ("role_id", "model_id", "model_type"));

CREATE TABLE notification_deliveries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    idempotency_key TEXT NOT NULL UNIQUE,
    user_id INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    reservation_id INTEGER NULL REFERENCES reservations(id) ON DELETE RESTRICT,
    incident_id INTEGER NULL REFERENCES incidents(id) ON DELETE RESTRICT,
    purpose TEXT NOT NULL, channel TEXT NOT NULL DEFAULT 'email' CHECK(channel='email'),
    recipient TEXT NOT NULL, locale TEXT NOT NULL CHECK(locale IN ('es','en')),
    status TEXT NOT NULL CHECK(status IN ('scheduled','processing','sent','failed','cancelled')),
    scheduled_for DATETIME NULL, sent_at DATETIME NULL,
    attempts INTEGER NOT NULL DEFAULT 0 CHECK(attempts >= 0), last_error TEXT NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    CHECK(status <> 'sent' OR sent_at IS NOT NULL)
);

CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));

CREATE TABLE payment_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    reservation_id INTEGER NULL REFERENCES reservations(id) ON DELETE RESTRICT,
    gift_voucher_id INTEGER NULL REFERENCES gift_vouchers(id) ON DELETE RESTRICT,
    parent_id INTEGER NULL REFERENCES payment_transactions(id) ON DELETE RESTRICT,
    recorded_by INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    kind TEXT NOT NULL CHECK(kind IN ('receipt','refund')),
    amount_cents INTEGER NOT NULL CHECK(typeof(amount_cents)='integer' AND amount_cents > 0),
    currency TEXT NOT NULL CHECK(length(currency)=3),
    method TEXT NOT NULL CHECK(method IN ('cash','card','transfer','other')),
    status TEXT NOT NULL CHECK(status IN ('pending','settled','void')),
    paid_at DATETIME NULL, notes TEXT NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    CHECK((reservation_id IS NOT NULL AND gift_voucher_id IS NULL) OR (reservation_id IS NULL AND gift_voucher_id IS NOT NULL)), CHECK((kind='receipt' AND parent_id IS NULL) OR (kind='refund' AND parent_id IS NOT NULL)), CHECK(status <> 'settled' OR paid_at IS NOT NULL), CHECK(parent_id IS NULL OR parent_id <> id)
);

CREATE TABLE "permissions" ("id" integer primary key autoincrement not null, "name" varchar not null, "guard_name" varchar not null, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "personal_access_tokens" ("id" integer primary key autoincrement not null, "tokenable_type" varchar not null, "tokenable_id" integer not null, "name" text not null, "token" varchar not null, "abilities" text, "last_used_at" datetime, "expires_at" datetime, "created_at" datetime, "updated_at" datetime);

CREATE TABLE preparation_checks (
id INTEGER PRIMARY KEY AUTOINCREMENT,
game_session_id INTEGER NOT NULL REFERENCES game_sessions(id) ON DELETE RESTRICT,
checked_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
label VARCHAR(255) NOT NULL, is_required INTEGER NOT NULL DEFAULT 1 CHECK(is_required IN (0,1)),
passed INTEGER NULL CHECK(passed IN (0,1)), checked_at DATETIME NULL,
created_at DATETIME NULL, updated_at DATETIME NULL, preparation_template_item_id INTEGER NULL REFERENCES preparation_template_items(id) ON DELETE RESTRICT,
CHECK((checked_by IS NULL AND checked_at IS NULL AND passed IS NULL) OR
(checked_by IS NOT NULL AND checked_at IS NOT NULL AND passed IS NOT NULL))
);

CREATE TABLE preparation_template_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    preparation_template_id INTEGER NOT NULL REFERENCES preparation_templates(id) ON DELETE RESTRICT,
    position INTEGER NOT NULL CHECK(position > 0), label TEXT NOT NULL,
    is_required INTEGER NOT NULL DEFAULT 1 CHECK(is_required IN (0,1)),
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(preparation_template_id,position)
);

CREATE TABLE preparation_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
    name TEXT NOT NULL, version INTEGER NOT NULL CHECK(version > 0),
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0,1)),
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(experience_id,name,version), UNIQUE(id,experience_id)
);

CREATE TABLE rate_plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
    code TEXT NOT NULL, name TEXT NOT NULL, currency TEXT NOT NULL CHECK(length(currency)=3),
    valid_from DATETIME NOT NULL, valid_until DATETIME NULL,
    status TEXT NOT NULL CHECK(status IN ('draft','active','archived')),
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(experience_id,code), UNIQUE(id,experience_id,currency), CHECK(valid_until IS NULL OR valid_until > valid_from)
);

CREATE TABLE "rates" (
id INTEGER PRIMARY KEY AUTOINCREMENT,
rate_plan_id INTEGER NOT NULL REFERENCES rate_plans(id) ON DELETE RESTRICT,
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
players INTEGER NOT NULL CHECK(typeof(players) = 'integer' AND players > 0), total_cents INTEGER NOT NULL CHECK(typeof(total_cents) = 'integer' AND total_cents >= 0),
currency VARCHAR(3) NOT NULL CHECK(length(currency) = 3),
created_at DATETIME NULL, updated_at DATETIME NULL,
UNIQUE(rate_plan_id, players),
FOREIGN KEY(rate_plan_id,experience_id,currency) REFERENCES rate_plans(id,experience_id,currency) ON DELETE RESTRICT
);

CREATE TABLE reservation_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL REFERENCES reservations(id) ON DELETE RESTRICT,
    actor_id INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    action TEXT NOT NULL, occurred_at DATETIME NOT NULL, reason TEXT NULL,
    before_values TEXT NULL CHECK(before_values IS NULL OR json_valid(before_values)),
    after_values TEXT NULL CHECK(after_values IS NULL OR json_valid(after_values)),
    created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE TABLE reservation_incident (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL REFERENCES reservations(id) ON DELETE RESTRICT,
    incident_id INTEGER NOT NULL REFERENCES incidents(id) ON DELETE RESTRICT,
    detected_at DATETIME NOT NULL, reviewed_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    reviewed_at DATETIME NULL, resolution_note TEXT NULL,
    status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','reviewed')),
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(reservation_id,incident_id), CHECK((status='pending' AND reviewed_by IS NULL AND reviewed_at IS NULL) OR (status='reviewed' AND reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL))
);

CREATE TABLE reservation_participants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL REFERENCES reservations(id) ON DELETE RESTRICT,
    user_id INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
    position INTEGER NOT NULL CHECK(typeof(position)='integer' AND position > 0),
    display_name TEXT NULL, arrived_at DATETIME NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(reservation_id,position), UNIQUE(reservation_id,user_id)
);

CREATE TABLE reservations (
id INTEGER PRIMARY KEY AUTOINCREMENT,
reference VARCHAR(36) NOT NULL UNIQUE,
user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
game_session_id INTEGER NOT NULL REFERENCES game_sessions(id) ON DELETE RESTRICT,
team_id INTEGER NULL REFERENCES teams(id) ON DELETE RESTRICT,
players INTEGER NOT NULL CHECK(typeof(players) = 'integer' AND players > 0), total_cents INTEGER NOT NULL CHECK(typeof(total_cents) = 'integer' AND total_cents >= 0),
currency VARCHAR(3) NOT NULL CHECK(length(currency) = 3),
status VARCHAR(30) NOT NULL CHECK(status IN ('pending','confirmed','completed','cancelled','expired')),
expires_at DATETIME NULL, confirmed_at DATETIME NULL, cancelled_at DATETIME NULL,
needs_review INTEGER NOT NULL DEFAULT 0 CHECK(needs_review IN (0,1)),
created_at DATETIME NULL, updated_at DATETIME NULL
, rate_id INTEGER NULL REFERENCES rates(id) ON DELETE RESTRICT, booking_policy_version_id INTEGER NULL REFERENCES booking_policy_versions(id) ON DELETE RESTRICT, booked_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT, contact_name TEXT NULL, contact_email TEXT NULL, contact_phone TEXT NULL, locale TEXT NOT NULL DEFAULT 'es' CHECK(locale IN ('es','en')), checked_in_at DATETIME NULL, checked_in_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT, cancelled_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT, cancellation_reason TEXT NULL, notes TEXT NULL);

CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL UNIQUE REFERENCES reservations(id) ON DELETE RESTRICT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    rating INTEGER NOT NULL CHECK(typeof(rating)='integer' AND rating BETWEEN 1 AND 5),
    comment TEXT NULL, status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','published','hidden')),
    moderated_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT, moderated_at DATETIME NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    CHECK((moderated_by IS NULL AND moderated_at IS NULL) OR (moderated_by IS NOT NULL AND moderated_at IS NOT NULL))
);

CREATE TABLE "role_has_permissions" ("permission_id" integer not null, "role_id" integer not null, foreign key("permission_id") references "permissions"("id") on delete cascade, foreign key("role_id") references "roles"("id") on delete cascade, primary key ("permission_id", "role_id"));

CREATE TABLE "roles" ("id" integer primary key autoincrement not null, "name" varchar not null, "guard_name" varchar not null, "created_at" datetime, "updated_at" datetime);

CREATE TABLE room_experience (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL REFERENCES rooms(id) ON DELETE RESTRICT,
    experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
    installed_from DATETIME NOT NULL, installed_until DATETIME NULL,
    notes TEXT NULL,
    created_at DATETIME NULL, updated_at DATETIME NULL,
    UNIQUE(room_id,installed_from), UNIQUE(id,room_id,experience_id), CHECK(installed_until IS NULL OR installed_until > installed_from)
);

CREATE TABLE rooms (
id INTEGER PRIMARY KEY AUTOINCREMENT,
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
name VARCHAR(255) NOT NULL UNIQUE, location VARCHAR(255) NOT NULL,
capacity INTEGER NOT NULL CHECK(typeof(capacity) = 'integer' AND capacity > 0),
status VARCHAR(30) NOT NULL CHECK(status IN ('operational','maintenance','inactive')),
created_at DATETIME NULL, updated_at DATETIME NULL, location_id INTEGER NULL REFERENCES locations(id) ON DELETE RESTRICT, archived_at DATETIME NULL,
UNIQUE(id, experience_id)
);

CREATE TABLE "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));

CREATE TABLE team_user (
team_id INTEGER NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
role VARCHAR(20) NOT NULL CHECK(role IN ('captain','member')), joined_at DATETIME NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL,
PRIMARY KEY(team_id, user_id)
);

CREATE TABLE teams (
id INTEGER PRIMARY KEY AUTOINCREMENT,
owner_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT, name VARCHAR(255) NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE TABLE "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, phone TEXT NULL, preferred_locale TEXT NOT NULL DEFAULT 'es' CHECK(preferred_locale IN ('es','en')), is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0,1)));

CREATE INDEX actions_incident_time ON maintenance_actions (incident_id, performed_at);

CREATE INDEX actions_technician ON maintenance_actions (technician_id);

CREATE INDEX blocks_incident ON availability_blocks(incident_id);

CREATE INDEX blocks_room_period ON availability_blocks(room_id,starts_at,ends_at,released_at);

CREATE INDEX blocks_user_period ON availability_blocks(user_id,starts_at,ends_at,released_at);

CREATE INDEX "cache_expiration_index" on "cache" ("expiration");

CREATE INDEX "cache_locks_expiration_index" on "cache_locks" ("expiration");

CREATE INDEX checks_session ON preparation_checks (game_session_id);

CREATE INDEX checks_template_item ON preparation_checks(preparation_template_item_id);

CREATE INDEX documents_owner_kind ON generated_documents(owner_id,kind,status);

CREATE INDEX documents_reservation ON generated_documents(reservation_id);

CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");

CREATE INDEX hint_usage_hint ON game_session_hint (hint_id);

CREATE INDEX incident_reviews_pending ON reservation_incident(incident_id,status);

CREATE INDEX incidents_assignee ON incidents (assigned_to);

CREATE INDEX incidents_room_severity_status ON incidents (room_id, severity, status);

CREATE INDEX incidents_session ON incidents (game_session_id);

CREATE INDEX installations_experience ON room_experience(experience_id);

CREATE INDEX installations_room_period ON room_experience(room_id,installed_from,installed_until);

CREATE INDEX "jobs_queue_index" on "jobs" ("queue");

CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions" ("model_id", "model_type");

CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles" ("model_id", "model_type");

CREATE INDEX notifications_pending ON notification_deliveries(status,scheduled_for);

CREATE INDEX notifications_reservation ON notification_deliveries(reservation_id);

CREATE INDEX participants_user ON reservation_participants(user_id);

CREATE INDEX payments_parent ON payment_transactions(parent_id);

CREATE INDEX payments_reservation_status ON payment_transactions(reservation_id,status,paid_at);

CREATE INDEX payments_voucher_status ON payment_transactions(gift_voucher_id,status);

CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions" ("name", "guard_name");

CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens" ("expires_at");

CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens" ("token");

CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens" ("tokenable_type", "tokenable_id");

CREATE INDEX plans_experience_period ON rate_plans(experience_id,status,valid_from,valid_until);

CREATE INDEX policies_experience_status ON booking_policy_versions(experience_id,status);

CREATE INDEX reservation_events_time ON reservation_events(reservation_id,occurred_at);

CREATE UNIQUE INDEX reservations_one_active_per_session ON reservations(game_session_id) WHERE status IN ('pending','confirmed');

CREATE INDEX reservations_session ON reservations (game_session_id);

CREATE INDEX reservations_status_expiry ON reservations (status, expires_at);

CREATE INDEX reservations_team ON reservations (team_id);

CREATE INDEX reservations_user_status ON reservations (user_id, status);

CREATE UNIQUE INDEX result_reservation_unique ON game_results(reservation_id) WHERE reservation_id IS NOT NULL;

CREATE INDEX reviews_moderation ON reviews(status,created_at);

CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles" ("name", "guard_name");

CREATE INDEX rooms_experience_status ON rooms (experience_id, status);

CREATE INDEX rooms_location_status ON rooms(location_id,status);

CREATE INDEX sessions_experience_status_start ON game_sessions (experience_id, status, starts_at);

CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");

CREATE INDEX sessions_master_start ON game_sessions (game_master_id, starts_at);

CREATE INDEX sessions_room_start ON game_sessions (room_id, starts_at);

CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");

CREATE INDEX team_user_user ON team_user (user_id);

CREATE INDEX teams_owner ON teams (owner_id);

CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");

CREATE INDEX voucher_redemptions_reservation ON gift_voucher_redemptions(reservation_id);

CREATE TRIGGER active_plan_no_overlap_insert BEFORE INSERT ON rate_plans
WHEN NEW.status='active' AND EXISTS (SELECT 1 FROM rate_plans p WHERE p.id<>COALESCE(NEW.id,-1) AND p.experience_id=NEW.experience_id AND p.currency=NEW.currency AND p.status='active' AND NEW.valid_from<COALESCE(p.valid_until,'9999-12-31 23:59:59') AND p.valid_from<COALESCE(NEW.valid_until,'9999-12-31 23:59:59'))
BEGIN SELECT RAISE(ABORT, 'Active rate plans overlap'); END;

CREATE TRIGGER active_plan_no_overlap_update BEFORE UPDATE ON rate_plans
WHEN NEW.status='active' AND EXISTS (SELECT 1 FROM rate_plans p WHERE p.id<>COALESCE(NEW.id,-1) AND p.experience_id=NEW.experience_id AND p.currency=NEW.currency AND p.status='active' AND NEW.valid_from<COALESCE(p.valid_until,'9999-12-31 23:59:59') AND p.valid_from<COALESCE(NEW.valid_until,'9999-12-31 23:59:59'))
BEGIN SELECT RAISE(ABORT, 'Active rate plans overlap'); END;

CREATE TRIGGER block_incident_room_insert BEFORE INSERT ON availability_blocks
WHEN NEW.incident_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM incidents i WHERE i.id=NEW.incident_id AND i.room_id=NEW.room_id)
BEGIN SELECT RAISE(ABORT, 'Incident block belongs to another room'); END;

CREATE TRIGGER block_incident_room_update BEFORE UPDATE ON availability_blocks
WHEN NEW.incident_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM incidents i WHERE i.id=NEW.incident_id AND i.room_id=NEW.room_id)
BEGIN SELECT RAISE(ABORT, 'Incident block belongs to another room'); END;

CREATE TRIGGER booking_policy_insert BEFORE INSERT ON reservations
WHEN NEW.booking_policy_version_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM booking_policy_versions p JOIN game_sessions s ON s.experience_id=p.experience_id WHERE p.id=NEW.booking_policy_version_id AND s.id=NEW.game_session_id)
BEGIN SELECT RAISE(ABORT, 'Booking policy belongs to another experience'); END;

CREATE TRIGGER booking_policy_update BEFORE UPDATE ON reservations
WHEN NEW.booking_policy_version_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM booking_policy_versions p JOIN game_sessions s ON s.experience_id=p.experience_id WHERE p.id=NEW.booking_policy_version_id AND s.id=NEW.game_session_id)
BEGIN SELECT RAISE(ABORT, 'Booking policy belongs to another experience'); END;

CREATE TRIGGER booking_preserve_participants BEFORE UPDATE ON reservations
WHEN EXISTS (SELECT 1 FROM reservation_participants p WHERE p.reservation_id=OLD.id AND p.position>NEW.players)
BEGIN SELECT RAISE(ABORT, 'Booking size would lose participants'); END;

CREATE TRIGGER booking_preserve_result BEFORE UPDATE ON reservations
WHEN EXISTS (SELECT 1 FROM game_results r WHERE r.reservation_id=OLD.id AND r.game_session_id<>NEW.game_session_id)
BEGIN SELECT RAISE(ABORT, 'Completed result cannot move to another session'); END;

CREATE TRIGGER booking_rate_insert BEFORE INSERT ON reservations
WHEN NEW.rate_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM rates r JOIN game_sessions s ON s.experience_id=r.experience_id WHERE r.id=NEW.rate_id AND s.id=NEW.game_session_id AND r.players=NEW.players AND r.currency=NEW.currency)
BEGIN SELECT RAISE(ABORT, 'Booking rate belongs to another experience, size or currency'); END;

CREATE TRIGGER booking_rate_update BEFORE UPDATE ON reservations
WHEN NEW.rate_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM rates r JOIN game_sessions s ON s.experience_id=r.experience_id WHERE r.id=NEW.rate_id AND s.id=NEW.game_session_id AND r.players=NEW.players AND r.currency=NEW.currency)
BEGIN SELECT RAISE(ABORT, 'Booking rate belongs to another experience, size or currency'); END;

CREATE TRIGGER check_template_insert BEFORE INSERT ON preparation_checks
WHEN NEW.preparation_template_item_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM preparation_template_items i JOIN game_sessions s ON s.preparation_template_id=i.preparation_template_id WHERE i.id=NEW.preparation_template_item_id AND s.id=NEW.game_session_id)
BEGIN SELECT RAISE(ABORT, 'Check item belongs to another template'); END;

CREATE TRIGGER check_template_update BEFORE UPDATE ON preparation_checks
WHEN NEW.preparation_template_item_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM preparation_template_items i JOIN game_sessions s ON s.preparation_template_id=i.preparation_template_id WHERE i.id=NEW.preparation_template_item_id AND s.id=NEW.game_session_id)
BEGIN SELECT RAISE(ABORT, 'Check item belongs to another template'); END;

CREATE TRIGGER hints_same_experience_insert BEFORE INSERT ON game_session_hint
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN hints h ON h.experience_id = s.experience_id
    WHERE s.id = NEW.game_session_id AND h.id = NEW.hint_id
) BEGIN SELECT RAISE(ABORT, 'Hint belongs to another experience'); END;

CREATE TRIGGER hints_same_experience_update BEFORE UPDATE ON game_session_hint
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN hints h ON h.experience_id = s.experience_id
    WHERE s.id = NEW.game_session_id AND h.id = NEW.hint_id
) BEGIN SELECT RAISE(ABORT, 'Hint belongs to another experience'); END;

CREATE TRIGGER installation_no_overlap_insert BEFORE INSERT ON room_experience
WHEN EXISTS (SELECT 1 FROM room_experience x WHERE x.room_id=NEW.room_id AND x.id<>COALESCE(NEW.id,-1) AND NEW.installed_from < COALESCE(x.installed_until,'9999-12-31 23:59:59') AND x.installed_from < COALESCE(NEW.installed_until,'9999-12-31 23:59:59'))
BEGIN SELECT RAISE(ABORT, 'Room installations overlap'); END;

CREATE TRIGGER installation_no_overlap_update BEFORE UPDATE ON room_experience
WHEN EXISTS (SELECT 1 FROM room_experience x WHERE x.room_id=NEW.room_id AND x.id<>COALESCE(NEW.id,-1) AND NEW.installed_from < COALESCE(x.installed_until,'9999-12-31 23:59:59') AND x.installed_from < COALESCE(NEW.installed_until,'9999-12-31 23:59:59'))
BEGIN SELECT RAISE(ABORT, 'Room installations overlap'); END;

CREATE TRIGGER installation_preserve_sessions BEFORE UPDATE ON room_experience
WHEN EXISTS (SELECT 1 FROM game_sessions s WHERE s.room_experience_id=OLD.id AND (s.room_id<>NEW.room_id OR s.experience_id<>NEW.experience_id OR s.starts_at<NEW.installed_from OR (NEW.installed_until IS NOT NULL AND s.ends_at>NEW.installed_until)))
BEGIN SELECT RAISE(ABORT, 'Installation change would invalidate sessions'); END;

CREATE TRIGGER participant_capacity_insert BEFORE INSERT ON reservation_participants
WHEN NOT EXISTS (SELECT 1 FROM reservations r WHERE r.id=NEW.reservation_id AND NEW.position<=r.players)
BEGIN SELECT RAISE(ABORT, 'Participant exceeds booking size'); END;

CREATE TRIGGER participant_capacity_update BEFORE UPDATE ON reservation_participants
WHEN NOT EXISTS (SELECT 1 FROM reservations r WHERE r.id=NEW.reservation_id AND NEW.position<=r.players)
BEGIN SELECT RAISE(ABORT, 'Participant exceeds booking size'); END;

CREATE TRIGGER rate_capacity_insert BEFORE INSERT ON rates
WHEN NOT EXISTS (
    SELECT 1 FROM experiences e WHERE e.id = NEW.experience_id AND NEW.players BETWEEN e.min_players AND e.max_players
) BEGIN SELECT RAISE(ABORT, 'Rate players outside experience limits'); END;

CREATE TRIGGER rate_capacity_update BEFORE UPDATE ON rates
WHEN NOT EXISTS (
    SELECT 1 FROM experiences e WHERE e.id = NEW.experience_id AND NEW.players BETWEEN e.min_players AND e.max_players
) BEGIN SELECT RAISE(ABORT, 'Rate players outside experience limits'); END;

CREATE TRIGGER receipt_preserve_refunds BEFORE UPDATE ON payment_transactions
WHEN EXISTS (SELECT 1 FROM payment_transactions r WHERE r.parent_id=OLD.id AND r.status<>'void') AND (NEW.kind<>'receipt' OR NEW.status<>'settled' OR NEW.reservation_id IS NOT OLD.reservation_id OR NEW.gift_voucher_id IS NOT OLD.gift_voucher_id OR NEW.currency<>OLD.currency OR NEW.amount_cents<(SELECT SUM(r.amount_cents) FROM payment_transactions r WHERE r.parent_id=OLD.id AND r.status<>'void'))
BEGIN SELECT RAISE(ABORT, 'Receipt change would invalidate refunds'); END;

CREATE TRIGGER redeemed_voucher_immutable BEFORE UPDATE ON gift_vouchers
WHEN EXISTS (SELECT 1 FROM gift_voucher_redemptions r WHERE r.gift_voucher_id=OLD.id) AND (NEW.code_hash<>OLD.code_hash OR NEW.experience_id IS NOT OLD.experience_id OR NEW.kind<>OLD.kind OR NEW.amount_cents IS NOT OLD.amount_cents OR NEW.players IS NOT OLD.players OR NEW.currency<>OLD.currency OR NEW.issued_at<>OLD.issued_at OR NEW.expires_at IS NOT OLD.expires_at OR NEW.voided_at IS NOT OLD.voided_at)
BEGIN SELECT RAISE(ABORT, 'Redeemed voucher terms are historical'); END;

CREATE TRIGGER redemption_valid_insert BEFORE INSERT ON gift_voucher_redemptions
WHEN NOT EXISTS (SELECT 1 FROM gift_vouchers v JOIN reservations r ON r.id=NEW.reservation_id JOIN game_sessions s ON s.id=r.game_session_id WHERE v.id=NEW.gift_voucher_id AND v.voided_at IS NULL AND NEW.redeemed_at>=v.issued_at AND (v.expires_at IS NULL OR NEW.redeemed_at<=v.expires_at) AND (v.experience_id IS NULL OR v.experience_id=s.experience_id) AND v.currency=r.currency AND NEW.applied_cents<=r.total_cents AND (v.kind='group' OR NEW.applied_cents<=v.amount_cents))
BEGIN SELECT RAISE(ABORT, 'Voucher incompatible, expired or void'); END;

CREATE TRIGGER redemption_valid_update BEFORE UPDATE ON gift_voucher_redemptions
WHEN NOT EXISTS (SELECT 1 FROM gift_vouchers v JOIN reservations r ON r.id=NEW.reservation_id JOIN game_sessions s ON s.id=r.game_session_id WHERE v.id=NEW.gift_voucher_id AND v.voided_at IS NULL AND NEW.redeemed_at>=v.issued_at AND (v.expires_at IS NULL OR NEW.redeemed_at<=v.expires_at) AND (v.experience_id IS NULL OR v.experience_id=s.experience_id) AND v.currency=r.currency AND NEW.applied_cents<=r.total_cents AND (v.kind='group' OR NEW.applied_cents<=v.amount_cents))
BEGIN SELECT RAISE(ABORT, 'Voucher incompatible, expired or void'); END;

CREATE TRIGGER refund_limit_insert BEFORE INSERT ON payment_transactions
WHEN NEW.kind='refund' AND NEW.status<>'void' AND NEW.amount_cents+COALESCE((SELECT SUM(p.amount_cents) FROM payment_transactions p WHERE p.parent_id=NEW.parent_id AND p.id<>COALESCE(NEW.id,-1) AND p.status<>'void'),0)>(SELECT amount_cents FROM payment_transactions WHERE id=NEW.parent_id)
BEGIN SELECT RAISE(ABORT, 'Refund exceeds receipt'); END;

CREATE TRIGGER refund_limit_update BEFORE UPDATE ON payment_transactions
WHEN NEW.kind='refund' AND NEW.status<>'void' AND NEW.amount_cents+COALESCE((SELECT SUM(p.amount_cents) FROM payment_transactions p WHERE p.parent_id=NEW.parent_id AND p.id<>COALESCE(NEW.id,-1) AND p.status<>'void'),0)>(SELECT amount_cents FROM payment_transactions WHERE id=NEW.parent_id)
BEGIN SELECT RAISE(ABORT, 'Refund exceeds receipt'); END;

CREATE TRIGGER refund_origin_insert BEFORE INSERT ON payment_transactions
WHEN NEW.kind='refund' AND NOT EXISTS (SELECT 1 FROM payment_transactions p WHERE p.id=NEW.parent_id AND p.kind='receipt' AND p.status='settled' AND p.reservation_id IS NEW.reservation_id AND p.gift_voucher_id IS NEW.gift_voucher_id AND p.currency=NEW.currency)
BEGIN SELECT RAISE(ABORT, 'Refund requires settled receipt for same booking or voucher'); END;

CREATE TRIGGER refund_origin_update BEFORE UPDATE ON payment_transactions
WHEN NEW.kind='refund' AND NOT EXISTS (SELECT 1 FROM payment_transactions p WHERE p.id=NEW.parent_id AND p.kind='receipt' AND p.status='settled' AND p.reservation_id IS NEW.reservation_id AND p.gift_voucher_id IS NEW.gift_voucher_id AND p.currency=NEW.currency)
BEGIN SELECT RAISE(ABORT, 'Refund requires settled receipt for same booking or voucher'); END;

CREATE TRIGGER reservation_capacity_insert BEFORE INSERT ON reservations
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN experiences e ON e.id = s.experience_id JOIN rooms r ON r.id = s.room_id
    WHERE s.id = NEW.game_session_id AND NEW.players BETWEEN e.min_players AND e.max_players AND NEW.players <= r.capacity
) BEGIN SELECT RAISE(ABORT, 'Reservation players outside capacity'); END;

CREATE TRIGGER reservation_capacity_update BEFORE UPDATE ON reservations
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN experiences e ON e.id = s.experience_id JOIN rooms r ON r.id = s.room_id
    WHERE s.id = NEW.game_session_id AND NEW.players BETWEEN e.min_players AND e.max_players AND NEW.players <= r.capacity
) BEGIN SELECT RAISE(ABORT, 'Reservation players outside capacity'); END;

CREATE TRIGGER result_booking_insert BEFORE INSERT ON game_results
WHEN NEW.reservation_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM reservations r WHERE r.id=NEW.reservation_id AND r.game_session_id=NEW.game_session_id)
BEGIN SELECT RAISE(ABORT, 'Result booking belongs to another session'); END;

CREATE TRIGGER result_booking_update BEFORE UPDATE ON game_results
WHEN NEW.reservation_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM reservations r WHERE r.id=NEW.reservation_id AND r.game_session_id=NEW.game_session_id)
BEGIN SELECT RAISE(ABORT, 'Result booking belongs to another session'); END;

CREATE TRIGGER review_booking_insert BEFORE INSERT ON reviews
WHEN NOT EXISTS (SELECT 1 FROM reservations r WHERE r.id=NEW.reservation_id AND r.user_id=NEW.user_id AND r.status='completed')
BEGIN SELECT RAISE(ABORT, 'Only completed booking owner may review'); END;

CREATE TRIGGER review_booking_update BEFORE UPDATE ON reviews
WHEN NOT EXISTS (SELECT 1 FROM reservations r WHERE r.id=NEW.reservation_id AND r.user_id=NEW.user_id AND r.status='completed')
BEGIN SELECT RAISE(ABORT, 'Only completed booking owner may review'); END;

CREATE TRIGGER session_inside_installation_insert BEFORE INSERT ON game_sessions
WHEN NOT EXISTS (SELECT 1 FROM room_experience x WHERE x.id=NEW.room_experience_id AND x.room_id=NEW.room_id AND x.experience_id=NEW.experience_id AND NEW.starts_at>=x.installed_from AND (x.installed_until IS NULL OR NEW.ends_at<=x.installed_until))
BEGIN SELECT RAISE(ABORT, 'Session outside installation period'); END;

CREATE TRIGGER session_inside_installation_update BEFORE UPDATE ON game_sessions
WHEN NOT EXISTS (SELECT 1 FROM room_experience x WHERE x.id=NEW.room_experience_id AND x.room_id=NEW.room_id AND x.experience_id=NEW.experience_id AND NEW.starts_at>=x.installed_from AND (x.installed_until IS NULL OR NEW.ends_at<=x.installed_until))
BEGIN SELECT RAISE(ABORT, 'Session outside installation period'); END;

CREATE TRIGGER sessions_no_overlap_insert BEFORE INSERT ON game_sessions
WHEN NEW.status <> 'cancelled' AND EXISTS (
    SELECT 1 FROM game_sessions s WHERE s.id <> COALESCE(NEW.id, -1) AND s.status <> 'cancelled'
    AND (s.room_id = NEW.room_id OR s.game_master_id = NEW.game_master_id)
    AND datetime(NEW.starts_at, '-' || COALESCE(NEW.preparation_minutes, 0) || ' minutes') < s.ends_at
    AND datetime(s.starts_at, '-' || COALESCE(s.preparation_minutes, 0) || ' minutes') < NEW.ends_at
) BEGIN SELECT RAISE(ABORT, 'Session overlaps room or game master'); END;

CREATE TRIGGER sessions_no_overlap_update BEFORE UPDATE ON game_sessions
WHEN NEW.status <> 'cancelled' AND EXISTS (
    SELECT 1 FROM game_sessions s WHERE s.id <> COALESCE(NEW.id, -1) AND s.status <> 'cancelled'
    AND (s.room_id = NEW.room_id OR s.game_master_id = NEW.game_master_id)
    AND datetime(NEW.starts_at, '-' || COALESCE(NEW.preparation_minutes, 0) || ' minutes') < s.ends_at
    AND datetime(s.starts_at, '-' || COALESCE(s.preparation_minutes, 0) || ' minutes') < NEW.ends_at
) BEGIN SELECT RAISE(ABORT, 'Session overlaps room or game master'); END;

COMMIT;
