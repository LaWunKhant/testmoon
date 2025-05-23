<?php

namespace App\Mail;

use App\Models\House;
use App\Models\Payment;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable; // Import Attachment class
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope; // Import House model if including house in PDF
use Illuminate\Queue\SerializesModels; // *** Import the PDF Facade ***
use Illuminate\Support\Facades\Log; // Use Log for debugging within the Mailable if needed
use Illuminate\Support\Str; // Use Str for generating unique filenames

class PaymentReceivedConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;

    public $tenant;

    public $house; // Add public property for House

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, Tenant $tenant, ?House $house = null) // Accept Payment, Tenant, and optional House
    {
        $this->payment = $payment;
        $this->tenant = $tenant;
        $this->house = $house; // Assign House to public property
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received Confirmation - Receipt Attached', // Updated subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payments.received',
            with: [ // Pass data to the Markdown view if it needs it (optional if only used in PDF view)
                'payment' => $this->payment,
                'tenant' => $this->tenant,
                'house' => $this->house,
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
            // Generate the PDF from the payment receipt view
            $pdf = Pdf::loadView('pdfs.payment_receipt', [
                'payment' => $this->payment,
                'tenant' => $this->tenant,
                'house' => $this->house, // Pass data to the PDF view
            ]);

            // Generate a unique filename for the PDF
            $filename = 'payment_receipt_'.$this->payment->id.'_'.Str::random(8).'.pdf';

            // Save the PDF to a temporary location (e.g., storage/app/temp)
            // Ensure the storage/app/temp directory exists and is writable
            $tempPath = storage_path('app/temp/'.$filename);
            // Ensure the storage/app/temp directory exists
            if (! \Illuminate\Support\Facades\Storage::exists('temp')) {
                \Illuminate\Support\Facades\Storage::makeDirectory('temp');
            }

            $pdf->save($tempPath);

            // Return the attachment from the temporary file path
            return [
                Attachment::fromPath($tempPath)
                    ->as('PaymentReceipt-'.$this->payment->id.'.pdf') // Friendly filename in email
                    ->withMime('application/pdf'),
            ];

        } catch (\Exception $e) {
            // Log any errors during PDF generation or attachment
            Log::error('Error generating or attaching PDF payment receipt: '.$e->getMessage());
            Log::error('PDF Exception Trace: '.$e->getTraceAsString());

            // Return an empty array if PDF generation/attachment fails
            return [];
        }

        // Note: Laravel handles cleanup of temporary files created via storage_path() and attachments
        // after the email is sent, but explicit cleanup might be needed in some complex scenarios.
        // For files saved using $pdf->save(), you might need to manually delete them after dispatching the email.
        // A simpler approach might be to get the raw output and attach from data if file cleanup is an issue.
        // Let's refine the cleanup: Dispatching a Mailable often doesn't guarantee immediate file cleanup.
        // A job is better for this with a ->chain(new DeleteTemporaryFile($tempPath))

        // *** Alternative: Get raw PDF output and attach from data (simpler for cleanup) ***
        /*
        try {
             $pdf = Pdf::loadView('pdfs.payment_receipt', [
                'payment' => $this->payment,
                'tenant' => $this->tenant,
                'house' => $this->house,
             ]);

            $pdfContent = $pdf->output(); // Get raw PDF content

             return [
                Attachment::fromData($pdfContent, 'PaymentReceipt-' . $this->payment->id . '.pdf') // Attach raw content
                             ->withMime('application/pdf'),
             ];

        } catch (\Exception $e) {
             Log::error('Error generating or attaching PDF payment receipt from data: ' . $e->getMessage());
             return [];
        }
        */
    }
}
