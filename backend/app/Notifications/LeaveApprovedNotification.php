<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveApprovedNotification extends Notification
{
    use Queueable;

    protected $leave;

    public function __construct($leave)
    {
        $this->leave = $leave;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Pengajuan ' . $this->leave->type . 
                        ' tanggal ' . $this->leave->date . 
                        ' telah ' . $this->leave->status,
            'leave_id' => $this->leave->id
        ];
    }
}