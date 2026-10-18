# Agenda, partidas y mantenimiento

El detalle de columnas está en el [diccionario](../diccionario-datos.md). Los nodos externos al bloque permiten ver los vínculos con otras áreas.

```mermaid
erDiagram
    users o|--o{ availability_blocks : released_by
    users o|--o{ availability_blocks : created_by
    incidents o|--o{ availability_blocks : incident_id
    users o|--o{ availability_blocks : user_id
    rooms o|--o{ availability_blocks : room_id
    reservations o|--o{ game_results : reservation_id
    users ||--o{ game_results : recorded_by
    game_sessions ||--o| game_results : game_session_id
    hints ||--o{ game_session_hint : hint_id
    game_sessions ||--o{ game_session_hint : game_session_id
    preparation_templates o|--o{ game_sessions : preparation_template_id_experience_id
    booking_policy_versions o|--o{ game_sessions : booking_policy_version_id_experience_id
    room_experience ||--o{ game_sessions : room_experience_id_room_id_experience_id
    users ||--o{ game_sessions : game_master_id
    rooms ||--o{ game_sessions : room_id
    experiences ||--o{ game_sessions : experience_id
    preparation_templates o|--o{ game_sessions : preparation_template_id
    booking_policy_versions o|--o{ game_sessions : booking_policy_version_id
    room_experience ||--o{ game_sessions : room_experience_id
    experiences ||--o{ hints : experience_id
    game_sessions o|--o{ incidents : game_session_id_room_id
    users o|--o{ incidents : assigned_to
    users ||--o{ incidents : reported_by
    game_sessions o|--o{ incidents : game_session_id
    rooms ||--o{ incidents : room_id
    users ||--o{ maintenance_actions : technician_id
    incidents ||--o{ maintenance_actions : incident_id
    preparation_template_items o|--o{ preparation_checks : preparation_template_item_id
    users o|--o{ preparation_checks : checked_by
    game_sessions ||--o{ preparation_checks : game_session_id
    preparation_templates ||--o{ preparation_template_items : preparation_template_id
    experiences ||--o{ preparation_templates : experience_id
    users o|--o{ reservation_incident : reviewed_by
    incidents ||--o{ reservation_incident : incident_id
    reservations ||--o{ reservation_incident : reservation_id

    availability_blocks {
        integer id PK
        integer room_id FK
        integer user_id FK
        integer incident_id FK
        integer created_by FK
        integer released_by FK
        datetime starts_at
        datetime ends_at
        string kind
    }

    booking_policy_versions {
        integer id PK
        integer experience_id FK
        string name
        string status
    }

    experiences {
        integer id PK
        string name
    }

    game_results {
        integer id PK
        integer game_session_id FK
        integer recorded_by FK
        integer reservation_id FK
    }

    game_session_hint {
        integer game_session_id PK,FK
        integer hint_id PK,FK
        datetime used_at
        integer penalty_seconds
    }

    game_sessions {
        integer id PK
        integer room_experience_id FK
        integer booking_policy_version_id FK
        integer preparation_template_id FK
        string locale
        integer experience_id FK
        integer room_id FK
        integer game_master_id FK
        datetime starts_at
        datetime ends_at
        string status
    }

    hints {
        integer id PK
        integer experience_id FK
        string code
    }

    incidents {
        integer id PK
        integer room_id FK
        integer game_session_id FK
        integer reported_by FK
        integer assigned_to FK
        string status
    }

    maintenance_actions {
        integer id PK
        integer incident_id FK
        integer technician_id FK
    }

    preparation_checks {
        integer id PK
        integer game_session_id FK
        integer checked_by FK
        integer preparation_template_item_id FK
    }

    preparation_template_items {
        integer id PK
        integer preparation_template_id FK
    }

    preparation_templates {
        integer id PK
        integer experience_id FK
        string name
    }

    reservation_incident {
        integer id PK
        integer reservation_id FK
        integer incident_id FK
        integer reviewed_by FK
        string status
    }

    reservations {
        integer id PK
        integer user_id FK
        integer game_session_id FK
        integer team_id FK
        integer players
        integer total_cents
        string status
        integer rate_id FK
        integer booking_policy_version_id FK
        integer booked_by FK
        string locale
        integer checked_in_by FK
        integer cancelled_by FK
    }

    room_experience {
        integer id PK
        integer room_id FK
        integer experience_id FK
        datetime installed_from
        datetime installed_until
    }

    rooms {
        integer id PK
        integer experience_id FK
        string name
        string status
        integer location_id FK
    }

    users {
        integer id PK
        string name
    }
```
