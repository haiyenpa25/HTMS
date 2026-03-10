<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailBroadcast;
use App\Models\User;

class NewsletterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $broadcast;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailBroadcast $broadcast, User $user)
    {
        $this->broadcast = $broadcast;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->broadcast->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'content' => $this->broadcast->content,
                'userName' => $this->user->name,
                'logoUrl' => url('/favicon.ico')
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
