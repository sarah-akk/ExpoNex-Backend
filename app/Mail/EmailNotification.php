<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private array $atters)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->atters['title'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.letter',
            with: ['atters' => $this->atters]
        );
    }
}
