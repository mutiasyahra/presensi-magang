<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LateAttendanceNotification extends Notification
{
    use Queueable;

    private $interns;

    public function __construct($interns)
    {
        $this->interns = $interns;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $names = collect($this->interns)->pluck('name')->implode(', ');
        return [
            'message' => count($this->interns) . ' pemagang terlambat lebih dari 15 menit hari ini: ' . $names,
            'type' => 'late_intern',
            'intern_count' => count($this->interns),
        ];
    }
}
