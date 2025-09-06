<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;

class WelcomeEmailNotification extends Notification
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // $verificationUrl = url('/v1/admin/verify-email/' . $this->token);
        $verificationUrl = env('WEB_URL'). 'verify-email/' . $this->token;
        $userName = $notifiable->name ?? 'User';

        return (new MailMessage)
            ->view('email.register_mail', [
                'userName' => $userName,
                'verificationUrl' => $verificationUrl,
                'appName' => 'AI World',
            ])
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Welcome to ' . config('app.name') . ' - Verify Your Email');
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
