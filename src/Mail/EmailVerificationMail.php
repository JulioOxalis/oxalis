<?php
namespace Oxalis\Mail;

use Illuminate\Mail\Mailable;

class EmailVerificationMail extends Mailable
{
    public function __construct(public readonly string $url) {}

    public function build(): static
    {
        return $this->subject('Verify your email address')
                    ->view('oxalis::emails.verify-email');
    }
}
