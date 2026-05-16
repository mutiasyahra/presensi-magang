<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LateInternNotification extends Notification
{
    use Queueable;

    protected $attendance;
    protected $internName;

    public function __construct($attendance, $internName)
    {
        $this->attendance = $attendance;
        $this->internName = $internName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->internName . ' telah melakukan presensi terlambat pada ' . $this->attendance->clock_in,
            'attendance_id' => $this->attendance->id,
            'attendance_date' => $this->attendance->attendance_date,
            'type' => 'late_intern'
        ];
    }
}
