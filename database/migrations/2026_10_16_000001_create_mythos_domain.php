<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('This sprint targets SQLite. Port CHECKs, partial index and triggers before changing engine.');
        }

        DB::transaction(function () {
            DB::unprepared(<<<'SQL'
CREATE TABLE experiences (
id INTEGER PRIMARY KEY AUTOINCREMENT,
name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL UNIQUE, description TEXT NOT NULL,
duration_minutes INTEGER NOT NULL CHECK(typeof(duration_minutes) = 'integer' AND duration_minutes > 0),
min_players INTEGER NOT NULL CHECK(typeof(min_players) = 'integer' AND min_players > 0),
max_players INTEGER NOT NULL CHECK(typeof(max_players) = 'integer' AND max_players >= min_players),
is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0,1)),
created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE TABLE rooms (
id INTEGER PRIMARY KEY AUTOINCREMENT,
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
name VARCHAR(255) NOT NULL UNIQUE, location VARCHAR(255) NOT NULL,
capacity INTEGER NOT NULL CHECK(typeof(capacity) = 'integer' AND capacity > 0),
status VARCHAR(30) NOT NULL CHECK(status IN ('operational','maintenance','inactive')),
created_at DATETIME NULL, updated_at DATETIME NULL,
UNIQUE(id, experience_id)
);

CREATE TABLE rates (
id INTEGER PRIMARY KEY AUTOINCREMENT,
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
players INTEGER NOT NULL CHECK(typeof(players) = 'integer' AND players > 0), total_cents INTEGER NOT NULL CHECK(typeof(total_cents) = 'integer' AND total_cents >= 0),
currency VARCHAR(3) NOT NULL CHECK(length(currency) = 3),
created_at DATETIME NULL, updated_at DATETIME NULL,
UNIQUE(experience_id, players)
);

CREATE TABLE teams (
id INTEGER PRIMARY KEY AUTOINCREMENT,
owner_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT, name VARCHAR(255) NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE TABLE team_user (
team_id INTEGER NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
role VARCHAR(20) NOT NULL CHECK(role IN ('captain','member')), joined_at DATETIME NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL,
PRIMARY KEY(team_id, user_id)
);

CREATE TABLE game_sessions (
id INTEGER PRIMARY KEY AUTOINCREMENT,
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
room_id INTEGER NOT NULL REFERENCES rooms(id) ON DELETE RESTRICT,
game_master_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
starts_at DATETIME NOT NULL CHECK(datetime(starts_at) IS NOT NULL AND datetime(starts_at) = starts_at), ends_at DATETIME NOT NULL CHECK(datetime(ends_at) IS NOT NULL AND datetime(ends_at) = ends_at AND ends_at > starts_at),
preparation_minutes INTEGER NULL CHECK(preparation_minutes IS NULL OR preparation_minutes >= 0),
status VARCHAR(30) NOT NULL CHECK(status IN ('scheduled','preparing','in_progress','completed','cancelled')),
created_at DATETIME NULL, updated_at DATETIME NULL,
FOREIGN KEY(room_id, experience_id) REFERENCES rooms(id, experience_id) ON DELETE RESTRICT,
UNIQUE(id, room_id), UNIQUE(id, experience_id)
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
);

CREATE TABLE hints (
id INTEGER PRIMARY KEY AUTOINCREMENT,
experience_id INTEGER NOT NULL REFERENCES experiences(id) ON DELETE RESTRICT,
code VARCHAR(50) NOT NULL, description TEXT NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL,
UNIQUE(experience_id, code), UNIQUE(id, experience_id)
);

CREATE TABLE game_session_hint (
game_session_id INTEGER NOT NULL REFERENCES game_sessions(id) ON DELETE RESTRICT,
hint_id INTEGER NOT NULL REFERENCES hints(id) ON DELETE RESTRICT,
used_at DATETIME NOT NULL, penalty_seconds INTEGER NULL CHECK(penalty_seconds IS NULL OR penalty_seconds >= 0),
created_at DATETIME NULL, updated_at DATETIME NULL,
PRIMARY KEY(game_session_id, hint_id)
);

CREATE TABLE preparation_checks (
id INTEGER PRIMARY KEY AUTOINCREMENT,
game_session_id INTEGER NOT NULL REFERENCES game_sessions(id) ON DELETE RESTRICT,
checked_by INTEGER NULL REFERENCES users(id) ON DELETE RESTRICT,
label VARCHAR(255) NOT NULL, is_required INTEGER NOT NULL DEFAULT 1 CHECK(is_required IN (0,1)),
passed INTEGER NULL CHECK(passed IN (0,1)), checked_at DATETIME NULL,
created_at DATETIME NULL, updated_at DATETIME NULL,
CHECK((checked_by IS NULL AND checked_at IS NULL AND passed IS NULL) OR
(checked_by IS NOT NULL AND checked_at IS NOT NULL AND passed IS NOT NULL))
);

CREATE TABLE game_results (
id INTEGER PRIMARY KEY AUTOINCREMENT,
game_session_id INTEGER NOT NULL UNIQUE REFERENCES game_sessions(id) ON DELETE RESTRICT,
recorded_by INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
escaped INTEGER NOT NULL CHECK(escaped IN (0,1)), elapsed_seconds INTEGER NOT NULL CHECK(typeof(elapsed_seconds) = 'integer' AND elapsed_seconds >= 0),
notes TEXT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL
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

CREATE TABLE maintenance_actions (
id INTEGER PRIMARY KEY AUTOINCREMENT,
incident_id INTEGER NOT NULL REFERENCES incidents(id) ON DELETE RESTRICT,
technician_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
description TEXT NOT NULL, performed_at DATETIME NOT NULL,
created_at DATETIME NULL, updated_at DATETIME NULL
);

CREATE INDEX rooms_experience_status ON rooms (experience_id, status);

CREATE INDEX team_user_user ON team_user (user_id);

CREATE INDEX teams_owner ON teams (owner_id);

CREATE INDEX sessions_room_start ON game_sessions (room_id, starts_at);

CREATE INDEX sessions_master_start ON game_sessions (game_master_id, starts_at);

CREATE INDEX sessions_experience_status_start ON game_sessions (experience_id, status, starts_at);

CREATE INDEX reservations_user_status ON reservations (user_id, status);

CREATE INDEX reservations_session ON reservations (game_session_id);

CREATE INDEX reservations_team ON reservations (team_id);

CREATE INDEX reservations_status_expiry ON reservations (status, expires_at);

CREATE INDEX hint_usage_hint ON game_session_hint (hint_id);

CREATE INDEX checks_session ON preparation_checks (game_session_id);

CREATE INDEX incidents_room_severity_status ON incidents (room_id, severity, status);

CREATE INDEX incidents_session ON incidents (game_session_id);

CREATE INDEX incidents_assignee ON incidents (assigned_to);

CREATE INDEX actions_incident_time ON maintenance_actions (incident_id, performed_at);

CREATE INDEX actions_technician ON maintenance_actions (technician_id);

CREATE UNIQUE INDEX reservations_one_active_per_session ON reservations(game_session_id) WHERE status IN ('pending','confirmed');

CREATE TRIGGER sessions_no_overlap_insert BEFORE INSERT ON game_sessions
WHEN NEW.status <> 'cancelled' AND EXISTS (
    SELECT 1 FROM game_sessions s WHERE s.id <> COALESCE(NEW.id, -1) AND s.status <> 'cancelled'
    AND (s.room_id = NEW.room_id OR s.game_master_id = NEW.game_master_id)
    AND datetime(NEW.starts_at, '-' || COALESCE(NEW.preparation_minutes, 0) || ' minutes') < s.ends_at
    AND datetime(s.starts_at, '-' || COALESCE(s.preparation_minutes, 0) || ' minutes') < NEW.ends_at
) BEGIN SELECT RAISE(ABORT, 'Session overlaps room or game master'); END;

CREATE TRIGGER reservation_capacity_insert BEFORE INSERT ON reservations
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN experiences e ON e.id = s.experience_id JOIN rooms r ON r.id = s.room_id
    WHERE s.id = NEW.game_session_id AND NEW.players BETWEEN e.min_players AND e.max_players AND NEW.players <= r.capacity
) BEGIN SELECT RAISE(ABORT, 'Reservation players outside capacity'); END;

CREATE TRIGGER hints_same_experience_insert BEFORE INSERT ON game_session_hint
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN hints h ON h.experience_id = s.experience_id
    WHERE s.id = NEW.game_session_id AND h.id = NEW.hint_id
) BEGIN SELECT RAISE(ABORT, 'Hint belongs to another experience'); END;

CREATE TRIGGER rate_capacity_insert BEFORE INSERT ON rates
WHEN NOT EXISTS (
    SELECT 1 FROM experiences e WHERE e.id = NEW.experience_id AND NEW.players BETWEEN e.min_players AND e.max_players
) BEGIN SELECT RAISE(ABORT, 'Rate players outside experience limits'); END;

CREATE TRIGGER sessions_no_overlap_update BEFORE UPDATE ON game_sessions
WHEN NEW.status <> 'cancelled' AND EXISTS (
    SELECT 1 FROM game_sessions s WHERE s.id <> COALESCE(NEW.id, -1) AND s.status <> 'cancelled'
    AND (s.room_id = NEW.room_id OR s.game_master_id = NEW.game_master_id)
    AND datetime(NEW.starts_at, '-' || COALESCE(NEW.preparation_minutes, 0) || ' minutes') < s.ends_at
    AND datetime(s.starts_at, '-' || COALESCE(s.preparation_minutes, 0) || ' minutes') < NEW.ends_at
) BEGIN SELECT RAISE(ABORT, 'Session overlaps room or game master'); END;

CREATE TRIGGER reservation_capacity_update BEFORE UPDATE ON reservations
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN experiences e ON e.id = s.experience_id JOIN rooms r ON r.id = s.room_id
    WHERE s.id = NEW.game_session_id AND NEW.players BETWEEN e.min_players AND e.max_players AND NEW.players <= r.capacity
) BEGIN SELECT RAISE(ABORT, 'Reservation players outside capacity'); END;

CREATE TRIGGER hints_same_experience_update BEFORE UPDATE ON game_session_hint
WHEN NOT EXISTS (
    SELECT 1 FROM game_sessions s JOIN hints h ON h.experience_id = s.experience_id
    WHERE s.id = NEW.game_session_id AND h.id = NEW.hint_id
) BEGIN SELECT RAISE(ABORT, 'Hint belongs to another experience'); END;

CREATE TRIGGER rate_capacity_update BEFORE UPDATE ON rates
WHEN NOT EXISTS (
    SELECT 1 FROM experiences e WHERE e.id = NEW.experience_id AND NEW.players BETWEEN e.min_players AND e.max_players
) BEGIN SELECT RAISE(ABORT, 'Rate players outside experience limits'); END;
SQL);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_actions');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('game_results');
        Schema::dropIfExists('preparation_checks');
        Schema::dropIfExists('game_session_hint');
        Schema::dropIfExists('hints');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('rates');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('experiences');
    }
};
