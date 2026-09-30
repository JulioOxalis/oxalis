<?php
namespace Oxalis\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly int $expiresInMinutes,
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Your one-time code')
            ->view('oxalis::emails.otp');
    }
}
