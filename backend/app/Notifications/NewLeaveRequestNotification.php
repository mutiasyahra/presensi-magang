<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLeaveRequestNotification extends Notification
{
    use Queueable;

    protected $leave;
    protected $internName;

    public function __construct($leave, $internName)
    {
        $this->leave = $leave;
        $this->internName = $internName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->internName . ' telah mengajukan ' . $this->leave->type . ' untuk tanggal ' . $this->leave->start_date,
            'leave_id' => $this->leave->id,
            'start_date' => $this->leave->start_date,
            'type' => 'new_leave_request'
        ];
    }
}
