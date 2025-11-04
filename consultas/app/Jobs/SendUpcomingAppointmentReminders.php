<?php

namespace App\Jobs;

use App\Mail\AppointmentReminderMail;
use App\Models\Cita;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\SmsService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendUpcomingAppointmentReminders implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $uniqueFor = 900;

    public function handle(SmsService $sms): void
    {
        $windowStart = CarbonImmutable::now();
        $windowEnd = $windowStart->addDay();

        $appointments = Cita::query()
            ->with(['paciente.user.notificationPreference', 'medico.user.notificationPreference', 'especialidad'])
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [$windowStart, $windowEnd])
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('starts_at')
            ->get();

        $appointments->each(function (Cita $cita) use ($sms, $windowStart): void {
            $this->notifyParticipant($cita, $cita->paciente?->user, 'paciente', $sms);
            $this->notifyParticipant($cita, $cita->medico?->user, 'medico', $sms);

            $cita->forceFill(['reminder_sent_at' => $windowStart])->save();
        });
    }

    protected function notifyParticipant(Cita $cita, ?User $user, string $role, SmsService $sms): void
    {
        if (!$user) {
            return;
        }

        $preference = $user->notificationPreference;
        $startsAt = $cita->starts_at;
        $endsAt = $cita->ends_at;

        $payload = [
            'recipient_name' => $user->name,
            'role' => $role,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'especialidad' => $cita->especialidad?->nombre,
            'medico_nombre' => $cita->medico?->user?->name,
            'paciente_nombre' => $cita->paciente?->user?->name,
            'app_name' => config('app.name'),
        ];

        if ($this->shouldSendEmail($preference)) {
            Mail::to($user->email)->send(new AppointmentReminderMail($payload));
        }

        $smsNumber = $this->resolveSmsNumber($user, $preference);
        if ($smsNumber && $this->shouldSendSms($preference)) {
            $sms->send($smsNumber, $this->buildSmsMessage($payload), [
                'role' => $role,
                'user_id' => $user->id,
                'cita_id' => $cita->id,
            ]);
        }
    }

    protected function shouldSendEmail(?NotificationPreference $preference): bool
    {
        return $preference?->email_opt_in ?? true;
    }

    protected function shouldSendSms(?NotificationPreference $preference): bool
    {
        return (bool) ($preference?->sms_opt_in ?? false);
    }

    protected function resolveSmsNumber(User $user, ?NotificationPreference $preference): ?string
    {
        return $preference?->sms_number ?: $user->phone;
    }

    protected function buildSmsMessage(array $payload): string
    {
        $startsAt = $payload['starts_at'];
        $especialidad = $payload['especialidad'] ?: 'consulta';

        if ($startsAt instanceof CarbonImmutable) {
            $formattedDate = $startsAt->locale(config('app.locale'))->isoFormat('DD/MM HH:mm');
        } else {
            $formattedDate = optional($startsAt)->format('d/m H:i');
        }

        return sprintf(
            'Recordatorio de %s el %s. Responde a %s si necesitas reprogramar.',
            $especialidad,
            $formattedDate,
            $payload['app_name']
        );
    }
}