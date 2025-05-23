<?php

namespace App\Mail;

use App\Models\House;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // *** Import Storage Facade ***

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
     * Total amount due across all overdue payments.
     *
     * @var float
     */
    public $totalDue;

    /**
     * Number of days overdue for the oldest payment.
     *
     * @var int
     */
    public $daysOverdue;

    /**
     * The property address (optional, based on how house is linked).
     *
     * @var string|null
     */
    public $propertyAddress;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Collection $payments)
    {
        $this->tenant = $tenant;
        $this->payments = $payments;

        // Calculate total amount due and days overdue in the constructor
        $this->totalDue = $this->payments->sum('amount');

        // Calculate days overdue based on the oldest payment's due date
        $oldestDue = $this->payments->sortBy('due_date')->first();
        $this->daysOverdue = $oldestDue ? now()->diffInDays($oldestDue->due_date) : 0; // Handle case with no payments

        // Try to get the property address from the tenant if the relationship exists and is loaded
        // Assumes 'tenant' model has a 'house' relationship eager loaded in the command query
        $this->propertyAddress = $this->tenant->house->address ?? 'Your rented property';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'URGENT: Overdue Rent Payment Notice',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.overdue_payment_reminder', // The Markdown view for email body
            with: [ // Pass data to the Markdown view
                'tenant' => $this->tenant,
                'payments' => $this->payments,
                'totalDue' => $this->totalDue,
                'daysOverdue' => $this->daysOverdue,
                'propertyAddress' => $this->propertyAddress,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        try {
            // Generate the PDF from the overdue notice view
            $pdf = Pdf::loadView('pdfs.overdue_notice', [
                'tenant' => $this->tenant,
                'payments' => $this->payments,
                'totalDue' => $this->totalDue,
                'daysOverdue' => $this->daysOverdue,
                'propertyAddress' => $this->propertyAddress,
            ]);

            // Generate a unique filename for the PDF
            $filename = 'overdue_notice_'.$this->tenant->id.'_'.Str::random(8).'.pdf'; // Use tenant ID for filename

            // Save the PDF to a temporary location (e.g., storage/app/temp)
            // Ensure the storage/app/temp directory exists and is writable
            $tempPath = storage_path('app/temp/'.$filename);

            // Ensure the storage/app/temp directory exists before saving
            if (! Storage::exists('temp')) { // Use Storage Facade directly
                Storage::makeDirectory('temp');
            }

            $pdf->save($tempPath); // Save the PDF to the temporary path

            // Return the attachment from the temporary file path
            return [
                Attachment::fromPath($tempPath) // Attach from the saved temporary file path
                    ->as('OverdueNotice-'.$this->tenant->id.'.pdf') // Friendly filename in email
                    ->withMime('application/pdf'),
            ];

        } catch (\Exception $e) {
            // Log any errors during PDF generation or attachment
            Log::error('Error generating or attaching PDF overdue notice (fromPath): '.$e->getMessage());
            Log::error('PDF Exception Trace: '.$e->getTraceAsString());

            // Return an empty array if PDF generation/attachment fails
            return [];
        }
    }
}
