<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public bool $isUserMail = false
    ) {
    }

    public function envelope(): Envelope
    {
        if ($this->isUserMail) {
            return new Envelope(
                subject: 'Ticket Received — ' . $this->ticket->ticket_number . ' | FreshNonVeg',
            );
        }

        return new Envelope(
            subject: 'New Support Ticket — ' . $this->ticket->ticket_number,
            replyTo: [$this->ticket->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->isUserMail ? 'emails.ticket-user' : 'emails.ticket-admin',
        );
    }
}
