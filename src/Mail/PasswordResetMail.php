<?php
namespace Oxalis\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly string $url) {}

    public function build(): static
    {
        return $this->subject('Reset your password')->view('oxalis::emails.password-reset');
    }
}
