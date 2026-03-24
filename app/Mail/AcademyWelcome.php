<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcademyWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public $app;
    /**
     * Create a new message instance.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ITcloud Academy: Arizangiz qabul qilindi!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.academy.welcome',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
