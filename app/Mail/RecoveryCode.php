<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoveryCode extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(private $pin_code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recovery Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.recovery',
            with: ['pin_code' => $this->pin_code]
        );
    }
}
