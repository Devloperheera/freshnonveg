<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
        $this->invoice->loadMissing('user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received — ' . $this->invoice->invoice_number . ' (₹' . number_format((float) $this->invoice->total, 2) . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-payment-admin',
        );
    }
}
