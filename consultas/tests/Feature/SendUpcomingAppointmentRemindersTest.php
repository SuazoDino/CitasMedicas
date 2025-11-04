<?php

namespace Tests\Feature;

use App\Jobs\SendUpcomingAppointmentReminders;
use App\Mail\AppointmentReminderMail;
use App\Models\Cita;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendUpcomingAppointmentRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_notifications_for_upcoming_appointments(): void
    {
        Mail::fake();
        config(['sms.driver' => 'array']);

        $sms = app(SmsService::class);
        $sms->reset();

        $pacienteUser = User::factory()->create(['email' => 'paciente@example.test', 'phone' => '+51999111222']);
        $medicoUser = User::factory()->create(['email' => 'medico@example.test', 'phone' => '+51999113333']);

        $pacienteId = DB::table('pacientes')->insertGetId([
            'user_id' => $pacienteUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $medicoId = DB::table('medicos')->insertGetId([
            'user_id' => $medicoUser->id,
            'id_doc_tipo' => 'DNI',
            'id_doc_numero' => '87654321',
            'lic_tipo' => 'CMP',
            'lic_numero' => '12345678',
            'lic_pais' => 'PE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $especialidadId = DB::table('especialidades')->insertGetId([
            'nombre' => 'Cardiología',
            'slug' => 'cardiologia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificationPreference::create([
            'user_id' => $pacienteUser->id,
            'sms_opt_in' => true,
            'sms_number' => '+51999111222',
            'push_opt_in' => false,
        ]);

        NotificationPreference::create([
            'user_id' => $medicoUser->id,
            'sms_opt_in' => true,
            'sms_number' => '+51999113333',
            'push_opt_in' => false,
        ]);

        $citaId = DB::table('citas')->insertGetId([
            'medico_id' => $medicoId,
            'paciente_id' => $pacienteId,
            'especialidad_id' => $especialidadId,
            'starts_at' => now()->addHours(6),
            'ends_at' => now()->addHours(7),
            'estado' => 'confirmada',
            'created_by_user_id' => $pacienteUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $job = new SendUpcomingAppointmentReminders();
        $job->handle($sms);

        Mail::assertSent(AppointmentReminderMail::class, function ($mail) use ($pacienteUser) {
            return $mail->hasTo($pacienteUser->email);
        });

        Mail::assertSent(AppointmentReminderMail::class, function ($mail) use ($medicoUser) {
            return $mail->hasTo($medicoUser->email);
        });

        $this->assertCount(2, $sms->sentMessages());

        $this->assertNotNull(Cita::find($citaId)->reminder_sent_at);
    }
} 