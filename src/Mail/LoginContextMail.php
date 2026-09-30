<?php
namespace Oxalis\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginContextMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $method,
        public readonly string $ip,
        public readonly string $userAgent,
        public readonly int    $riskScore,
        public readonly string $timestamp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' — Sign-in from new device or location',
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'oxalis::emails.login-context');
    }
}
