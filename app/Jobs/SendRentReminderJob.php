<?php

namespace App\Jobs;

use App\Mail\RentReminderMail;
use App\Models\RentPayment;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    protected $rentPayment;

    public function __construct(Tenant $tenant, RentPayment $rentPayment)
    {
        $this->tenant = $tenant;
        $this->rentPayment = $rentPayment;
    }

    public function handle()
    {
        // Send email reminder
        Mail::to($this->tenant->email)->send(new RentReminderMail($this->tenant, $this->rentPayment));

        // Log that reminder was sent
        \Log::info("Rent reminder sent to tenant: {$this->tenant->name} for payment due on {$this->rentPayment->due_date}");
    }
}
