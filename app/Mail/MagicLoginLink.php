<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLoginLink extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public string $url){}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Login Link to ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.magic-login-link',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
