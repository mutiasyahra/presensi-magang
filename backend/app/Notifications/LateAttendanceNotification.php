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
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
                    ->error()
                    ->subject('Intern Attendance Alert: Late Arrival Detected')
                    ->line('The following intern(s) are more than 15 minutes late today:');

        foreach ($this->interns as $intern) {
            $mail->line('- ' . $intern->name . ' (' . $intern->email . ')');
        }

        $mail->action('View Live Monitor', url('/admin?tab=live'))
             ->line('Please check the dashboard for more details.');

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'intern_count' => count($this->interns),
        ];
    }
}
