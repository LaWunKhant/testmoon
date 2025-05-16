<?php

namespace App\Jobs;

use App\Mail\OverduePaymentReminderMail;
use App\Models\Tenant;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendOverduePaymentReminderJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The tenant to send reminder to
     */
    protected $tenant;

    /**
     * Collection of overdue payments
     */
    protected $payments;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Collection $payments)
    {
        $this->tenant = $tenant;
        $this->payments = $payments;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send email with overdue payment reminder
        Mail::to($this->tenant->email)
            ->send(new OverduePaymentReminderMail($this->tenant, $this->payments));

        // Update payment records to mark that reminders were sent
        foreach ($this->payments as $payment) {
            $payment->reminder_sent = true;
            $payment->reminder_sent_at = now();
            $payment->save();
        }

        // Log that the reminder was sent
        \Log::info("Overdue payment reminder sent to tenant: {$this->tenant->name} for ".
                   $this->payments->count().' overdue payments.');
    }
}
