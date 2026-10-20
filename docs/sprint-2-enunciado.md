# Sprint 2: enunciado y criterios de entrega

Fuente: enunciado del profesor facilitado por el alumno el 16/10/2026.
Periodo oficial: 05/10/2026 a 18/10/2026, cierre a las 23:59.

## Objetivo

Construir una base de datos capaz de sostener todo el proyecto y evitar cambios estructurales tardíos.

## Trabajo previsto

- Cerrar el diagrama entidad relación y justificar las entidades principales.
- Crear migraciones, claves, restricciones e índices.
- Preparar al menos los siete modelos que utilizará la API.
- Crear factories realistas y seeders reproducibles.
- Implementar y probar relaciones 1 a N y N a N.
- Implementar una relación N a N con columnas adicionales en la tabla pivote y manejarlas desde el código.
- Añadir pruebas iniciales de modelos, relaciones y datos de prueba.

## Comprobación al cerrar

La base de datos puede reconstruirse desde cero, poblarse y demostrar desde código todas las relaciones exigidas.

## Ampliación posible

A5: borrado lógico, porque debe incorporarse al modelo y a las consultas desde el principio. Es opcional y no se considera un mínimo del sprint.

## Nota de planificación del 16/10/2026

Este enunciado prevalece sobre la previsión inicial de tres modelos en la API.
Se preparan al menos siete modelos para la futura API, sin interpretar que se exijan siete CRUD completos.
En aquella previsión se mantenían dos CRUD completos, Experience y Reservation, y los demás mínimos del calendario.
También se exigen expresamente pruebas iniciales y manejo del pivote desde código.
La propuesta original y este refinamiento quedan conservados en el historial de Git.

La implementación de los endpoints sigue prevista para el sprint 6. Preparar modelos no equivale a implementar la API ni a completar las historias de los sprints posteriores.

## Actualización de alcance del 20/10/2026

El alumno comunica [correcciones del profesor al sprint 1](revision-sprint-1.md): la futura API pasa a cinco CRUD y sus siete recursos son User, Experience, Room, GameSession, Reservation, Rate e Incident. Es una modificación de la planificación de S1; no modifica el texto docente de S2 anterior ni se presenta como una corrección recibida de su entrega. La propuesta PDF se publica revisada y las versiones anteriores permanecen en Git.
