<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryDisbursed extends Notification
{
    use Queueable;

    protected $salaryDisbursement;
    protected $status;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($salaryDisbursement, $status = null)
    {
        $this->salaryDisbursement = $salaryDisbursement;
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
        $status = $this->status ?? $this->salaryDisbursement->status;
        $baseMsg = 'Your salary for ' . $this->salaryDisbursement->month . ' has been ';
        if ($status === 'approved') {
            $msg = $baseMsg . 'approved. Net Payable: ' . number_format($this->salaryDisbursement->net_payable, 2);
        } elseif ($status === 'paid') {
            $msg = $baseMsg . 'paid. Net Payable: ' . number_format($this->salaryDisbursement->net_payable, 2);
        } elseif ($status === 'updated') {
            $msg = $baseMsg . 'updated by admin. Please review the changes. Net Payable: ' . number_format($this->salaryDisbursement->net_payable, 2);
        } elseif ($status === 'sent_for_review') {
            $msg = $baseMsg . 'sent for your review. Net Payable: ' . number_format($this->salaryDisbursement->net_payable, 2);
        } else {
            $msg = $baseMsg . 'disbursed. Net Payable: ' . number_format($this->salaryDisbursement->net_payable, 2);
        }
        return [
            'salary_disbursement_id' => $this->salaryDisbursement->id,
            'amount' => $this->salaryDisbursement->net_payable,
            'month' => $this->salaryDisbursement->month,
            'status' => $status,
            'message' => $msg,
            'url' => url('/my-salary-disbursements'),
        ];
    }
}
