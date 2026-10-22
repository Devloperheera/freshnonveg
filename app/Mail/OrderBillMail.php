<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderBillMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                (string) config('mail.from.address'),
                'FreshNonVeg'
            ),
            subject: 'Aapka order '.$this->order->order_number.' confirm — FreshNonVeg bill',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-bill',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
