<?php

namespace App\Mail;

use App\Models\DynamicForm;
use App\Models\DynamicFormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DynamicFormReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;
    public $form;
    public $replyMessage;
    public $formData;

    /**
     * Create a new message instance.
     */
    public function __construct(DynamicFormSubmission $submission, DynamicForm $form, $replyMessage, $formData = null)
    {
        $this->submission = $submission;
        $this->form = $form;
        $this->replyMessage = $replyMessage;

        // ✅ Use passed data or decode from submission
        if ($formData !== null) {
            $this->formData = $formData;
        } else {
            $data = $submission->form_data;
            if (is_string($data)) {
                $data = json_decode($data, true) ?? [];
            }
            $this->formData = is_array($data) ? $data : [];
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reply to Your Enquiry - ' . $this->form->name,
            from: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.dynamic-form-reply',
            with: [
                'submission' => $this->submission,
                'form' => $this->form,
                'replyMessage' => $this->replyMessage,
                'formData' => $this->formData,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
