<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OverduePaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The tenant instance.
     *
     * @var \App\Models\Tenant
     */
    public $tenant;

    /**
     * The collection of overdue payments.
     *
     * @var \Illuminate\Support\Collection
     */
    public $payments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Collection $payments)
    {
        $this->tenant = $tenant;
        $this->payments = $payments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Calculate total amount due
        $totalDue = $this->payments->sum('amount');

        // Get the oldest and newest overdue payments
        $oldestDue = $this->payments->sortBy('due_date')->first();
        $daysOverdue = now()->diffInDays($oldestDue->due_date);

        return $this->subject('URGENT: Overdue Rent Payment Notice')
            ->view('emails.overdue_payment_reminder')
            ->with([
                'tenantName' => $this->tenant->name,
                'payments' => $this->payments,
                'totalDue' => $totalDue,
                'daysOverdue' => $daysOverdue,
                'propertyAddress' => $this->tenant->house->address ?? 'Your rented property',
            ]);
    }
}
