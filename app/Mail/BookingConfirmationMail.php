<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;
    public $form;
    public $formData;
    public $isUser;

    public function __construct($submission, $form, $formData, $isUser = false)
    {
        $this->submission = $submission;
        $this->form = $form;
        $this->formData = $formData;
        $this->isUser = $isUser;
    }

    public function build()
    {
        $subject = $this->isUser
            ? 'Booking Confirmation - ' . $this->form->name
            : 'New Booking Received - ' . $this->form->name;

        // Use different views for user and admin
        $view = $this->isUser
            ? 'emails.booking-confirmation-user'
            : 'emails.booking-confirmation-admin';

        return $this->subject($subject)
                    ->view($view)
                    ->with([
                        'submission' => $this->submission,
                        'form' => $this->form,
                        'formData' => $this->formData,
                        'isUser' => $this->isUser,
                    ]);
    }
}
