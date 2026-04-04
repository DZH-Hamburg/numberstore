<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorLoginCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Dein Login-Code'))
            ->line(__('Dein Bestätigungscode für die Anmeldung lautet: :code', ['code' => $this->code]))
            ->line(__('Der Code ist 10 Minuten gültig. Wenn du diese Anmeldung nicht angefordert hast, ignoriere diese E-Mail.'));
    }
}
