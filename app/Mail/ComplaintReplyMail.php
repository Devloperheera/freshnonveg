<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComplaintReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Complaint $complaint, public float $creditedNow = 0) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), 'FreshNonVeg'),
            subject: 'Aapki complaint #'.$this->complaint->id.' ka jawab — FreshNonVeg',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'Emails.complaint-reply');
    }
}
