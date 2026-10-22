<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $name,
        public string $purpose = 'register'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->purpose) {
            'login' => 'Your Login OTP - FreshNonVeg',
            'reset' => 'Password Reset OTP - FreshNonVeg',
            default => 'Verify Your Email OTP - FreshNonVeg',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.user-otp');
    }

    public function attachments(): array
    {
        return [];
    }
}
