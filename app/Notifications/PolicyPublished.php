<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PolicyPublished extends Notification
{
    use Queueable;

    protected $policy;
    protected $publisher;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($policy, $publisher)
    {
        $this->policy = $policy;
        $this->publisher = $publisher;
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
            'policy_id' => $this->policy->id,
            'title' => $this->policy->title,
            'summary' => $this->policy->summary,
            'published_by' => $this->publisher->name ?? 'Admin',
            'message' => 'A new policy has been published: ' . $this->policy->title,
            'url' => url('/policies/' . $this->policy->id),
        ];
    }
}
