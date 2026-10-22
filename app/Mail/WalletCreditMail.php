<?php

namespace App\Mail;

use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WalletCreditMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WalletTransaction $txn, public ?string $note = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), 'FreshNonVeg'),
            subject: '₹'.number_format((float) $this->txn->amount, 2).' aapke FreshNonVeg wallet me add hue',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'Emails.wallet-credit');
    }
}
