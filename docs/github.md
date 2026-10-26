# Publicación en GitHub

Revisión documental: 26/10/2026. Nombre definitivo: **Mythos - Sistema de reservas y gestión de escape rooms**. EscapeHub es el material de origen; no se crea otro repositorio ni otro tablero. El nombre de la ficha externa del centro queda pendiente de comprobar con el alumno.

## Enlaces y configuración

- Repositorio existente: [rexxsalvar/mythos-dwes](https://github.com/rexxsalvar/mythos-dwes), propietario rexxsalvar y rama main. Visibilidad pública comprobada por el complemento GitHub el 20/10/2026.
- Tablero: [Mythos - Proyecto DWES](https://github.com/users/rexxsalvar/projects/1). Acceso público comprobado en la revisión anterior del 06/10; la consulta web automatizada del 20/10 no permite revalidar su visibilidad.
- Tabla: [Product Backlog](https://github.com/users/rexxsalvar/projects/1/views/2).
- Criterios vigentes: [backlog](backlog.md), [revisión de S1](revision-sprint-1.md) y [calendario de diez sprints](calendario.md).

Se mantienen 16 historias y 12 tareas. Campos: Prioridad P1/P2/P3, Sprint previsto 1 a 10/Final y Status. Estados: Backlog, Por hacer, En curso, En revisión, Bloqueado y Hecho. Los identificadores HU/T se conservan.

## Revisión de las tarjetas

Las correcciones de S1 refinan los cuerpos y criterios de las tarjetas existentes. Se sustituyen los mínimos antiguos por siete recursos API y cinco CRUD, cinco policies, siete scopes, siete clases Livewire, siete comandos, cinco pares evento-listener, cinco jobs con tres en cola, cinco correos y tres informes PDF complejos. ES/EN y cobertura medida del 85 % siguen obligatorios.

T-01 a T-06 conservan su evidencia documental y T-07 la implementación publicada el 18/10 (52f2fe7). Las funciones posteriores continúan pendientes; los criterios no se marcan completados por estar descritos. Los cambios de planificación no cuentan como una corrección docente de S2.

El commit local y su publicación se distinguen comprobando main remoto. No se reescribe el historial ni se duplican tarjetas. El registro o ficha del centro y la entrega en Moodle no se dan por actualizados sin confirmación externa.

## Implementación del sprint 3 (26/10/2026)

HU-01 se completa con el enunciado actualizado: cinco roles con permisos, dos policies de consulta, autenticación, perfil y componentes/FormRequest. La evidencia está en [sprint-3-verificacion.md](sprint-3-verificacion.md). T-08 y HU-08 siguen abiertas con las acciones futuras identificadas. Se mantienen las 28 tarjetas; el cierre de HU-01 no cierra cinco CRUD ni la API.

Las 76 pruebas (348 assertions), Pint y compilación de recursos se ejecutan en local y en el workflow actualizado. El resultado remoto y el commit publicado se enlazan en la tarjeta de HU-01; no se da por exitoso un workflow por haberlo configurado.
