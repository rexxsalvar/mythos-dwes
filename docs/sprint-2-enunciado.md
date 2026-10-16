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

## Refinamiento respecto al sprint 1

Este enunciado prevalece sobre la previsión inicial de tres modelos en la API.
Se preparan al menos siete modelos para la futura API, sin interpretar que se exijan siete CRUD completos.
Se mantienen los dos CRUD completos previstos, Experience y Reservation, y los demás mínimos del calendario.
También se exigen expresamente pruebas iniciales y manejo del pivote desde código.
La propuesta PDF del sprint 1 se conserva como documento histórico; el refinamiento se registra aquí y en la documentación actual.

La implementación de los endpoints sigue prevista para el sprint 6. Preparar modelos no equivale a implementar la API ni a completar las historias de los sprints posteriores.
