<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceCorrected extends Notification
{
    use Queueable;

    protected $attendance;
    protected $correctedBy;
    protected $correctionDetails;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($attendance, $correctedBy, $correctionDetails)
    {
        $this->attendance = $attendance;
        $this->correctedBy = $correctedBy;
        $this->correctionDetails = $correctionDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
            'date' => $this->attendance->date,
            'correction' => $this->correctionDetails,
            'corrected_by' => $this->correctedBy->name ?? 'Admin',
            'message' => 'Your attendance for ' . $this->attendance->date . ' was corrected.',
            'url' => url('/attendance/' . $this->attendance->id),
        ];
    }
}
