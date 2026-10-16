# Mythos - Proyecto Web DWES

Aplicación de reservas y gestión para el negocio de escape rooms Mythos, en Murcia. Proyecto individual del grado de Desarrollo de Aplicaciones Web.

## Estado · 16/10/2026

Sprint 1 completado: propuesta, Product Backlog y tablero. Sprint 2: base Laravel 13 con SQLite, modelo de datos, migraciones, factories, seeders y pruebas iniciales. Las pantallas de negocio, autenticación, roles y API siguen previstas para sus sprints.

| Entregable | Archivo |
|---|---|
| Propuesta formal (4 páginas) | [PDF](output/pdf/Mythos_Desarrollo_de_idea_Sprint_1.pdf) |
| Historias y tareas priorizadas | [Product Backlog](docs/backlog.md) |
| Tablero, estados y objetivo de sprint | [Tablero Scrum](docs/tablero.md) |
| Mínimos docentes y aplicación a Mythos | [Requisitos](docs/requisitos.md) |
| Fechas y objetivos de los sprints | [Calendario](docs/calendario.md) |
| Repositorio y Projects | [Configuración de GitHub](docs/github.md) |
| Enunciado actualizado del profesor | [Sprint 2](docs/sprint-2-enunciado.md) |
| Diagrama E-R, entidades, índices y siete modelos API | [Modelo de datos](docs/modelo-datos.md) |
| Comprobaciones y demostración de la entrega | [Evidencia del sprint 2](docs/sprint-2-verificacion.md) |

## Ejecutar y comprobar el sprint 2

Entorno elegido con el alumno: PHP 8.4, Laravel 13, SQLite y Pest 4. Composer y Node/npm disponibles mediante el entorno local. Las versiones resueltas están fijadas en `composer.lock` y `package-lock.json`.

En una copia recién clonada:

```powershell
composer run setup
php artisan db:seed
composer test
php scripts/demo_relationships.php
```

`setup` instala dependencias, prepara `.env`, genera la clave local, crea el archivo SQLite si falta, migra y compila los recursos. El seeder es solo para desarrollo/pruebas: contiene personas y salas demo, no clientes reales. No se versionan `.env`, la base SQLite, dependencias ni temporales.

Para iniciar el servidor local: `php artisan serve`. La página inicial es todavía la del esqueleto Laravel; el entregable de este sprint es el modelo de datos, no una interfaz funcional de reservas.

La demostración muestra desde Eloquent tarifas, reservas de un usuario, miembros y atributos del pivote, pistas utilizadas, preparación, resultado y actuaciones de mantenimiento. Las pruebas verifican además INSERT/UPDATE de pivotes, restricciones, reversión y dos escritores independientes.

## Alcance

Catálogo, reservas, sesiones, preparación de partidas, equipos, resultados e incidencias. Se utilizará Expedición Maldita como referencia real: 75 minutos y de 2 a 6 jugadores. Los plazos de cancelación y preparación se concretarán durante el análisis.

Base instalada: Laravel 13, SQLite y Pest 4, con PHP 8.4. Tecnologías previstas para los siguientes sprints: Blade, Livewire, Laravel-permission, Sanctum y laravel-dompdf. Idiomas: español e inglés. La API pasa de tres a al menos siete modelos por el enunciado docente actualizado; los endpoints siguen previstos para S6.

El mínimo docente de dos roles se cubrirá con cliente y administrador. La propuesta contempla además recepción, game master y mantenimiento. Los mínimos técnicos y las ampliaciones se distinguen en [requisitos](docs/requisitos.md).

## Estado y trabajo pendiente

- Propuesta, backlog, calendario y tablero local preparados.
- Repositorio público: [rexxsalvar/mythos-dwes](https://github.com/rexxsalvar/mythos-dwes), rama `main`.
- Tablero público vinculado al repositorio: [Mythos - Proyecto DWES](https://github.com/users/rexxsalvar/projects/1).
- Vista de tabla: [Product Backlog](https://github.com/users/rexxsalvar/projects/1/views/2), con campos Prioridad y Sprint previsto.
- T-01 a T-06 completadas. T-07 tiene implementación y evidencia en esta entrega; el resto del desarrollo permanece planificado.
- Entrega oficial: **12/02/2027**. La subida a la plataforma docente y la defensa corresponden al alumno.

## Reproducir el PDF

El generador está en `scripts/build_proposal.py`. Requiere Python, ReportLab y las fuentes Segoe UI de Windows; no forma parte del código Laravel.

```powershell
python -m pip install -r scripts/requirements-docs.txt
python scripts/build_proposal.py
```

Los archivos temporales quedan excluidos del repositorio. El PDF generado sí se versiona como entregable.
