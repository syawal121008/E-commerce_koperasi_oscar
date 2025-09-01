<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

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
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Gunakan URL publik untuk logo
        $logoUrl = asset('images/smk.png');

        return (new MailMessage)
            ->subject('Permintaan Reset Password')
            ->view('emails.custom_reset', [
                'url' => $url,
                'user' => $notifiable,
                'logoUrl' => $logoUrl, // kirim ke Blade
            ]);
    }
}
