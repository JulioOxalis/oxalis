<?php
namespace Oxalis\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MagicLinkMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly string $url,
        public readonly int $expiresInMinutes,
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Your sign-in link')
            ->view('oxalis::emails.magic-link');
    }
}
