<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusChanged extends Notification
{
    use Queueable;

    protected $leave;
    protected $status;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($leave, $status)
    {
        $this->leave = $leave;
        $this->status = $status;
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
            'leave_id' => $this->leave->id,
            'status' => $this->status,
            'start_date' => $this->leave->start_date,
            'end_date' => $this->leave->end_date,
            'message' => 'Your leave request from ' . $this->leave->start_date . ' to ' . $this->leave->end_date . ' has been ' . $this->status . '.',
            'url' => url('/leave'),
        ];
    }
}
