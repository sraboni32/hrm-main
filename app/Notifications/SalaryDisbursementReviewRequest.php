<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SalaryDisbursementReviewRequest extends Notification
{
    use Queueable;

    protected $disbursement;

    public function __construct($disbursement)
    {
        $this->disbursement = $disbursement;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'salary_disbursement_id' => $this->disbursement->id,
            'month' => $this->disbursement->month,
            'net_payable' => $this->disbursement->net_payable,
            'message' => 'Your salary for ' . $this->disbursement->month . ' is ready for review.',
            'url' => url('/my-salary-disbursements'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
} 