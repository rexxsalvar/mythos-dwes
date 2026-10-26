# Evidencia del sprint 3 - Acceso, rutas y validación

Implementación: 26/10/2026. Periodo docente: 19/10 a 01/11/2026. Referencia: [enunciado recibido](sprint-3-enunciado.md). HU-01 cubre el acceso; T-08 adelanta los componentes y la validación de los formularios existentes, manteniendo pendiente su integración con futuros CRUD.

## Correspondencia con el enunciado

| Criterio | Implementación |
|---|---|
| Autenticación web | Registro de clientes, inicio/cierre de sesión, recordar sesión, renovación de identificador y limitación de intentos. Controladores en app/Http/Controllers/Auth. |
| Zonas y rutas nombradas | 18 rutas propias en routes/web.php: pública, autenticación, perfil, cliente, personal, mantenimiento y administración. Middleware guest/auth, can y permission. |
| Cinco perfiles con reglas reales | AccessRolesSeeder define cinco roles y ocho permisos. Las consultas filtran propiedad/asignación y las rutas comprueban permisos en servidor. |
| Dos policies efectivas | ReservationPolicy y GameSessionPolicy: viewAny/view sobre reservas propias o partidas asignadas y consulta global para personal autorizado. |
| FormRequest en todos los formularios existentes de más de dos campos | RegisterRequest, LoginRequest, UpdateProfileRequest y AgendaFilterRequest. |
| Componentes reutilizables | components/input, label, select, date y checkbox; layout y badge compartidos. Se usan en registro, acceso, perfil y filtros de agenda. |
| Pruebas de acceso y validación | AuthenticationTest, AccessPermissionsTest y WebValidationTest: 33 casos nuevos; se conservan los 43 casos de S2. |
| Flujo completo | Registro como cliente, acceso, edición validada del perfil, consulta propia y salida; rutas ajenas denegadas. |

## Matriz de acceso implementada

| Perfil | Permisos de consulta |
|---|---|
| Cliente | Perfil propio y reservas cuyo user_id coincide con su cuenta. |
| Recepción | Perfil propio, reservas de clientes y agenda de todas las sesiones. |
| Game master | Perfil propio y sesiones cuyo game_master_id coincide con su cuenta. |
| Mantenimiento | Perfil propio e incidencias cuyo assigned_to coincide con su cuenta. |
| Administrador | Perfil propio, consultas anteriores y resumen administrativo. |

El registro público asigna siempre cliente. Los campos role, roles, permissions, is_active e identificadores enviados por el navegador no conceden privilegios. La edición del perfil solo permite name, email, phone y preferred_locale; cambiar el correo elimina su marca previa de verificación. El formulario no cambia contraseñas ni roles.

Las policies y el middleware comprueban el servidor, además de adaptar los enlaces visibles. Una cuenta desactivada no inicia sesión y, si ya estaba dentro, se cierra su sesión al siguiente acceso. No hay un bypass global de administrador que ignore las policies.

## Formularios y componentes

| Formulario | Campos y validaciones principales | Componentes |
|---|---|---|
| Registro | Nombre, correo único normalizado, teléfono opcional, contraseña y confirmación. Al menos ocho caracteres con letras y números. | label/input |
| Acceso | Correo, contraseña y recordar sesión; cuenta activa y máximo cinco intentos fallidos por correo/IP en un minuto. | label/input/checkbox |
| Perfil | Nombre, correo único, teléfono opcional e idioma preferido es/en; identidad del usuario autenticado. | label/input/select |
| Agenda | Desde/hasta como fechas válidas y ordenadas, sala existente; filtro sobre las sesiones que el usuario puede ver. | label/date/select; date reutiliza input |

Los errores se muestran junto al campo y se vinculan mediante aria-invalid/aria-describedby. Se conserva la entrada válida al corregir errores, escapada mediante Blade; los componentes de contraseña nunca repintan su valor. Todos los formularios de escritura incluyen CSRF; salir utiliza POST. La agenda consulta días de Europe/Madrid sobre timestamps UTC y permite filtrar con una sola fecha.

La interfaz actual está en español. Guardar preferred_locale o consultar el idioma de una partida no implementa todavía la interfaz bilingüe completa de S8.

## Demostración

1. Abrir la página inicial y crear una cuenta de cliente con un correo ficticio no utilizado.
2. Entrar en Mi perfil; enviar un teléfono no válido y comprobar que el error aparece junto al campo y que los demás datos se conservan. Corregir y guardar.
3. Consultar Mis reservas. Una cuenta nueva no tiene reservas; cliente@example.test permite revisar las cinco reservas demo existentes.
4. Intentar acceder como cliente a /administracion o /personal/sesiones: se deniega el acceso. Un cliente tampoco puede consultar por URL la reserva de otro titular.
5. Entrar con los perfiles del personal y comprobar su agenda o incidencias asignadas. Un game master no puede consultar una partida ajena y un técnico recibe 404 para una incidencia no accesible.
6. Cerrar sesión; una ruta privada redirige a acceso. Una petición JSON anónima recibe 401 y un autenticado sin permiso, 403.

Las cuentas del seeder son ficticias: cliente@example.test, miembro@example.test, recepcion@example.test, master@example.test, tecnico@example.test y admin@example.test. Contraseña demo: solo-pruebas-mythos. DatabaseSeeder carga los permisos antes de asignar esos perfiles. Para una base local existente basta ejecutar `php artisan db:seed --class=AccessRolesSeeder` para aplicar los permisos sin volver a poblar las tablas del negocio.

## Comprobaciones realizadas

- Pest: **76 pruebas y 348 assertions**, con los casos iniciales y los 33 nuevos. Incluyen matriz de los cinco roles, políticas por registro, inyección de privilegios, cuentas desactivadas, límite de intentos, perfil, fechas y datos ajenos.
- La prueba inicial de seeders sigue comprobando carga completa repetida, integridad y ausencia de duplicados. AccessRolesSeeder limpia expresamente la caché de permisos cuando DatabaseSeeder suprime los eventos de modelos.
- `npm run build`: recursos compilados correctamente. Pint comprueba el formato PHP.
- Navegador Chrome: acceso de los cinco perfiles demo, consultas de recepción/game master/mantenimiento/administración y error de teléfono en el perfil. Pantallas revisadas visualmente.
- Petición real POST sin token a registro: HTTP **419**, sin crear una cuenta.
- Copia SQLite previa a aplicar los permisos; hashes de los registros existentes de usuarios y nueve tablas de negocio iguales antes y después. Cinco roles, ocho permisos y cero errores de claves ajenas.
- Sin nuevas migraciones, cambios de estructura o borrado de datos. Las pruebas usan SQLite temporal/en memoria, no la base local del negocio.

La cobertura global del 85 % se medirá en S9; estas cifras no se presentan como ese porcentaje. La comprobación remota se publica en GitHub Actions junto al commit de HU-01.

## Alcance que sigue pendiente

No se implementan creación/cancelación de reservas, edición de sesiones, actuaciones de mantenimiento, cinco CRUD completos, Livewire, API, correos, informes o ampliación A4. Las páginas de reservas/sesiones/incidencias son consultas protegidas para demostrar las dos policies y los cinco perfiles. El flujo editable de este sprint es el perfil propio.

Los 15 minutos de pendiente y 30 de preparación siguen como decisiones aprobadas para el flujo de reservas posterior; no se activan por este trabajo de acceso. Los ajustes de esquema para los informes de mantenimiento y diario conservan su planificación.

## Referencias técnicas

[Autenticación Laravel 13](https://laravel.com/docs/13.x/authentication), [autorización Laravel 13](https://laravel.com/docs/13.x/authorization) y el código instalado de Laravel-permission 8. Se utilizan los paquetes existentes; no se añade un framework de interfaz para cubrir este sprint.
