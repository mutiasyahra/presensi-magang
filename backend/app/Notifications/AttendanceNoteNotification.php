<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceNoteNotification extends Notification
{
    use Queueable;

    protected $attendance;
    protected $note;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($attendance, $note)
    {
        $this->attendance = $attendance;
        $this->note = $note;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'attendance_id' => $this->attendance->id,
            'attendance_date' => $this->attendance->attendance_date,
            'message' => 'Admin memberikan catatan untuk presensi Anda: "' . $this->note . '"',
            'type' => 'review_note'
        ];
    }
}
