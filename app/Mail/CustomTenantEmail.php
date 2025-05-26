<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels; // Ensure Tenant model is imported

class CustomTenantEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject; // Make subject public

    public $body;    // Make body public

    public $tenant;  // Make tenant public (useful for view if needed)

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $body, ?Tenant $tenant = null) // Accept subject, body, and optional tenant
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->tenant = $tenant;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject, // Use the subject from the constructor
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // You can use a simple text or markdown view here.
        // Let's use a simple markdown view for now.
        return new Content(
            markdown: 'emails.tenants.custom', // Create this view file later
            with: [ // Pass data to the view (public properties are also available)
                'body' => $this->body,
                'tenant' => $this->tenant, // Pass tenant to the view if needed
                // You might pass the owner user here too if you want 'from' details in the email body
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
        return []; // No attachments by default for custom emails
    }
}
