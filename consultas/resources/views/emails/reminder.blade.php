@php
    use Illuminate\Support\Carbon;

    $startsAt = $starts_at instanceof \Carbon\CarbonInterface ? $starts_at : Carbon::parse($starts_at);
    $endsAt = $ends_at instanceof \Carbon\CarbonInterface ? $ends_at : ($ends_at ? Carbon::parse($ends_at) : null);
@endphp

@component('mail::message')
# Recordatorio de cita

Hola **{{ $recipient_name }}**, este es un recordatorio automático para tu próxima cita.

@component('mail::panel')
- **Rol:** {{ ucfirst($role) }}
- **Especialidad:** {{ $especialidad ?? 'Consulta general' }}
- **Fecha:** {{ $startsAt->locale(app()->getLocale())->translatedFormat('d \d\e F \d\e Y') }}
- **Horario:** {{ $startsAt->format('H:i') }} {{ $endsAt ? '– '.$endsAt->format('H:i') : '' }}
- **Médico:** {{ $medico_nombre ?? 'Por confirmar' }}
- **Paciente:** {{ $paciente_nombre ?? 'Por confirmar' }}
@endcomponent

Si necesitas reprogramar o cancelar, ingresa a la plataforma cuanto antes para liberar el horario.

Gracias,
{{ $app_name ?? config('app.name') }}
@endcomponent