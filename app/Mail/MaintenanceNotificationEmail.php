<?php

namespace App\Mail;

use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $maintenanceRequest;

    public $tenant; // Add this property

    public $isUpdate;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MaintenanceRequest $maintenanceRequest, Tenant $tenant, $isUpdate = false)
    {
        $this->maintenanceRequest = $maintenanceRequest;
        $this->tenant = $tenant; // Assign the tenant
        $this->isUpdate = $isUpdate;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        if ($this->isUpdate) {
            return new Envelope(
                subject: 'Maintenance Request Status Updated',
            );
        }

        return new Envelope(
            subject: 'New Maintenance Request',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.maintenance_notification',
            with: [
                'request' => $this->maintenanceRequest,
                'tenant' => $this->tenant, // Pass the tenant to the view
                'isUpdate' => $this->isUpdate,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
