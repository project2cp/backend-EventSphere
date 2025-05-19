<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $eventData;

    public function __construct($eventData)
    {
        $this->eventData = $eventData;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->eventData['title'],
            'message' => $this->eventData['message'],
            'event_id' => $this->eventData['event_id'],
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->eventData['title'],
            'message' => $this->eventData['message'],
            'event_id' => $this->eventData['event_id'],
        ]);
    }
}
