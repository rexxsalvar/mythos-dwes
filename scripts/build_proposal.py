from pathlib import Path
from reportlab.pdfgen import canvas
from reportlab.platypus import SimpleDocTemplate, Paragraph, Table, TableStyle, Spacer, PageBreak
from reportlab.lib import colors
from reportlab.lib.styles import ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'output/pdf/Mythos_Desarrollo_de_idea_Sprint_1.pdf'
OUT.parent.mkdir(parents=True, exist_ok=True)
for name, file in [('Segoe','segoeui.ttf'),('Segoe-Bold','segoeuib.ttf')]:
    pdfmetrics.registerFont(TTFont(name, str(Path('C:/Windows/Fonts') / file)))
pdfmetrics.registerFontFamily('Segoe', normal='Segoe', bold='Segoe-Bold')
INK = colors.HexColor('#17333E')
TEAL = colors.HexColor('#087F82')
MUTED = colors.HexColor('#536773')
LINE = colors.HexColor('#D9E5E7')
W = 499.28
styles = {
    'body': ParagraphStyle('body', fontName='Segoe', fontSize=10, leading=13.5, textColor=INK, spaceAfter=7),
    'small': ParagraphStyle('small', fontName='Segoe', fontSize=8.4, leading=11.3, textColor=MUTED, spaceAfter=5),
    'heading': ParagraphStyle('heading', fontName='Segoe-Bold', fontSize=13.2, leading=17, textColor=TEAL, spaceBefore=10, spaceAfter=7, keepWithNext=True),
    'cell': ParagraphStyle('cell', fontName='Segoe', fontSize=9.3, leading=12.2, textColor=INK),
    'th': ParagraphStyle('th', fontName='Segoe-Bold', fontSize=9.2, leading=12, textColor=colors.white),
    'title': ParagraphStyle('title', fontName='Segoe-Bold', fontSize=29, leading=32, textColor=INK, spaceAfter=5),
    'subtitle': ParagraphStyle('subtitle', fontName='Segoe', fontSize=12, leading=16, textColor=INK, spaceAfter=7),
}
story = []
def p(text, style='body'):
    return Paragraph(text, styles[style])
def add(text, style='body'):
    story.append(p(text,style))
def h(text):
    add(text,'heading')
def table(headers,rows,widths):
    t=Table([[p(x,'th') for x in headers]]+[[p(x,'cell') for x in row] for row in rows],colWidths=widths,repeatRows=1,hAlign='LEFT')
    t.setStyle(TableStyle([
        ('BACKGROUND',(0,0),(-1,0),INK),
        ('ROWBACKGROUNDS',(0,1),(-1,-1),[colors.HexColor('#F0F5F5'),colors.white]),
        ('VALIGN',(0,0),(-1,-1),'TOP'),
        ('LEFTPADDING',(0,0),(-1,-1),8),('RIGHTPADDING',(0,0),(-1,-1),8),
        ('TOPPADDING',(0,0),(-1,-1),5),('BOTTOMPADDING',(0,0),(-1,-1),5),
        ('LINEBELOW',(0,-1),(-1,-1),.5,LINE),
    ]))
    story.extend([t,Spacer(1,5)])
def page():
    story.append(PageBreak())

add('MYTHOS','title')
add('Sistema de reservas y gestión para un negocio de escape rooms','subtitle')
add('Desarrollo de la idea · Sprint 1 · Proyecto individual DWES · 01/10/2026','small')
add('<b>Finalidad de esta ficha.</b> Concretar la propuesta para comenzar el análisis y la planificación. Se apoya en el negocio real de Mythos, en Murcia, y plantea las funciones que se desarrollarán en el proyecto.','small')
h('1. Descripción y objetivo del proyecto')
add('Mythos será una aplicación web para consultar experiencias y gestionar el recorrido completo de una reserva: elección de sesión, confirmación, preparación de la sala, llegada del grupo, desarrollo de la partida y registro del resultado. Los clientes dispondrán de una zona privada y el personal de herramientas para organizar sesiones e incidencias.')
add('La experiencia real de referencia será <b>Expedición Maldita</b>, una aventura de temática egipcia para 2 a 6 jugadores y 75 minutos. Sus precios publicados por grupo son 80 €, 90 €, 100 €, 110 € y 132 €, respectivamente. El modelo permitirá gestionar varias salas y experiencias.')
add('El núcleo estará en evitar reservas incompatibles, controlar participantes y permisos, aplicar condiciones de cancelación y bloquear salas por mantenimiento. Estas necesidades justificarán el uso de API, eventos, colas, correos, informes y pruebas.')
h('2. Actores y perfiles de usuario')
table(['Perfil','Responsabilidad principal'],[
    ['Visitante','Consulta experiencias, tarifas, condiciones y disponibilidad.'],
    ['Cliente','Gestiona sus reservas y equipos; consulta resultados y documentos.'],
    ['Recepción','Revisa reservas, registra llegadas y gestiona cambios de sesión.'],
    ['Game master','Prepara y dirige sus partidas; registra pistas, tiempos, resultados e incidencias.'],
    ['Mantenimiento','Atiende incidencias, registra reparaciones y revisa el estado de las salas.'],
    ['Administrador','Gestiona experiencias, salas, horarios, tarifas, usuarios, permisos e informes.'],
],[107,392.28])
add('El mínimo docente es de dos roles con Laravel-permission: cliente y administrador. La propuesta mantiene además recepción, game master y mantenimiento como especialización del personal. El visitante no requiere un rol autenticado.','small')
h('3. Alcance funcional inicial')
add('Catálogo público con fichas de experiencias y disponibilidad. Registro y autenticación, reservas y cancelaciones, agenda del personal, preparación de partidas, equipos, resultados e incidencias de mantenimiento.')
add('Se gestionarán <b>experiencias, salas, sesiones, reservas e incidencias</b>. El mínimo técnico incluye tres clases Livewire, dos de ellas CRUD completos, y una API con tres modelos y dos CRUD completos. La aplicación tendrá traducciones al español y al inglés. Los pagos online, bonos regalo y otras modalidades serán ampliaciones.')

page()
h('4. Propuesta de modelo de datos')
add('Se distinguirá la experiencia, que representa la historia o juego, de la sala física donde se realiza. El siguiente modelo es orientativo y se concretará durante el análisis.')
table(['Modelo','Responsabilidad'],[
    ['User','Datos de acceso, contacto y roles del usuario.'],
    ['Experience','Historia, descripción, duración, límites de jugadores e idiomas.'],
    ['Room','Espacio físico, ubicación, capacidad y estado operativo.'],
    ['GameSession','Partida programada: experiencia, sala, horario, game master y estado.'],
    ['Reservation','Cliente, sesión, participantes, equipo, importe y estado de la reserva.'],
    ['Rate','Tarifa de una experiencia según el número de jugadores.'],
    ['Team','Equipo y usuarios que lo integran.'],
    ['Hint','Pista de una experiencia y registro de su uso en las partidas.'],
    ['PreparationCheck','Comprobación previa de la sala, responsable y resultado de la revisión.'],
    ['GameResult / Review','Resultado de la partida y, como ampliación, valoración del cliente.'],
    ['Incident','Incidencia de una sala o sesión: gravedad, estado y responsable.'],
    ['MaintenanceAction','Intervención realizada para resolver una incidencia.'],
],[126,373.28])
h('5. Relaciones que deberían aparecer')
add('Un usuario puede realizar muchas reservas. Cada sesión corresponde a una experiencia, se celebra en una sala y tiene un game master responsable. Las salas acumulan sesiones e incidencias; cada incidencia puede tener varias actuaciones de mantenimiento.')
add('Una sesión finalizada tendrá un resultado. Como ampliación, su titular podrá valorar la reserva completada. Se plantea una partida privada por grupo, por lo que solo podrá existir una reserva activa por sesión.')
add('La relación <b>N:N entre equipos y usuarios</b> guardará en la tabla pivote la función de capitán o miembro y la fecha de incorporación. La relación <b>N:N entre sesiones y pistas</b> registrará cuándo se dio cada pista y su penalización, si procede. No será obligatorio que todos los participantes tengan cuenta.')
add('<b>Reglas de negocio.</b> Impedir reservas duplicadas y grupos fuera del aforo; evitar solapamientos de sala y game master; reservar tiempo de preparación; conservar el precio acordado; limitar cancelaciones por estado y antelación; permitir al game master modificar solo sus partidas; bloquear nuevas reservas e inicios de partida ante una incidencia crítica.')
add('Las reservas ya confirmadas afectadas por una avería se revisarán desde recepción. El plazo de cancelación y el margen entre partidas se definirán con el negocio durante el análisis. Las reglas se reflejarán en servicios, validaciones, Policies y pruebas.')

page()
h('6. Casos de uso principales')
add('Consultar disponibilidad por experiencia y fecha. Reservar una sesión y obtener confirmación. Cancelar según las condiciones establecidas. Programar partidas y asignar game master. Completar la preparación, registrar llegada, pistas y resultado. Gestionar una avería y sus sesiones afectadas. Consultar el historial y generar informes.')
h('7. Funcionalidades por áreas')
table(['Área','Funciones'],[
    ['Zona pública','Catálogo, detalle de experiencias, tarifas, calendario, condiciones y contacto. La disponibilidad no mostrará datos privados de clientes.'],
    ['Cliente','Próximas reservas, cancelaciones permitidas, perfil, equipos, historial, resultados y documentos.'],
    ['Personal','Agenda, llegadas, cambios de sesión, preparación, registro de partidas, incidencias y mantenimiento según permisos.'],
    ['Administración','Datos maestros, tarifas, reglas, usuarios, roles, asignaciones e informes de actividad.'],
],[107,392.28])
h('8. API propuesta')
add('La API utilizará tres modelos: Experience, GameSession y Reservation. Incluirá dos CRUD completos, de experiencias y reservas, con escritura protegida y el de reservas autenticado mediante Sanctum. Las sesiones ofrecerán consulta de disponibilidad. El cliente solo podrá acceder a sus reservas; la administración tendrá permisos de gestión.')
add('Permitirá utilizar estas funciones desde un cliente externo y compartirá las reglas de negocio con Livewire. Se documentarán ejemplos de petición y respuesta, errores y requisitos de acceso.')
h('9. Automatizaciones y procesos de Laravel')
table(['Mecanismo','Ejemplo en Mythos','Finalidad'],[
    ['3 commands','Caducar reservas, preparar recordatorios y generar informe de actividad.','El de informes se invocará también desde código.'],
    ['2 events','ReservationConfirmed e IncidentReported.','Representar cambios relevantes.'],
    ['Listeners','Confirmar por correo la reserva y avisar internamente de incidencias.','Un listener asociado a cada evento.'],
    ['2 jobs','Generar un documento y enviar recordatorios.','Al menos el de recordatorios utilizará colas.'],
    ['2 emails','Confirmación de reserva y recordatorio de partida.','Comunicación con el cliente.'],
],[79,239,181.28])
add('Los cambios de disponibilidad se guardarán antes de enviar avisos. Las reservas pendientes tendrán una caducidad configurable para no bloquear indefinidamente una sesión.','small')

page()
h('10. Policies, scopes, validación y componentes')
add('<b>Dos Policies:</b> reservas propias y partidas asignadas. <b>Tres scopes:</b> reservas futuras; sesiones disponibles según fecha, reservas y bloqueos; incidencias filtradas por sala, gravedad y estado. Los dos últimos serán complejos. Se usará <b>FormRequest</b> en formularios con más de dos campos.')
add('<b>Tres clases Livewire:</b> CRUD de experiencias, CRUD de reservas y agenda filtrable. Componentes Blade para input, fechas, select, labels y checkbox. <b>Pest</b> deberá alcanzar al menos un <b>85 % de cobertura</b>, con pruebas de permisos, aforo, concurrencia y mantenimiento.')
h('11. Informes, documentos y ampliaciones')
add('Los dos PDF mínimos, con <b>laravel-dompdf</b>, serán el justificante de reserva y el informe mensual de ocupación e importes. El segundo incluirá filtros, tablas y totales, distinguiendo reservas de cobros registrados. Las traducciones a <b>español e inglés son obligatorias</b>.')
add('Ampliaciones: diploma, valoraciones, informes adicionales, bonos regalo, pagos online, Excel, QR y pasaporte de escapista. Se priorizarán después de los mínimos. Los bonos y el juego en inglés ya figuran en la oferta de Mythos.')
h('12. Planificación según el calendario docente')
table(['Sprint','Aplicación al proyecto'],[
    ['1 · 21/09-04/10/26','Propuesta, repositorio Git, tablero Scrum y backlog inicial.'],
    ['2 · 05/10-18/10/26','Diagrama E-R, pivote con columnas propias, migraciones, factories y seeders.'],
    ['3 · 19/10-01/11/26','Estructura, autenticación, rutas, dos roles y primera Policy.'],
    ['4 · 02/11-15/11/26','CRUD web, FormRequest, primer scope y componentes Blade.'],
    ['5 · 16/11-29/11/26','Tres clases Livewire, segunda Policy y tres scopes, dos complejos.'],
    ['6 · 30/11-13/12/26','API: tres modelos, dos CRUD, autenticación y documentación inicial.'],
    ['7 · 14/12-27/12/26','Tres comandos, dos eventos y listeners, dos jobs y dos emails.'],
    ['8 · 28/12/26-10/01/27','Dos PDF con dompdf, uno complejo, y traducciones ES/EN.'],
    ['9 · 11/01-24/01/27','Pest: cobertura mínima del 85 %, correcciones y primeras ampliaciones.'],
    ['10 · 25/01-07/02/27','Ampliaciones, documentación de API, README y revisión de mínimos.'],
    ['08/02-12/02/27','Integración final, demo, defensa y entrega el 12 de febrero.'],
],[129,370.28])
add('<b>Fuentes del negocio</b> · Consulta: 29/09/2026. <link href="https://mythosmurcia.es/expedicion-maldita/" color="#087F82">Expedición Maldita y tarifas</link> · <link href="https://mythosmurcia.es/preguntas-frecuentes/" color="#087F82">Preguntas frecuentes</link> · <link href="https://mythosmurcia.es/experiencia-regalo/" color="#087F82">Bono regalo</link>. La estructura sigue el ejemplo ReservaSport facilitado.','small')

class NumberedCanvas(canvas.Canvas):
    def __init__(self,*args,**kwargs):
        super().__init__(*args,**kwargs)
        self.saved=[]
    def showPage(self):
        self.saved.append(dict(self.__dict__))
        self._startPage()
    def save(self):
        total=len(self.saved)
        for state in self.saved:
            self.__dict__.update(state)
            self.setStrokeColor(LINE)
            self.line(48,38,547.28,38)
            self.setFont('Segoe',8)
            self.setFillColor(MUTED)
            self.drawString(48,25,'MYTHOS · Desarrollo de la idea · Sprint 1')
            self.drawRightString(547.28,25,f'{self._pageNumber} / {total}')
            super().showPage()
        super().save()

doc=SimpleDocTemplate(str(OUT),pagesize=(595.28,841.89),leftMargin=42,rightMargin=42,topMargin=35,bottomMargin=53,
    title='Mythos - Desarrollo de la idea - Sprint 1',author='Proyecto Mythos',subject='Propuesta inicial de aplicación web de escape rooms')
doc.build(story,canvasmaker=NumberedCanvas)
print(OUT)
