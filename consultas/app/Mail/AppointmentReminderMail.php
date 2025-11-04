<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function envelope(): Envelope
    {
        $subjectParts = array_filter([
            'Recordatorio de cita',
            $this->payload['especialidad'] ?? null,
        ]);

        return new Envelope(
            subject: implode(' · ', $subjectParts),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointments.reminder',
            with: $this->payload,
        );
    }
}