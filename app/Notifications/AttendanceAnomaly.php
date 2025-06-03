<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceAnomaly extends Notification
{
    use Queueable;

    protected $employee;
    protected $date;
    protected $anomalyType;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($employee, $date, $anomalyType)
    {
        $this->employee = $employee;
        $this->date = $date;
        $this->anomalyType = $anomalyType;
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
            'employee_name' => $this->employee->firstname . ' ' . $this->employee->lastname,
            'date' => $this->date,
            'anomaly_type' => $this->anomalyType,
            'message' => 'Attendance anomaly detected: ' . $this->anomalyType . ' on ' . $this->date,
            'url' => url('/attendance'),
        ];
    }
}
