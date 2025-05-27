<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
// Removed: use Illuminate\Http\UploadedFile; // Not needed if no file attachments
use Illuminate\Mail\Mailable;
// Removed: use Illuminate\Mail\Mailables\Attachment; // Not needed if no file attachments
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomTenantEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;

    public $body;

    public $tenant;

    // Removed: public $attachments = []; // Not needed if no file attachments

    /**
     * Create a new message instance.
     */
    // Removed $uploadedFiles parameter from constructor
    public function __construct(string $subject, string $body, ?Tenant $tenant = null)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->tenant = $tenant;
        // Removed: $this->attachments = $uploadedFiles; // Not needed
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            // You might set the sender here based on the owner user (Auth::user()->email)
            // from: new Address(Auth::user()->email, Auth::user()->name),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenants.custom', // This view displays the email body
            with: [ // Pass data to the view
                'body' => $this->body,
                'tenant' => $this->tenant,
                // You might pass the owner user here too
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    // Removed the entire attachments() method
    public function attachments(): array
    {
        return []; // Return an empty array as there are no attachments
    }
}
