# Mythos - Proyecto Web DWES

Aplicación de reservas y gestión para el negocio de escape rooms Mythos, en Murcia. Proyecto individual del grado de Desarrollo de Aplicaciones Web.

## Sprint 1 · 21/09/2026 - 04/10/2026

Esta entrega contiene la propuesta, el Product Backlog y la configuración del tablero. El desarrollo de Laravel comenzará en los siguientes sprints.

| Entregable | Archivo |
|---|---|
| Propuesta formal (4 páginas) | [PDF](output/pdf/Mythos_Desarrollo_de_idea_Sprint_1.pdf) |
| Historias y tareas priorizadas | [Product Backlog](docs/backlog.md) |
| Tablero, estados y objetivo de sprint | [Tablero Scrum](docs/tablero.md) |
| Mínimos docentes y aplicación a Mythos | [Requisitos](docs/requisitos.md) |
| Fechas y objetivos de los sprints | [Calendario](docs/calendario.md) |
| Publicación pendiente en GitHub | [Configuración de GitHub](docs/github.md) |

## Alcance

Catálogo, reservas, sesiones, preparación de partidas, equipos, resultados e incidencias. Se utilizará Expedición Maldita como referencia real: 75 minutos y de 2 a 6 jugadores. Los plazos de cancelación y preparación se concretarán durante el análisis.

Tecnologías previstas: Laravel, Blade, Livewire, Laravel-permission, Sanctum, laravel-dompdf y Pest. Idiomas: español e inglés. Las versiones y la base de datos se decidirán al preparar la aplicación.

El mínimo docente de dos roles se cubrirá con cliente y administrador. La propuesta contempla además recepción, game master y mantenimiento. Los mínimos técnicos y las ampliaciones se distinguen en [requisitos](docs/requisitos.md).

## Estado y trabajo pendiente

- Propuesta, backlog, calendario y tablero local preparados.
- Repositorio Git local, rama `main`.
- Pendiente: publicar el repositorio privado y crear GitHub Projects. El acceso del navegador a GitHub fue denegado; no se ha creado ningún recurso remoto.
- Entrega oficial: **12/02/2027**. La subida a la plataforma docente y la defensa corresponden al alumno.

## Reproducir el PDF

El generador está en `scripts/build_proposal.py`. Requiere Python, ReportLab y las fuentes Segoe UI de Windows; no forma parte del código Laravel.

```powershell
python -m pip install -r scripts/requirements-docs.txt
python scripts/build_proposal.py
```

Los archivos temporales quedan excluidos del repositorio. El PDF generado sí se versiona como entregable.

