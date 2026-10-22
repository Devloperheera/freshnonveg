<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;
    public $isUserMail;

    /**
     * Create a new message instance.
     */
    public function __construct(ContactSubmission $submission, $isUserMail = false)
    {
        $this->submission = $submission;
        $this->isUserMail = $isUserMail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->isUserMail) {
            return new Envelope(
                subject: 'Thank You for Contacting FreshNonVeg',
                from: config('mail.from.address'),
            );
        }

        return new Envelope(
            subject: 'New Contact Enquiry - FreshNonVeg',
            from: config('mail.from.address'),
            replyTo: $this->submission->email ? [$this->submission->email] : null,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->isUserMail ? 'emails.contact-user' : 'emails.contact-admin',
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