<?php

namespace Database\Seeders;

use App\Models\AvailabilityBlock;
use App\Models\BookingPolicyVersion;
use App\Models\Experience;
use App\Models\ExperienceLanguage;
use App\Models\ExperienceTranslation;
use App\Models\GameResult;
use App\Models\GeneratedDocument;
use App\Models\GiftVoucher;
use App\Models\GiftVoucherRedemption;
use App\Models\Incident;
use App\Models\IncidentImpact;
use App\Models\NotificationDelivery;
use App\Models\PaymentTransaction;
use App\Models\PreparationTemplate;
use App\Models\PreparationTemplateItem;
use App\Models\Reservation;
use App\Models\ReservationEvent;
use App\Models\ReservationParticipant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CompleteDataModelSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $experience = Experience::where('slug', 'expedicion-maldita')->firstOrFail();
            $client = User::where('email', 'cliente@example.test')->firstOrFail();
            $admin = User::where('email', 'admin@example.test')->firstOrFail();
            $master = User::where('email', 'master@example.test')->firstOrFail();
            $technician = User::where('email', 'tecnico@example.test')->firstOrFail();

            // Data for the five planned profiles; no authorization routes or Policies are implemented here.
            foreach (['cliente' => 'cliente', 'administrador' => 'admin', 'recepcion' => 'recepcion', 'game_master' => 'master', 'mantenimiento' => 'tecnico'] as $role => $email) {
                Role::findOrCreate($role, 'web');
                User::where('email', $email.'@example.test')->firstOrFail()->assignRole($role);
            }
            User::where('email', 'miembro@example.test')->firstOrFail()->assignRole('cliente');

            foreach (['es' => ['Expedición Maldita', 'Aventura egipcia de referencia; datos de demostración.'], 'en' => ['Cursed Expedition', 'Egyptian adventure reference; demonstration data.']] as $locale => [$title, $description]) {
                ExperienceTranslation::updateOrCreate(['experience_id' => $experience->id, 'locale' => $locale], compact('title', 'description'));
                ExperienceLanguage::firstOrCreate(['experience_id' => $experience->id, 'locale' => $locale]);
            }
            $policy = BookingPolicyVersion::firstOrCreate(['experience_id' => $experience->id, 'version' => 1], [
                'name' => 'Borrador pendiente de decisiones del negocio', 'status' => 'draft',
                'cancellation_notice_minutes' => null, 'pending_expiry_minutes' => null, 'preparation_minutes' => null,
                'confirmation_conditions' => null, 'payment_conditions' => null,
            ]);
            $template = PreparationTemplate::firstOrCreate(['experience_id' => $experience->id, 'name' => 'Preparación de demostración', 'version' => 1], ['is_active' => true]);
            foreach (['Revisar mecanismos', 'Comprobar iluminación', 'Preparar material'] as $offset => $label) {
                PreparationTemplateItem::updateOrCreate(['preparation_template_id' => $template->id, 'position' => $offset + 1], ['label' => $label, 'is_required' => true]);
            }

            $bookings = Reservation::where('reference', 'like', 'MYTHOS-DEMO-%')->with('gameSession')->get()->keyBy('status');
            foreach ($bookings as $booking) {
                $booking->gameSession->update(['preparation_template_id' => $template->id]);
                // The unapproved policy is deliberately NOT assigned to bookings or sessions.
                $booking->update([
                    'rate_id' => $experience->rates()->where('players', $booking->players)->firstOrFail()->id,
                    'booked_by' => $client->id, 'contact_name' => $client->name, 'contact_email' => $client->email, 'locale' => 'es',
                ]);
                for ($position = 1; $position <= $booking->players; $position++) {
                    ReservationParticipant::updateOrCreate(['reservation_id' => $booking->id, 'position' => $position], [
                        'user_id' => $position === 1 ? $client->id : null,
                        'display_name' => $position === 1 ? $client->name : 'Participante demo '.$position,
                    ]);
                }
                ReservationEvent::firstOrCreate(['reservation_id' => $booking->id, 'action' => 'demo_imported', 'occurred_at' => '2026-10-10 12:00:00'], [
                    'actor_id' => $admin->id, 'reason' => 'Registro ficticio de demostración del modelo', 'after_values' => ['status' => $booking->status, 'players' => $booking->players, 'total_cents' => $booking->total_cents],
                ]);
            }
            $completed = $bookings['completed'];
            foreach ($completed->gameSession->preparationChecks as $check) {
                $item = $template->items()->where('label', $check->label)->firstOrFail();
                $check->update(['preparation_template_item_id' => $item->id]);
            }
            $completed->update(['checked_in_at' => '2026-10-15 16:55:00', 'checked_in_by' => $master->id]);
            GameResult::where('game_session_id', $completed->game_session_id)->firstOrFail()->update(['reservation_id' => $completed->id]);

            $resolved = Incident::where('title', 'Sensor demo revisado')->firstOrFail();
            $critical = Incident::where('title', 'Bloqueo técnico ficticio')->firstOrFail();
            AvailabilityBlock::updateOrCreate(['incident_id' => $resolved->id], [
                'room_id' => $resolved->room_id, 'user_id' => null, 'created_by' => $master->id, 'starts_at' => '2026-10-15 18:30:00', 'ends_at' => '2026-10-15 20:00:00',
                'released_at' => '2026-10-15 20:00:00', 'released_by' => $technician->id, 'reason' => 'Revisión técnica ficticia finalizada', 'kind' => 'maintenance',
            ]);
            AvailabilityBlock::updateOrCreate(['incident_id' => $critical->id], [
                'room_id' => $critical->room_id, 'user_id' => null, 'created_by' => $master->id, 'starts_at' => '2026-10-16 09:00:00', 'ends_at' => null,
                'released_at' => null, 'released_by' => null, 'reason' => 'Bloqueo ficticio pendiente de resolver', 'kind' => 'maintenance',
            ]);
            IncidentImpact::updateOrCreate(['reservation_id' => $completed->id, 'incident_id' => $resolved->id], [
                'detected_at' => '2026-10-15 18:30:00', 'reviewed_by' => $admin->id, 'reviewed_at' => '2026-10-15 20:05:00',
                'status' => 'reviewed', 'resolution_note' => 'Demostración: incidencia posterior a la partida, sin cambio de reserva.',
            ]);
            foreach (['deposit' => 4000, 'balance' => 6000] as $reference => $amount) {
                PaymentTransaction::updateOrCreate(['reference' => 'DEMO-'.$reference], [
                    'reservation_id' => $completed->id, 'gift_voucher_id' => null, 'parent_id' => null, 'recorded_by' => $admin->id,
                    'kind' => 'receipt', 'amount_cents' => $amount, 'currency' => 'EUR', 'method' => 'cash', 'status' => 'settled',
                    'paid_at' => '2026-10-15 16:55:00', 'notes' => 'Importe ficticio; no establece condiciones de cobro del negocio.',
                ]);
            }
            $cancelled = $bookings['cancelled'];
            $receipt = PaymentTransaction::updateOrCreate(['reference' => 'DEMO-cancelled-receipt'], [
                'reservation_id' => $cancelled->id, 'gift_voucher_id' => null, 'parent_id' => null, 'recorded_by' => $admin->id,
                'kind' => 'receipt', 'amount_cents' => 10000, 'currency' => 'EUR', 'method' => 'card', 'status' => 'settled', 'paid_at' => '2026-10-10 12:00:00', 'notes' => 'Cobro ficticio.',
            ]);
            PaymentTransaction::updateOrCreate(['reference' => 'DEMO-cancelled-refund'], [
                'reservation_id' => $cancelled->id, 'gift_voucher_id' => null, 'parent_id' => $receipt->id, 'recorded_by' => $admin->id,
                'kind' => 'refund', 'amount_cents' => 10000, 'currency' => 'EUR', 'method' => 'card', 'status' => 'settled', 'paid_at' => '2026-10-11 12:00:00', 'notes' => 'Devolución ficticia; no establece política comercial.',
            ]);

            foreach (['confirmed' => 'confirmation', 'pending' => 'reminder'] as $demoKey => $purpose) {
                NotificationDelivery::updateOrCreate(['idempotency_key' => 'demo-notification-'.$demoKey], [
                    'user_id' => $client->id, 'reservation_id' => $bookings['confirmed']->id, 'purpose' => 'reservation_'.$purpose,
                    'channel' => 'email', 'recipient' => 'cliente@example.test', 'locale' => 'es', 'status' => 'scheduled', 'scheduled_for' => '2026-10-16 12:00:00', 'attempts' => 0,
                ]);
            }
            GeneratedDocument::firstOrCreate(['reference' => 'DEMO-receipt'], [
                'owner_id' => $client->id, 'requested_by' => $client->id, 'reservation_id' => $completed->id,
                'kind' => 'reservation_receipt', 'format' => 'pdf', 'locale' => 'es', 'status' => 'queued', 'parameters' => ['demo' => true],
            ]);
            GeneratedDocument::firstOrCreate(['reference' => 'DEMO-monthly-report'], [
                'owner_id' => $admin->id, 'requested_by' => $admin->id, 'reservation_id' => null,
                'kind' => 'monthly_report', 'format' => 'pdf', 'locale' => 'es', 'status' => 'queued', 'parameters' => ['month' => '2026-10', 'demo' => true],
            ]);

            // Optional extensions: only example records; no issuance, checkout or redemption workflow.
            Review::firstOrCreate(['reservation_id' => $completed->id], [
                'user_id' => $client->id, 'rating' => 5, 'comment' => 'Valoración ficticia de demostración.', 'status' => 'pending',
            ]);
            $voucher = GiftVoucher::firstOrCreate(['code_hash' => hash('sha256', 'DEMO-NO-VALIDO-COMERCIALMENTE')], [
                'experience_id' => $experience->id, 'purchased_by' => $client->id, 'issued_by' => $admin->id,
                'kind' => 'amount', 'amount_cents' => 10000, 'players' => null, 'currency' => 'EUR', 'issued_at' => '2026-10-01 12:00:00',
                'expires_at' => null, 'recipient_name' => 'Destinatario ficticio', 'message' => 'Bono demo; vigencia comercial pendiente de definir.',
            ]);
            GiftVoucherRedemption::firstOrCreate(['gift_voucher_id' => $voucher->id], [
                'reservation_id' => $bookings['pending']->id, 'redeemed_by' => $client->id, 'redeemed_at' => '2026-10-10 12:00:00', 'applied_cents' => 10000,
            ]);
        });
    }
}
