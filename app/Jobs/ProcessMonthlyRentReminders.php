<?php

namespace App\Jobs;

use App\Mail\RentReminderEmail;
use App\Models\House;
// Ensure this is App\Models\RentPayment
use App\Models\Tenant;     // Ensure this is App\Models\Tenant
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessMonthlyRentReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $house;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(House $house)
    {
        $this->house = $house;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Processing rent reminders for house: '.$this->house->id);

        try {
            // Get tenants for the house
            // Ensure the House model has a 'tenants' relationship (hasMany)
            $tenants = $this->house->tenants;

            Log::info('Number of tenants found for house '.$this->house->id.': '.$tenants->count());

            // Use a database transaction for atomicity
            DB::transaction(function () use ($tenants) {
                foreach ($tenants as $tenant) {
                    Log::info('Attempting to create RentPayment for tenant ID: '.$tenant->id.', Name: '.$tenant->name);
                    Log::info('Tenant object dump: '.print_r($tenant->toArray(), true)); // Dump tenant attributes
                    Log::info('Checking rentPayments relationship callable for tenant ID: '.$tenant->id);

                    // This is the problematic line (line 49)
                    // We need to ensure $tenant->rentPayments() is not null here
                    $rentPaymentRelationship = $tenant->rentPayments();
                    Log::info('Result of $tenant->rentPayments(): '.($rentPaymentRelationship ? 'Object returned' : 'NULL returned'));

                    // If it's still null, the error will occur here
                    $rentPayment = $rentPaymentRelationship->create([
                        'due_date' => now()->addMonth(),
                        'amount' => $tenant->rent, // Ensure this is not null
                        'paid' => 0, // Assuming 'paid' column exists and defaults to 0 for new payments
                    ]);

                    Log::info("Rent payment created for tenant {$tenant->id}, amount: {$rentPayment->amount}, ID: {$rentPayment->id}");

                    // Dispatch the email job. It's good to queue the mailable directly.
                    Mail::to($tenant->email)->queue(new RentReminderEmail($tenant, $rentPayment));
                    Log::info("Rent reminder email queued for tenant {$tenant->id}");
                }
            });

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error processing rent reminders for house: '.$this->house->id.' - '.$e->getMessage());
            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }
}
