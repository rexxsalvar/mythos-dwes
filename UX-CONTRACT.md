# UX Contract

## Contexto y fuentes

Presentación de las funciones existentes en S3. Interfaz en español; horas mostradas en Europe/Madrid. Objetivo WCAG 2.2 AA. Tema oscuro único.

| Ámbito | Fuente mantenida |
|---|---|
| Alcance y cinco perfiles | docs/sprint-3-verificacion.md; docs/requisitos.md |
| Navegación y métodos HTTP | routes/web.php |
| Permisos y asignación | AccessRolesSeeder; ReservationPolicy; GameSessionPolicy; controladores existentes |
| Campos y validación | LoginRequest; RegisterRequest; UpdateProfileRequest; AgendaFilterRequest |
| Tokens y aspecto | DESIGN.md; resources/css/app.css |

Estas fuentes se consultan; el rediseño no las modifica. No hay nuevas decisiones de negocio.

## Canonical UI Map

| Capability | Canonical owner | Source of truth | Allowed variants | Verification |
|---|---|---|---|---|
| Select/Listbox | components/select.blade.php | FormRequest y opciones existentes | native, ventana del SO aceptada | Perfil y agenda, teclado y apertura |
| Date | components/date.blade.php y components/input.blade.php | AgendaFilterRequest | native, calendario del SO aceptado | Rango y teclado en agenda |
| Form | Componentes de campo; formularios Blade existentes | FormRequest y rutas existentes | registro, acceso, perfil, filtros | Pruebas existentes y validación en navegador |
| Scrollbar | resources/css/app.css | DESIGN.md | documento y tablas con overflow horizontal | Navegador estrecho y estilos globales |
| Toast | Mensaje de sesión en components/layout.blade.php | session('status') del servidor | aviso persistente role=status; sin toast flotante | Pruebas existentes y renderizado |
| CRUD | Rutas y formularios existentes, sin CRUD nuevos | docs/sprint-3-verificacion.md | registro y perfil; recursos solo de consulta | Pruebas existentes y enlaces de ida/vuelta |

No existen selección múltiple, búsqueda remota, operaciones destructivas, cargas de archivos ni sincronización en segundo plano. No se añaden para cumplir una plantilla de UX.

## Flujos conservados

| Operación | Resultado | Fallo y recuperación |
|---|---|---|
| Acceso y registro | Destino decidido por el controlador; sin elección pública de rol | Errores del servidor, old() y foco en primer campo inválido; contraseña sin repintar |
| Perfil | Misma petición PATCH y mensaje de sesión | Errores junto al campo; conservar entrada no sensible |
| Filtrar agenda | GET con from, until y room_id | Mismos errores; botón Limpiar a la ruta existente |
| Consultar listado/detalle | Enlaces y paginación del servidor | Estado vacío real, sin datos de relleno |
| Salir | POST con CSRF al controlador existente | Comportamiento del servidor, sin logout por GET |

No interceptar envíos con fetch ni sustituir validación del servidor. `data-submit` bloquea repeticiones y señala aria-busy mientras continúa el envío normal; pageshow recupera el botón al volver atrás. No se añaden confirmaciones de guardado ni guardado automático.

## Navegación, datos y estados

- Los enlaces se generan con las rutas existentes y las mismas comprobaciones @can/@auth. El rediseño no concede permisos ni expone registros ajenos.
- Cada pantalla conserva su título y marca la sección activa. El menú móvil es un disclosure en el flujo, sin modal, focus trap ni bloqueo de scroll; Escape cierra y devuelve el foco al botón.
- Sin JavaScript todos los enlaces permanecen disponibles. Mostrar contraseña es una mejora opcional con botón, aria-controls, aria-pressed y etiqueta variable; se enmascara al restaurar la página.
- Las tablas conservan columnas, orden, acciones y paginación del servidor. Los contenedores estrechos admiten desplazamiento por teclado. No se añade ordenación ni filtrado local.
- Fechas, monedas, precios históricos y participantes conservan las expresiones actuales. preferred_locale sigue siendo preferencia; no equivale a interfaz bilingüe.
- 403, 404 y 419 conservan significado y destinos. Una incidencia ajena continúa devolviendo el 404 deliberado del servidor. No se cambia esta protección para seguir un valor genérico de una skill.
- Estado, gravedad y error se indican con texto, no solo con color. Los avisos permanecen visibles y los errores conservan aria-invalid/aria-describedby.
- Los formularios crecen con su contenido; no se recortan para ajustar la altura de una tabla. No se añaden estados de carga ficticios a consultas síncronas.
- Revisar escritorio, móvil, teclado, select/calendario nativos, estados vacíos y movimiento reducido. La auditoría estática complementa las pruebas existentes y el navegador; no demuestra por sí sola los flujos.

## Secuencia de la portada

- Solo `home` contiene la entrada a la pirámide guiada por la cobra de la referencia del usuario. Es una ambientación de ficción; no representa una reserva, un estado de juego ni una función nueva.
- El documento es el propietario del scroll. La escena usa sticky y un canvas 2D decorativo, sin scroll lock ni autoplay. Solo en portada y con puntero preciso, la rueda vertical desplaza suavemente el documento con una constante de 160 ms. La selección del fotograma sigue directamente la posición real de la página, con una única imagen opaca, sin mezcla ni suavizado de la secuencia. Teclado, tacto, zoom, rueda horizontal, campos y contenedores con scroll propio conservan su comportamiento nativo. Cualquiera de esas interacciones cancela el ajuste pendiente; también se cancela al ocultar la pestaña o navegar. Movimiento reducido desactiva el suavizado.
- Los títulos, descripciones, enlaces de acceso y enlaces de registro pertenecen a Blade; siguen disponibles desde el inicio y fuera del canvas. La decoración completa queda fuera del árbol accesible y no recibe eventos del puntero.
- Se mantiene un cartel fijo mientras llegan los primeros fotogramas. Un fallo inicial, falta de soporte o ahorro de datos mantiene esa alternativa. Un fotograma posterior fallido conserva la imagen disponible más próxima; no bloquea la lectura ni los enlaces.
- Se descargan fotogramas próximos, con tres solicitudes simultáneas como máximo. Se conservan hasta 12 imágenes decodificadas en escritorio y 8 en móvil; las demás se liberan. Una franja comprimida anticipa hasta 24 posiciones en escritorio y 12 en móvil, según la dirección del desplazamiento; su caché se limita a 32 y 20 archivos respectivamente. No se precarga ni decodifica toda la secuencia al entrar. Cada variante tiene su encuadre y dimensiones; el móvil no estira el panorama de escritorio.
- Movimiento reducido evita solicitar la secuencia y elimina el recorrido largo. Cambiar esa preferencia o cruzar el ancho móvil reinicia la variante y libera sus imágenes anteriores.
- La cámara alcanza el interior antes del relato final y conserva ese encuadre mientras se lee. El escenario queda limitado al contenedor de portada y termina antes del pie de página.
- Verificación: exterior, aproximación, cruce, interior y retroceso del scroll en escritorio; lectura y accesos en móvil; cartel alternativo y ausencia de cambios en las rutas privadas.

## Continuación Dr. Zodiac

- Capítulo visual independiente después del contenedor completo de Expedición. Su controlador no sustituye ni modifica el renderizador del báculo, el suavizado de rueda, sus estilos, fotogramas o texto.
- La ficha de Dr. Zodiac permanece en Blade: historia, marca The Shadow, El Palmar, 2–6 participantes, 60 minutos y dificultad media. Fuentes de contenido: The Shadow y el catálogo de Mythos, consultadas el 02/11/2026. El enlace de salida se identifica como web oficial de The Shadow; no se añade una reserva propia ni una ruta nueva.
- Desplazamiento nativo del documento y un canvas 2D decorativo. Cada posición pinta una sola imagen completa, sin mezcla o desenfoque. El enlace «Ir a la ficha de la sala» permite omitir el recorrido con teclado o puntero y enfoca el artículo de destino. No se capturan teclas, gestos o rueda desde este controlador.
- El capítulo solo solicita su manifiesto y fotogramas al aproximarse al viewport. Máximo tres solicitudes simultáneas; hasta 10 imágenes decodificadas en escritorio y 6 en móvil, y 24/16 comprimidas. Se abortan las solicitudes y liberan los bitmaps al alejarse, salir de la página o cambiar de variante/preferencias. No se ejecuta un bucle de render permanente.
- El cartel reserva la superficie mientras carga. Si falta soporte, hay ahorro de datos, colores forzados o movimiento reducido, se ofrece la composición estática sin solicitar la secuencia y se elimina el recorrido largo. Un fallo inicial o un tiempo de carga inicial superior a 12 segundos activa esa alternativa; un fotograma posterior fallido conserva el disponible más próximo.
- La escena y el avance están fuera del árbol accesible. El título, relato, datos y enlaces se leen sin JavaScript. La preferencia de movimiento y el encuadre móvil pueden cambiar durante la visita. El pie de página permanece fuera de ambas escenas.

## Continuación El Virus

- Tercer capítulo visual, después de Zodiac y antes del pie de página. No modifica los dos capítulos aceptados, el smooth scroll de 160 ms ni el comportamiento de S1–S3. El documento sigue siendo el propietario del desplazamiento.
- `home/virus.blade.php` contiene la información accesible y los enlaces. «Ir a la ficha de la sala» es un enlace nativo al artículo con `tabindex="-1"`; permite saltar el recorrido y coloca el foco en el destino. «Conocer El Virus» lleva a Enigmatic y el texto contiguo identifica la web oficial. Fuente: https://enigmatic.es/el-juego/, consultada el 02/11/2026. No se crea una reserva propia ni se presenta la escena ficticia como fotografía del negocio.
- `experience-sequence.js` es el propietario compartido del comportamiento de los capítulos nuevos. La separación de los controladores anteriores responde a la petición explícita de conservarlos. Su selector `data-experience-sequence` no afecta a Expedición o Zodiac; no captura rueda, teclado ni gestos.
- Canvas decorativo con una sola imagen completa, sin mezclas o desenfoque. La cámara llega al laboratorio antes de terminar la ficha. Variante de escritorio de 144 imágenes y móvil de 72, con encuadre propio. La escena termina dentro de su sección; el pie queda fuera.
- Manifiesto y fotogramas solo al aproximarse al viewport. Máximo tres solicitudes simultáneas, caché de 10 imágenes decodificadas en escritorio y 6 en móvil, y 24/16 comprimidas. Una decodificación en curso puede ocupar temporalmente una imagen adicional. Se aborta y libera al alejarse, navegar o cambiar variante/preferencias. No hay bucle de render permanente.
- Cartel durante la carga. Movimiento reducido, ahorro de datos, colores forzados o soporte insuficiente evitan solicitar la secuencia y eliminan su recorrido largo. Fallo inicial o espera inicial superior a 12 segundos ofrecen la alternativa estática; un fallo posterior conserva la imagen disponible más próxima. Cambios tardíos de peticiones canceladas no alteran la nueva variante.
- Título, relato, datos y enlaces disponibles sin JavaScript. Decoración y avance fuera del árbol accesible. Verificar inicio, giro, compuerta, llegada y retroceso, acceso directo por teclado, lectura estrecha y recuperación ante errores.
- El frasco aprobado el 03/11/2026 sustituye únicamente la representación del objeto en los fotogramas y carteles. Mantiene el recorrido y el laboratorio; no se altera el controlador compartido ni el scroll. El frente conserva la imagen aprobada y el símbolo sin cruz roja. La revisión `bottle-photo-2` cambia las URLs del manifiesto, los fotogramas y los carteles para que la caché no mezcle imágenes antiguas con las nuevas.
- La revisión `door-photo-3` incorpora la compuerta aprobada. Las dos hojas se abren lateralmente con el scroll, siguiendo la posición del frasco. El marco y el piloto forman parte del decorado fijo.
- La revisión `optical-glass-4` incorpora el frasco con vidrio y líquido refractivos. Los reflejos se calculan durante el render de cada fotograma y la etiqueta usa la imagen aprobada.
