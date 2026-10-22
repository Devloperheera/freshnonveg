<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlanBookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;
    public $plan;
    public $form;
    public $formData;

    public function __construct($submission, $plan, $form, $formData)
    {
        $this->submission = $submission;
        $this->plan = $plan;
        $this->form = $form;
        $this->formData = $formData;
    }

    public function build()
    {
        return $this->subject('Booking Confirmation - ' . $this->plan->name)
                    ->view('emails.plan-booking-user')
                    ->with([
                        'submission' => $this->submission,
                        'plan' => $this->plan,
                        'form' => $this->form,
                        'formData' => $this->formData,
                    ]);
    }
}
