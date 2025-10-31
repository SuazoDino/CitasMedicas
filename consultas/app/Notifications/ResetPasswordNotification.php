<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     */
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $baseUrl = config('app.frontend_url')
            ?? config('app.url')
            ?? env('APP_URL', 'http://localhost:5173');

        $resetUrl = rtrim($baseUrl, '/') . '/reset-password?token=' . urlencode($this->token) .
            '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject(__('Restablecer contraseña'))
            ->line(__('Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta.'))
            ->action(__('Restablecer contraseña'), $resetUrl)
            ->line(__('Si no realizaste esta solicitud, puedes ignorar este mensaje.'));
    }
}