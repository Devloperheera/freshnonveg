<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlanBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;
    public $plan;
    public $form;
    public $formData;

    /**
     * Create a new message instance.
     */
    public function __construct($submission, $plan, $form, $formData)
    {
        $this->submission = $submission;
        $this->plan = $plan;
        $this->form = $form;
        $this->formData = $formData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Plan Booking Request - ' . $this->plan->name)
                    ->view('emails.plan-booking-admin')
                    ->with([
                        'submission' => $this->submission,
                        'plan' => $this->plan,
                        'form' => $this->form,
                        'formData' => $this->formData,
                    ]);
    }
}
