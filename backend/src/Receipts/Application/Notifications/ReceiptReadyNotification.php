<?php

namespace MunicipalSaas\Receipts\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ReceiptReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $folio,
        private readonly string $downloadUrl,
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Recibo municipal disponible')
            ->line("Tu recibo {$this->folio} ya esta disponible.")
            ->action('Descargar recibo', $this->downloadUrl);
    }
}
