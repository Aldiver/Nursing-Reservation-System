<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationNotification extends Notification
{
    use Queueable;
    private $user;
    private $owner;
    private $notif_title;
    private $notif_message;
    private $reservation_id;
    private $notif_type;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $owner, $reservation_id, $notif_title = "Reservation Notification", $notif_message = "Notification Message", $notif_type = "")
    {
        $this->user = $user;
        $this->owner = $owner;
        $this->reservation_id = $reservation_id;
        $this->notif_title = $notif_title;
        $this->notif_message = $notif_message;
        $this->notif_type = $notif_type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->notif_title,
            'body'  => $this->notif_message,
            'reservation' => $this->reservation_id,
            'owner' => $this->owner,
            'notif_type' => $this->notif_type,
        ];
    }
}
