<?php
namespace Oxalis\Mail;

use Illuminate\Mail\Mailable;

class LoginNotificationMail extends Mailable
{
    public function __construct(
        public readonly string $method,
        public readonly string $ip,
        public readonly string $userAgent,
        public readonly string $time,
    ) {}

    public function build(): static
    {
        return $this->subject('New sign-in to ' . config('app.name'))
                    ->view('oxalis::emails.login-notification');
    }
}
