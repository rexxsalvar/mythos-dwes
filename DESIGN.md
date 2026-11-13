---
version: alpha
name: Mythos
description: Aventura de escape room en la portada; consultas y formularios claros en el área privada.
colors:
  background: "#0B0B10"
  surface: "#12121A"
  primary: "#F9BF00"
  text: "#FFFFFF"
  muted: "#C7C5D0"
  error: "#FFB4AB"
typography:
  ritual:
    fontFamily: "Aboreto, Georgia, serif"
  display:
    fontFamily: "Big Shoulders Display, Arial Narrow, Impact, sans-serif"
  body:
    fontFamily: "IBM Plex Sans, Segoe UI, sans-serif"
rounded:
  DEFAULT: "4px"
spacing:
  pageMax: "1280px"
  pageInset: "40px"
  mobileInset: "22px"
components:
  button:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.background}"
    rounded: "{rounded.DEFAULT}"
  input:
    backgroundColor: "{colors.background}"
    textColor: "{colors.text}"
  secondaryText:
    textColor: "{colors.muted}"
---

# Mythos

## Overview

Un viaje desde el exterior de una pirámide hasta una cámara interior. El báculo de cobra de la referencia aportada por el usuario guía el recorrido: pasa del primer plano a la entrada, se endereza y avanza al interior; después aparece el relato de equipo. La firma de la portada es cruzar ese umbral con el scroll. Los escenarios son una ambientación ilustrada de ficción, no fotografías de las instalaciones ni nuevas promesas comerciales. Las pantallas privadas prescinden de efectos ambientales para facilitar las consultas. Público de Murcia, interfaz actual en español y uso en móvil y escritorio. La preferencia de inglés no activa una traducción completa.

El encargo autoriza renovar la presentación de S1–S3, sin adelantar funciones. No usar círculos abstractos, neón, terror gratuito, métricas ficticias ni tarjetas decorativas repetidas.

**Propiedad de tokens: modelo B.** `resources/css/app.css` es la fuente canónica. Este documento refleja sus valores; no hay generación ni configuración adicional. Colores del sitio de referencia: background, surface, primary, text y muted. Bordes y tonos semánticos son adaptaciones de contraste para esta aplicación.

## Colors

| Rol | Variable CSS | Uso |
|---|---|---|
| background / surface | --paper / --surface | Fondo y superficies |
| primary | --accent | Acción principal, foco y marca |
| text / muted | --ink / --muted | Texto y explicación |
| Bordes #34323F / #716E7E | --line / --field-line | División y límites de campos |
| Error / éxito #9ED5B1 / información #B9CDF6 | --danger / --success / --info | Mensajes y estados con texto |

Tema oscuro único. El amarillo lleva texto oscuro. Los estados mantienen su nombre y un indicador además del color. Contraste de texto objetivo AA; el modo de colores forzados conserva controles y desplazamiento nativos.

## Typography

Aboreto para los títulos de la portada y la placa de acceso, mediante `--font-ritual`; mayúsculas abiertas, de aspecto escultórico, con tamaños contenidos para que respire la escena. Es una elección artística de inscripción, no una reproducción de escritura egipcia. Big Shoulders Display se mantiene en títulos y cifras del área privada; IBM Plex Sans conserva la lectura, navegación y controles. Las tres fuentes son locales, con sustitutas y `font-display: swap`; Aboreto incluye su licencia OFL junto al archivo TTF. Los nombres, errores y textos largos pueden ocupar varias líneas; formularios en escritura normal.

## Layout

Contenido privado de hasta 1280 px con márgenes de 40 px; 22 px en móvil. Portada en tres momentos: presentación y accesos ante la pirámide, aproximación al umbral y relato de equipo dentro de la cámara. Una escena a sangre permanece detrás del contenido durante el recorrido; el encuadre móvil es vertical y conserva la cobra bajo los textos iniciales. El menú se despliega en el flujo por debajo de 1120 px. A 760 px las composiciones pasan a una columna. El documento mantiene el desplazamiento vertical; las tablas desplazan horizontalmente dentro de su contenedor.

## Elevation & Depth

Jerarquía mediante superficies y líneas. La portada combina una cobra reconstruida en tres dimensiones con escenarios ilustrados, una abertura real en la proyección del exterior y un corto paso de piedra modelado para dar profundidad al cruce. El perfil frontal y los materiales del báculo proceden de la referencia fotográfica del usuario; unas vistas de apoyo generadas a partir de ella aportan el reverso y el lateral. La superficie conserva las escamas de bronce envejecido, las placas plateadas y la veta del asta oscura, con reflejos suaves. Todo se compone y renderiza fuera de la aplicación. El texto usa sombras amplias de lectura sobre la escena, sin adornos ambientales en datos y formularios.

## Shapes

Radio compartido de 4 px. Capucha de cobra, escamas de bronce, vientre plateado y asta oscura según la referencia; piedra erosionada y umbrales rectos en los escenarios. No se añade un logotipo al objeto ni se redibuja la marca. Los badges tienen un radio reducido y mantienen etiquetas legibles.

## Components

`layout` comparte marca, navegación autorizada y estado de sesión. `area-link` representa accesos existentes sin numeraciones decorativas. `input`, `label`, `select`, `date`, `checkbox` y `badge` conservan su contrato. `icon` contiene dibujos SVG propios de trazo uniforme y decorativos; las acciones conservan texto accesible.

Botones: la portada usa la variante `button--expedition`, una placa oscura de contorno amarillo, esquinas biseladas y un icono de pirámide. El hover rellena la placa; el foco conserva un contorno exterior completo porque solo se recortan las capas decorativas. El área privada mantiene su botón amarillo habitual. Los enlaces conservan destinos y etiquetas. Select y calendario nativos: aceptamos sus ventanas emergentes del sistema operativo. Mensajes de servidor en el layout y errores junto al campo.

Movimiento de portada: 144 imágenes WebP panorámicas de 1280 × 800 en escritorio y 72 verticales de 480 × 1000 en móvil. Blender renderiza el giro longitudinal completo del modelo, una inclinación gradual y una leve oscilación durante el avance; la aplicación solo distribuye imágenes y las dibuja en un canvas 2D. La posición real del documento decide el fotograma en ambos sentidos, mostrando una sola imagen completa, sin mezclas ni desenfoque añadido. En escritorio, la rueda vertical desplaza suavemente el documento con una constante de 160 ms; la secuencia no tiene un suavizado propio. Teclado, gestos táctiles, zoom y controles con desplazamiento propio mantienen su comportamiento nativo. El suavizado se cancela al interactuar por otros medios, cambiar de página u ocultar la pestaña y se desactiva con movimiento reducido. No se bloquea el documento ni se transforma su contenido. La cámara llega al interior antes del relato y mantiene ese encuadre durante su lectura. Se prepara una franja comprimida por delante de la dirección de desplazamiento, conservando solo una ventana pequeña decodificada. `prefers-reduced-motion`, ahorro de datos, falta de soporte o fallo inicial muestran el exterior fijo y el relato sobre un interior estático, sin el recorrido largo. Textos y enlaces siguen en Blade. El contenido existe y se lee sin JavaScript.

Los archivos de producción quedan fuera de la aplicación. `public/assets/mythos/expedition` contiene los WebP finales, carteles, recursos de imagen y el manifiesto de dimensiones; no se incorpora un motor 3D ni una dependencia nueva. El recorte de cobra y los dos escenarios se preparan con la herramienta integrada imagegen; los prompts y las fuentes de producción se guardan fuera del repositorio.

Referencias consultadas para esta evolución: [cobra de bronce del British Museum](https://www.britishmuseum.org/collection/object/Y_EA52831), [uraeus de The Met](https://www.metmuseum.org/art/collection/search/552850) y [descripción oficial de Aboreto](https://github.com/google/fonts/blob/main/ofl/aboreto/DESCRIPTION.en_us.html). La tipografía se inspira en mayúsculas escultóricas del Renacimiento; aquí se usa por su aspecto de inscripción. La reconstrucción del báculo es una interpretación tridimensional de la referencia del usuario, no una digitalización de esas piezas históricas.

Recursos locales en `public/assets/mythos`, obtenidos de la web de referencia:
- [Paleta y tipografía originales](https://mythosmurcia.es/wp-content/themes/mythos-child/assets/css/mythos.min.css?ver=2.5.2).
- [Fotografía de Expedición Maldita](https://mythosmurcia.es/wp-content/themes/mythos-child/assets/img/hero-expedicion-1120.webp) y [encuadre móvil](https://mythosmurcia.es/wp-content/themes/mythos-child/assets/img/hero-expedicion-movil.webp).
- [Logotipo tipográfico](https://mythosmurcia.es/wp-content/uploads/2026/10/logo-mythos.png.webp) y [símbolo](https://mythosmurcia.es/wp-content/uploads/2020/04/cropped-mythos-escape-room-murcia-4-300x300.png.webp).
- [Big Shoulders Display](https://mythosmurcia.es/wp-content/themes/mythos-child/assets/fonts/big-shoulders-display.woff2) e [IBM Plex Sans](https://mythosmurcia.es/wp-content/themes/mythos-child/assets/fonts/ibm-plex-sans.woff2).

## Do's and Don'ts

Continuación Dr. Zodiac, aprobada el 02/11/2026: un expediente de investigación abre una segunda escena después del bloque completo de Expedición Maldita. Carpeta de cartón, radiografía, informe y fotografía con movimiento tridimensional prerenderizado; la fotografía termina llenando el encuadre con el pasillo de una clínica ficticia. Los fotogramas, encuadres, textos, estilos y controlador de Expedición se conservan. `resources/css/zodiac.css` posee los tokens locales `--zodiac-paper: #D5C8A6`, `--zodiac-muted: #BDC6BD` y `--zodiac-background: #101311`, junto a los tokens de marca existentes. Big Shoulders Display da carácter condensado al título, IBM Plex Sans sirve al texto y Courier New a las anotaciones. No cambia la tipografía de otras secciones.

`resources/views/home/zodiac.blade.php` contiene la presentación accesible; `resources/js/zodiac-journey.js` controla únicamente la decoración del capítulo. Se entregan 144 WebP de 1600 × 1000 y 72 de 600 × 1250 con cámara móvil propia. La apertura mantiene las hojas en planos separados: primero ganan altura y se desplazan a los lados, después se inclinan. La tapa deja espacio para el contenido y el clip acompaña a la tapa. La revisión de calidad duplica el muestreo del render y conserva el inicio de cámara, el último tercio y el encuadre final. No cambia el comportamiento del scroll. El material de producción y los prompts quedan fuera del repositorio. Historia y ficha consultadas en [The Shadow](https://theshadowescaperoom.com/el-juego/) y [Mythos](https://mythosmurcia.es/nuestros-juegos/); el enlace indica que lleva a la web oficial de The Shadow. La escena es una interpretación artística identificada como tal. No hay nuevas funciones de reservas ni cambios de alcance de los sprints.

- Reutilizar tokens y componentes; conservar permisos y datos del servidor.
- Mantener las pantallas privadas sobrias y comprobar teclado, móvil y errores.
- No inventar datos comerciales, acciones futuras ni un logotipo alternativo.
- No añadir dependencias, servicios externos, seguimiento o documentación de instalación.

## Continuación El Virus

Tercer capítulo después de Dr. Zodiac: una muestra de cristal guía la entrada a un laboratorio. Se conservan completos los dos capítulos anteriores y el suavizado de rueda de 160 ms. El contraste de acero frío, vidrio y líquido ámbar distingue esta investigación del expediente y de la pirámide. No hay hologramas, partículas, paneles de datos ficticios ni efectos añadidos sobre las imágenes.

`resources/css/virus.css` posee los tokens locales `--virus-glass: #D5E5E4`, `--virus-muted: #B5C3C6` y `--virus-background: #101619`. Reutiliza negro, amarillo, tipografía y foco del sitio. Big Shoulders Display compone el título y las cifras; IBM Plex Sans sirve a la lectura y Courier New a las anotaciones. La composición inicial coloca el título a la izquierda y la muestra a la derecha; móvil usa cámara vertical y márgenes de 22 px. La ficha final permanece sobre un laboratorio oscuro y legible.

El frasco conserva la silueta aprobada el 03/11/2026 y la etiqueta envejecida con «EL VIRUS» y el símbolo azul oscuro sin cruz roja. La imagen aprobada y sus vistas de apoyo aportan la textura del papel. El vidrio hueco, el líquido ámbar y el tapón moleteado se modelan en 3D. El render por trazado de rayos calcula reflejos y refracción en cada fotograma, y la superficie del líquido se mantiene horizontal al inclinarse el frasco. El frasco gira, se eleva y cruza la abertura mientras la cámara entra al laboratorio. Se distribuyen 144 WebP de 1600 × 1000 y 72 de 600 × 1250. El laboratorio es una ambientación artística original creada con imagegen. Los recursos finales están en `public/assets/mythos/virus` y las fuentes del render permanecen fuera del repositorio.

La compuerta usa la imagen aprobada de acero gastado, placa marfil «CONTENCIÓN» y piloto ámbar. La fotografía se proyecta sobre dos hojas correderas y sobre un marco fijo. La placa acompaña a la hoja derecha. Al abrirse las hojas queda un hueco real por el que pasan el frasco y la cámara.

El Virus 2 sitúa el Programa Solanum en un puesto de control abandonado. El medallón de latón y esmalte oliva muestra tres muescas y encaja en la consola. Tras un breve apagón, el piloto verde confirma la activación y las balizas se encienden una a una hasta la salida. La ficha de la sala permanece accesible mediante un enlace que salta el recorrido. El decorado y el medallón son interpretaciones artísticas de la historia oficial de Enigmatic.

Legends continúa la portada con un cuaderno cerrado sobre piedra soleada. La goma roja se aparta, la tapa gira sobre el lomo y la tinta conecta las pistas. El último dibujo es una farola que conduce al sendero del jardín. El mapa y la vista son recreaciones artísticas. `resources/css/legends.css` reúne la tinta oscura, el ocre de la ficha y el rojo del cuaderno, con Big Shoulders Display para el título y letra itálica para la nota interior. La ficha mantiene su enlace directo, y el capítulo muestra la portada cerrada sin JavaScript o con movimiento reducido. La localización y la tableta interactiva están documentadas por [Legends](https://legendsmurcia.es/).

`resources/views/home/virus.blade.php` conserva título, relato, datos y enlaces accesibles. `resources/js/experience-sequence.js` es el controlador reutilizable de los capítulos siguientes; los controladores aceptados de Expedición y Zodiac permanecen intactos. Solo dibuja una imagen opaca por posición real del scroll. La carga se aproxima al viewport, limita memoria y solicitudes, y libera recursos al salir. Sin JavaScript, con movimiento reducido, ahorro de datos, falta de soporte o fallo inicial se muestra la alternativa estática sin recorrido largo.

Historia y ficha consultadas en [Enigmatic](https://enigmatic.es/el-juego/) el 02/11/2026: Dr. Luis Fuentes Ferrer desaparecido durante 48 horas, búsqueda de la cura con el CNI, 2–6 personas, 60 minutos y dificultad media. «Conocer El Virus» indica su destino en la web oficial. No se añaden reservas, rutas, modelos, permisos ni funciones de otros sprints.

Road Room cierra el recorrido con una foto retocada del lateral del remolque. La cámara se acerca a la puerta derecha para revelar el ascensor y cruza por el logotipo hacia la cabina del DJ, a la izquierda. El capítulo usa azul noche y ámbar, con Big Shoulders Display para el título. La ficha tiene acceso directo y el remolque se muestra cerrado con movimiento reducido.
