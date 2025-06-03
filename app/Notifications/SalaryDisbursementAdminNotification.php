<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryDisbursementAdminNotification extends Notification
{
    use Queueable;

    protected $salaryDisbursement;
    protected $reviewStatus;
    protected $employeeName;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($salaryDisbursement, $reviewStatus, $employeeName)
    {
        $this->salaryDisbursement = $salaryDisbursement;
        $this->reviewStatus = $reviewStatus;
        $this->employeeName = $employeeName;
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
                    ->line('A salary disbursement has been reviewed by an employee.')
                    ->action('View Salary Disbursements', url('/salary-disbursements'))
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
        $message = '';
        $priority = 'normal';

        if ($this->reviewStatus === 'reviewed') {
            $message = $this->employeeName . ' has approved their salary for ' . $this->salaryDisbursement->month . '. Net Payable: ' . number_format($this->salaryDisbursement->net_payable, 2);
            $priority = 'normal';
        } elseif ($this->reviewStatus === 'feedback') {
            $message = $this->employeeName . ' has provided feedback on their salary for ' . $this->salaryDisbursement->month . '. Requires attention.';
            $priority = 'high';
        }

        return [
            'salary_disbursement_id' => $this->salaryDisbursement->id,
            'employee_name' => $this->employeeName,
            'employee_id' => $this->salaryDisbursement->employee_id,
            'amount' => $this->salaryDisbursement->net_payable,
            'month' => $this->salaryDisbursement->month,
            'review_status' => $this->reviewStatus,
            'feedback' => $this->salaryDisbursement->feedback,
            'priority' => $priority,
            'message' => $message,
            'url' => url('/report/monthly-salary-disbursement-report'),
            'type' => 'salary_review',
        ];
    }
}
