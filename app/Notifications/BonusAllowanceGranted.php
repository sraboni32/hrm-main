<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BonusAllowanceGranted extends Notification
{
    use Queueable;

    protected $bonusAllowance;
    protected $grantedBy;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($bonusAllowance, $grantedBy)
    {
        $this->bonusAllowance = $bonusAllowance;
        $this->grantedBy = $grantedBy;
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
        $amount = $this->bonusAllowance->type === 'percentage' 
            ? $this->bonusAllowance->amount . '%' 
            : number_format($this->bonusAllowance->amount, 2);

        return [
            'bonus_id' => $this->bonusAllowance->id,
            'amount' => $amount,
            'type' => $this->bonusAllowance->type,
            'description' => $this->bonusAllowance->description,
            'granted_by' => $this->grantedBy->name ?? 'Admin',
            'message' => 'You have been granted a ' . $this->bonusAllowance->type . ' bonus/allowance of ' . $amount,
            'url' => url('/hrm/bonus-allowance'),
        ];
    }
}
