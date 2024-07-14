<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public $user;
    public $owner;
    public $title;
    public $message;
    public $reservation_id;
    // public $
    public $notif_type;

    public function __construct($user, $owner, $reservation_id, $eventType)
    {
        $this->user = $user;
        $this->owner = $owner;
        $this->reservation_id = $reservation_id;
        $this->notif_type = $eventType;
        $this->setEventDetails($eventType);
    }

    /**
     * Set the title and message based on the event type.
     *
     * @param string $eventType
     * @return void
     */
    private function setEventDetails(string $eventType)
    {
        switch ($eventType) {
            case 'created':
                $this->title = "Reservation Created: ID[$this->reservation_id]";
                $this->message = "$this->owner created a new reservation. Please note this reservation for further action.";
                break;
            case 'noted':
                $this->title = "Reservation Noted: ID[$this->reservation_id]";
                $this->message = "The reservation has been noted and is recommended for your approval.";
                break;
            case 'notify_noted':
                $this->title = "Reservation Noted: ID[$this->reservation_id]";
                $this->message = "The reservation has been noted and is awaiting approval.";
                break;
            case 'updated':
                $this->title = "Reservation Updated: ID[$this->reservation_id]";
                $this->message = "Your reservation has been revised.";
                break;
            case 'approved':
                $this->title = "Reservation Approved: ID[$this->reservation_id]";
                $this->message = "Your reservation has been approved.";
                break;
            case 'conflict':
                $this->title = "Reservation Conflict Detected: ID[$this->reservation_id]";
                $this->message = "A conflict has been detected with your reservation. Please review the details.";
                break;
            case 'deleted':
                $this->title = "Reservation Deleted: ID[$this->reservation_id]";
                $this->message = "Your reservation has been deleted. Contact administrator or create a new one.";
                break;
            default:
                $this->title = "";
                $this->message = "";
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
