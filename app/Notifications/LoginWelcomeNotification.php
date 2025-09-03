<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginWelcomeNotification extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        // No parameters needed for this notification
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Get the website URL from config
        $websiteUrl = config('app.url');
        
        // Get the user's name from the notifiable object
        $userName = $notifiable->name ?? 'User';
       
        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Your InvestorMaxx Account Has Been Created')
            ->greeting('Hello ' . $userName . ',')
            ->line('A SuperAdmin has created your account in the InvestorMaxx System.')
            ->line('You can now access your account by logging in to the website.')
            ->action('Login to InvestorMaxx', $websiteUrl)
            ->line('If you have any questions, please contact your system administrator.')
            ->line('Thank you for using InvestorMaxx!');
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
            //
        ];
    }
}