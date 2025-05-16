<?php

namespace App\Jobs;

use App\Models\House;
use App\Models\RentPayment;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMonthlyRentReminders implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $house;

    public function __construct(House $house)
    {
        $this->house = $house;
    }

    public function handle()
    {
        // Get all active tenants for this house
        $tenants = $this->house->tenants()->where('active', true)->get();

        // Get the rent payment due date (typically first of next month)
        $dueDate = Carbon::now()->addMonth()->startOfMonth();

        foreach ($tenants as $tenant) {
            // Check if a rent payment record already exists for next month
            $paymentExists = RentPayment::where('tenant_id', $tenant->id)
                ->where('due_date', $dueDate)
                ->exists();

            if (! $paymentExists) {
                // Create the rent payment record
                $payment = new RentPayment([
                    'tenant_id' => $tenant->id,
                    'house_id' => $this->house->id,
                    'amount' => $tenant->rent_amount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                ]);
                $payment->save();

                // Dispatch a job to send the reminder
                dispatch(new SendRentReminderJob($tenant, $payment));
            }
        }

        // Log that processing is complete
        \Log::info("Processed rent reminders for house: {$this->house->name}");
    }
}
