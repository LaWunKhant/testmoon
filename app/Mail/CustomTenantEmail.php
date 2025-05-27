<?php

namespace App\Mail;

use App\Models\Tenant;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CustomTenantEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;

    public $body;

    public $tenant;

    // *** CHANGE THIS FROM protected TO public ***
    public $attachments = []; // *** Property to hold uploaded files (array of UploadedFile) - MUST BE PUBLIC ***

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $body, ?Tenant $tenant = null, array $uploadedFiles = [])
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->tenant = $tenant;
        $this->attachments = $uploadedFiles; // Store uploaded files array
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenants.custom',
            with: [
                'body' => $this->body,
                'tenant' => $this->tenant,
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
        $attachments = [];

        foreach ($this->attachments as $uploadedFile) {
            if ($uploadedFile instanceof UploadedFile) {
                try {
                    $attachments[] = Attachment::fromPath($uploadedFile->getRealPath())
                        ->as($uploadedFile->getClientOriginalName())
                        ->withMime($uploadedFile->getMimeType());
                } catch (Exception $e) {
                    Log::error('Failed to attach uploaded file to custom email (Mailable): '.$e->getMessage(), ['filename' => $uploadedFile->getClientOriginalName()]);
                }
            }
        }

        return $attachments;
    }
}
