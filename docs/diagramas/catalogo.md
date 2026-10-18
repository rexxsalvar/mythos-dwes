# Catálogo, locales y condiciones

El detalle de columnas está en el [diccionario](../diccionario-datos.md). Los nodos externos al bloque permiten ver los vínculos con otras áreas.

```mermaid
erDiagram
    experiences ||--o{ booking_policy_versions : experience_id
    experiences ||--o{ experience_languages : experience_id
    experiences ||--o{ experience_translations : experience_id
    experiences ||--o{ rate_plans : experience_id
    rate_plans ||--o{ rates : rate_plan_id_experience_id_currency
    experiences ||--o{ rates : experience_id
    rate_plans ||--o{ rates : rate_plan_id
    experiences ||--o{ room_experience : experience_id
    rooms ||--o{ room_experience : room_id
    locations o|--o{ rooms : location_id
    experiences ||--o{ rooms : experience_id

    booking_policy_versions {
        integer id PK
        integer experience_id FK
        string name
        string status
    }

    experience_languages {
        integer id PK
        integer experience_id FK
        string locale
    }

    experience_translations {
        integer id PK
        integer experience_id FK
        string locale
    }

    experiences {
        integer id PK
        string name
    }

    locations {
        integer id PK
        string name
        string code
    }

    rate_plans {
        integer id PK
        integer experience_id FK
        string code
        string name
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
```
