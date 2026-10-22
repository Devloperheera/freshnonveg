<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $replyMessage
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reply to Ticket ' . $this->ticket->ticket_number . ' | FreshNonVeg',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-reply',
        );
    }
}
