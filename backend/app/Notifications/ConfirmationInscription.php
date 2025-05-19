<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmationInscription extends Notification
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     *
     * @param string $event Nom ou détails de l'événement
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirmation de votre inscription')
            ->greeting('Bonjour ' . $notifiable->name . ',') // Si le modèle User a un champ "name"
            ->line('Votre inscription à l\'événement "' . $this->event . '" a été confirmée.')
            ->line('Nous sommes ravis de vous compter parmi les participants.')
            ->action('Voir les détails de l\'événement', url('/events'))
            ->line('Merci de votre confiance !')
            ->salutation('Cordialement, l\'équipe.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
        ];
    }
}
