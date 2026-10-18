# Reservas, equipos, cobros y ampliaciones

El detalle de columnas está en el [diccionario](../diccionario-datos.md). Los nodos externos al bloque permiten ver los vínculos con otras áreas.

```mermaid
erDiagram
    users ||--o{ gift_voucher_redemptions : redeemed_by
    reservations ||--o{ gift_voucher_redemptions : reservation_id
    gift_vouchers ||--o| gift_voucher_redemptions : gift_voucher_id
    users o|--o{ gift_vouchers : issued_by
    users o|--o{ gift_vouchers : purchased_by
    experiences o|--o{ gift_vouchers : experience_id
    users ||--o{ payment_transactions : recorded_by
    payment_transactions o|--o{ payment_transactions : parent_id
    gift_vouchers o|--o{ payment_transactions : gift_voucher_id
    reservations o|--o{ payment_transactions : reservation_id
    users o|--o{ reservation_events : actor_id
    reservations ||--o{ reservation_events : reservation_id
    users o|--o{ reservation_participants : user_id
    reservations ||--o{ reservation_participants : reservation_id
    users o|--o{ reservations : cancelled_by
    users o|--o{ reservations : checked_in_by
    users o|--o{ reservations : booked_by
    booking_policy_versions o|--o{ reservations : booking_policy_version_id
    rates o|--o{ reservations : rate_id
    teams o|--o{ reservations : team_id
    game_sessions ||--o{ reservations : game_session_id
    users ||--o{ reservations : user_id
    users o|--o{ reviews : moderated_by
    users ||--o{ reviews : user_id
    reservations ||--o| reviews : reservation_id
    users ||--o{ team_user : user_id
    teams ||--o{ team_user : team_id
    users ||--o{ teams : owner_id

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

    gift_voucher_redemptions {
        integer id PK
        integer gift_voucher_id FK
        integer reservation_id FK
        integer redeemed_by FK
    }

    gift_vouchers {
        integer id PK
        integer experience_id FK
        integer purchased_by FK
        integer issued_by FK
        string kind
        integer amount_cents
        integer players
    }

    payment_transactions {
        integer id PK
        integer reservation_id FK
        integer gift_voucher_id FK
        integer parent_id FK
        integer recorded_by FK
        string kind
        integer amount_cents
        string status
    }

    rates {
        integer id PK
        integer rate_plan_id FK
        integer experience_id FK
        integer players
        integer total_cents
        string currency FK
    }

    reservation_events {
        integer id PK
        integer reservation_id FK
        integer actor_id FK
    }

    reservation_participants {
        integer id PK
        integer reservation_id FK
        integer user_id FK
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

    reviews {
        integer id PK
        integer reservation_id FK
        integer user_id FK
        string status
        integer moderated_by FK
    }

    team_user {
        integer team_id PK,FK
        integer user_id PK,FK
        string role
        datetime joined_at
    }

    teams {
        integer id PK
        integer owner_id FK
        string name
    }

    users {
        integer id PK
        string name
    }
```
