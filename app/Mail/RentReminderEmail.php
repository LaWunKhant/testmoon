<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RentReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;

    public $rentPayment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($tenant, $rentPayment)
    {
        $this->tenant = $tenant;
        $this->rentPayment = $rentPayment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.rent_reminder')  // Make sure this view exists!
            ->with([
                'tenantName' => $this->tenant->name, // Pass the tenant's name to the view
                'amountDue' => $this->rentPayment->amount,
                'dueDate' => $this->rentPayment->due_date,
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rent Reminder',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.rent_reminder',  // Corrected and consistent view name.
            with: [
                'tenant' => $this->tenant,
                'rentPayment' => $this->rentPayment,
            ],
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
