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
    'body': ParagraphStyle('body', fontName='Segoe', fontSize=9.4, leading=12.2, textColor=INK, spaceAfter=6),
    'small': ParagraphStyle('small', fontName='Segoe', fontSize=8.4, leading=11.3, textColor=MUTED, spaceAfter=5),
    'heading': ParagraphStyle('heading', fontName='Segoe-Bold', fontSize=12.2, leading=15.3, textColor=TEAL, spaceBefore=8, spaceAfter=7, keepWithNext=True),
    'cell': ParagraphStyle('cell', fontName='Segoe', fontSize=8.6, leading=11, textColor=INK),
    'th': ParagraphStyle('th', fontName='Segoe-Bold', fontSize=8.8, leading=11.2, textColor=colors.white),
    'title': ParagraphStyle('title', fontName='Segoe-Bold', fontSize=29, leading=32, textColor=INK, spaceAfter=5),
    'subtitle': ParagraphStyle('subtitle', fontName='Segoe', fontSize=12, leading=16, textColor=INK, spaceAfter=6),
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
        ('TOPPADDING',(0,0),(-1,-1),4),('BOTTOMPADDING',(0,0),(-1,-1),4),
        ('LINEBELOW',(0,-1),(-1,-1),.5,LINE),
    ]))
    story.extend([t,Spacer(1,5)])
def page():
    story.append(PageBreak())

add('MYTHOS','title')
add('Sistema de reservas y gestión de escape rooms','subtitle')
add('Propuesta del Sprint 1 · Revisión docente: 20/10/2026 · Proyecto individual DWES','small')
h('1. Objetivo y alcance académico')
add('La aplicación permitirá consultar experiencias y gestionar una reserva desde la elección de sesión hasta la preparación, llegada del grupo, partida, resultado y mantenimiento. Mythos, en Murcia, aporta el contexto del negocio. EscapeHub identifica el material inicial, no otro proyecto. El nombre definitivo para la ficha docente será <b>Mythos - Sistema de reservas y gestión de escape rooms</b>.')
add('Expedición Maldita es la referencia del análisis inicial: 75 minutos y grupos de 2 a 6 personas. Las condiciones reales del negocio no sustituyen las decisiones académicas ni demuestran por sí mismas que se cubran los requisitos. Las personas de demostración serán ficticias.')
h('2. Perfiles y responsabilidades')
table(['Perfil','Responsabilidad'],[
    ['Visitante','Catálogo, tarifas y disponibilidad sin datos privados.'],
    ['Cliente','Reservas propias, participantes, equipos, resultados y justificantes.'],
    ['Recepción','Reservas, llegadas, cambios autorizados y revisión de sesiones afectadas.'],
    ['Game master','Preparación y desarrollo de partidas asignadas, pistas y resultados.'],
    ['Mantenimiento','Incidencias asignadas, actuaciones y minutos de intervención.'],
    ['Administración','Catálogo, salas, agenda, tarifas, usuarios, permisos e informes.'],
],[108,391.28])
add('Se utilizará Laravel-permission; cliente y administrador son el mínimo docente y se mantienen los tres perfiles de personal para la operativa. El visitante no requiere un rol autenticado.','small')
h('3. Compromisos obligatorios y exclusiones')
add('<b>Cinco CRUD:</b> experiencias, salas, sesiones, reservas e incidencias. <b>Siete recursos API, cinco policies, siete scopes y siete clases Livewire.</b> Siete comandos, cinco pares evento-listener, cinco jobs con tres en cola y cinco correos. Tres PDF complejos y justificante simple con laravel-dompdf.')
add('<b>Interfaz en español e inglés y Pest con al menos el 85 % de cobertura son obligatorios.</b> Traducir la aplicación es distinto de ofrecer una partida dirigida en inglés: ese idioma requiere disponibilidad operativa y no se deduce de una ficha traducida.')
add('Valoraciones, diploma, bonos regalo y Excel son ampliaciones. El correo de resultado será obligatorio aunque no haya diploma. Quedan fuera pagos online, QR, pasaporte de escapista, logística física de bonos y puesta en producción. Los cobros son registros manuales; no se incluye contabilidad ni facturación fiscal.')

page()
h('4. Modelo de datos y relaciones')
table(['Área','Entidades y finalidad'],[
    ['Catálogo y precios','Experience, Room, Location, Rate y RatePlan: juego, espacio, local y precios por grupo. Traducciones e idioma de juego separados.'],
    ['Agenda y reservas','User, GameSession, Reservation y participantes: titular, grupo, horario, responsable, vencimiento y precio acordado.'],
    ['Partidas y equipos','Team, Hint, GameResult y preparación: equipo estable, pistas, comprobaciones previas y resultado.'],
    ['Incidencias','Incident y MaintenanceAction: avería, técnico, intervenciones y tiempos registrados.'],
    ['Histórico y documentos','Bloqueos, cambios de reserva, cobros, avisos y archivos generados; sin automatismos por tener la tabla.'],
],[108,391.28])
add('Relaciones <b>1:N</b>: usuario-reservas e incidencia-actuaciones. <b>N:N</b>: equipos-usuarios con función y fecha de incorporación; sesiones-pistas con momento y penalización. Los participantes pueden no tener cuenta. El modelo publicado en S2 conserva 32 tablas de negocio; los nuevos informes requieren añadir minutos de intervención y dos tipos de documento mediante migraciones posteriores.','small')
h('5. Sesión exclusiva, pendientes e histórico')
add('Cada sesión es privada para un grupo. Estados de reserva: <b>pending, confirmed, completed, cancelled y expired</b>. Pending y confirmed ocupan la sesión; solo puede existir una activa. El alumno aprueba como reglas académicas <b>15 minutos de pendiente y 30 minutos de preparación antes del inicio</b>. El vencimiento se adelanta al inicio de preparación si llega antes; no se reserva cuando ese intervalo ya ha comenzado.')
add('Sala y game master se ocupan en <b>[inicio - 30 minutos, fin)</b>. Se impiden intersecciones incluso con altas concurrentes; los intervalos contiguos se admiten. Una incidencia crítica bloquea nuevas reservas e inicio y señala las confirmadas para revisión. Resolver una avería no elimina otros bloqueos.')
add('Reservar, confirmar y caducar usarán transacción y bloqueo de escritura en SQLite, con índice único de reserva activa como garantía final. Se caduca cada minuto y también al reservar dentro de la transacción. Al confirmar se conservan participantes, tarifa del grupo, moneda y fecha: editar Rate no recalcula el histórico. Cancelación y condiciones de cobro pendientes de acuerdo; no se atribuyen plazos inventados al negocio.')
h('6. Tres informes PDF complejos')
table(['Informe / filtros','Detalle y agregados verificables'],[
    ['Mensual: mes, local, experiencia, sala y estado.','Sesiones ofertadas/ocupadas, porcentaje de ocupación, participantes, importe reservado, cobros, devoluciones y neto; subtotales por día y experiencia.'],
    ['Diario: día, local, sala, experiencia, game master y estado.','Sesiones y participantes; minutos programados de juego y preparación; carga por sala y responsable.'],
    ['Mantenimiento: periodo, sala, gravedad, estado y técnico.','Incidencias y actuaciones; recuentos únicos, minutos de intervención por sala/técnico y media de tiempo de resolución.'],
],[185,314.28])
add('Con laravel-dompdf, filtros visibles, detalle, subtotales y totales sin duplicarlos por relaciones. Mantenimiento usa tiempos, no costes inventados. Justificante obligatorio y diploma opcional pueden ser simples. Filtros y fórmulas completos en docs/revision-sprint-1.md.','small')

page()
h('7. API y autorización')
table(['Recurso','Operaciones y acceso'],[
    ['User','Consulta propia o por administración. Siempre autenticado.'],
    ['Experience','CRUD completo. Lectura pública del catálogo publicado; escritura administrativa.'],
    ['Room','CRUD completo. Oferta pública limitada; escritura administrativa.'],
    ['GameSession','CRUD completo por personal autorizado; disponibilidad pública sin datos personales.'],
    ['Reservation','CRUD completo, con cancelación conservando histórico. Siempre autenticado; titular o personal autorizado.'],
    ['Rate','Consulta pública de tarifas vigentes por experiencia y grupo; sin escritura pública.'],
    ['Incident','CRUD completo. Siempre autenticado, solo personal autorizado.'],
],[107,392.28])
add('<b>User, Reservation e Incident</b> quedan reservados a autenticación con Sanctum. Los cinco CRUD incluyen alta, listado/detalle, edición y baja compatible con el histórico. Se documentarán rutas, filtros, paginación, errores 401/403 y ejemplos. Web, Livewire y API aplicarán las mismas reglas. Team y MaintenanceAction mantienen uso interno.')
h('8. Cinco policies')
add('<b>ReservationPolicy:</b> titular, rol, estado y acción. <b>GameSessionPolicy:</b> agenda administrativa o partida asignada. <b>ExperiencePolicy:</b> publicación y edición administrativa. <b>RoomPolicy:</b> oferta y gestión de salas. <b>IncidentPolicy:</b> acceso del personal y técnico asignado. User se limita al perfil propio o administración mediante autorización de ruta y consulta.')
h('9. Siete scopes')
add('Experience.<b>published</b>; Reservation.<b>upcoming</b> y <b>activeAt</b>; GameSession.<b>availableBetween</b>; Incident.<b>requiringAttention</b>; Rate.<b>effectiveForGroup</b>; MaintenanceAction.<b>performedBetween</b>. Disponibilidad combina fecha, aforo, reservas, bloqueos y preparación; incidencias combina sala, gravedad y estado; tarifas incorpora vigencia y grupo. No basta con declarar métodos sin utilizarlos.')
h('10. Siete clases Livewire y validación')
add('<b>ExperiencesManager, RoomsManager, SessionsManager, ReservationsManager e IncidentsManager</b> implementan los cinco CRUD. <b>AgendaBrowser</b> filtra la agenda y <b>PreparationPanel</b> gestiona preparación y resultado. FormRequest en formularios de más de dos campos y componentes Blade para input, fechas, select, labels y checkbox. Los componentes Blade no cuentan como clases Livewire.')
h('11. Evidencia de calidad y límites de la propuesta')
add('<b>Pest y cobertura mínima medida del 85 % del código de aplicación.</b> Se comprobarán permisos, aforo, expiración, dos solicitudes concurrentes, preparación, precio histórico, colas, idiomas y documentos. El porcentaje se acredita con informe de cobertura; no por citar Mythos ni por contar tablas o pruebas.')
add('Esta revisión actualiza compromisos y criterios. No declara implementados endpoints, policies, Livewire, correos o informes. S2 tiene evidencia publicada del modelo y pruebas iniciales; el alumno todavía no ha recibido corrección docente de ese sprint. El registro del centro debe reflejar Mythos y sus enlaces; su actualización externa queda por comprobar.','small')

page()
h('12. Automatizaciones comprometidas')
add('<b>Siete comandos:</b> reservations:expire; reservations:remind; reports:monthly; reports:daily; reports:maintenance; sessions:send-results; incidents:review-reservations. El informe mensual se invoca también desde código autorizado. El comando de resultados no finaliza partidas automáticamente.')
table(['Evento','Listener'],[
    ['ReservationConfirmed','QueueReservationConfirmation'],
    ['ReservationCancelled','QueueReservationCancellation'],
    ['IncidentReported','HandleIncidentImpact'],
    ['GameSessionCompleted','QueueSessionResult'],
    ['DocumentRequested','QueueDocumentGeneration'],
],[230,269.28])
add('<b>Cinco jobs:</b> SendCustomerEmail, SendIncidentAlert y GenerateDocument <b>en cola</b>; ExpirePendingReservations y ReviewAffectedReservations síncronos. Eventos después del commit; registro de envíos y claves para evitar programar duplicados; errores y reintentos comprobables.')
add('<b>Cinco correos:</b> confirmación, recordatorio, cancelación, aviso interno de incidencia y resultado de partida. El quinto funciona sin diploma; se adjunta si se desarrolla esa ampliación. No hay recordatorios de reservas canceladas o vencidas.')
h('13. Diez sprints y semana final')
table(['Sprint / fechas','Resultado y comprobación'],[
    ['1 · 21/09-04/10/26','Propuesta, Git, tablero y backlog; revisión del 20/10 con criterios corregidos.'],
    ['2 · 05/10-18/10/26','E-R, modelos, pivotes, migraciones, factories, seeders y reconstrucción.'],
    ['3 · 19/10-01/11/26','Autenticación, roles, rutas y ReservationPolicy; matriz de permisos.'],
    ['4 · 02/11-15/11/26','CRUD web, FormRequest, componentes, pendientes y preparación; concurrencia.'],
    ['5 · 16/11-29/11/26','Completar cinco CRUD/policies, siete scopes y siete clases Livewire; operativa.'],
    ['6 · 30/11-13/12/26','Siete recursos API, cinco CRUD, tres recursos privados y documentación.'],
    ['7 · 14/12-27/12/26','Siete comandos, cinco pares evento-listener, cinco jobs/tres en cola y cinco emails.'],
    ['8 · 28/12/26-10/01/27','Tres PDF complejos, justificante e interfaz ES/EN obligatoria.'],
    ['9 · 11/01-24/01/27','Pest: cobertura mínima del 85 % y correcciones; extras solo tras los mínimos.'],
    ['10 · 25/01-07/02/27','Cierre de contrato API, revisión de criterios y versión candidata; extras si caben.'],
    ['Final · 08/02-12/02/27','Integración, demo, defensa y entrega oficial el viernes 12/02.'],
],[136,363.28])
add('Navidad afecta a S7/S8: se priorizan los requisitos obligatorios. La semana final no se convierte en un sprint 11. Las mismas 28 tarjetas se refinan con criterios de aceptación, sin duplicarlas.','small')
add('<b>Registro y evidencia.</b> <link href="https://github.com/rexxsalvar/mythos-dwes" color="#087F82">Repositorio mythos-dwes</link> · <link href="https://github.com/users/rexxsalvar/projects/1" color="#087F82">Tablero Mythos - Proyecto DWES</link> · <link href="https://github.com/rexxsalvar/mythos-dwes/blob/main/docs/revision-sprint-1.md" color="#087F82">Criterios detallados de la revisión</link>. Fuentes: correcciones docentes comunicadas el 20/10 y propuesta inicial; la referencia comercial del análisis del 29/09 no certifica condiciones actuales.','small')

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
    title='Mythos - Desarrollo de la idea - Sprint 1',author='Proyecto Mythos',subject='Propuesta revisada según correcciones docentes del 20/10/2026')
doc.build(story,canvasmaker=NumberedCanvas)
print(OUT)
