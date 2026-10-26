# Sprint 3: acceso, rutas y validación

Fuente: enunciado del profesor facilitado por el alumno el 26/10/2026. Periodo: 19/10/2026 a 01/11/2026, cierre a las 23:59.

## Objetivo

Disponer de un esqueleto web seguro y coherente sobre el que añadir funcionalidades.

## Trabajo previsto

- Implantar autenticación y separar las zonas pública y privada.
- Ordenar y nombrar las rutas por áreas, recursos y middleware.
- Implementar los cinco roles o permisos con reglas reales.
- Implementar dos de las cinco policies previstas con contenido efectivo.
- Aplicar FormRequest a todos los formularios de más de dos campos que ya existan.
- Crear y reutilizar componentes de vista para input, label, select, fecha y checkbox.
- Añadir pruebas de autenticación, rutas, permisos y validación.

## Comprobación al cerrar

Un usuario puede autenticarse, acceder solo a su zona y completar un flujo validado con componentes reutilizables.

## Ampliación posible

A4: regla de validación personalizada aplicada a una regla real del dominio. Es opcional y no se incluye en esta implementación.

## Ajuste de planificación

Este enunciado adelanta a S3 la segunda policy (GameSessionPolicy), los cinco perfiles con permisos efectivos y la validación/componentes de los formularios existentes. T-08 conserva en S4 la aplicación a los futuros formularios CRUD. No se adelantan los cinco CRUD completos, Livewire, API, automatizaciones o informes.
