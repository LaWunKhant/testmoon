<?php

namespace App\Jobs;

use App\Mail\RentReminderMail;
use App\Models\House;
use App\Models\RentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // Import DB class for transactions

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
        try {
            //  Your logic to process rent reminders for the house
            Log::info('Processing rent reminders for house: '.$this->house->id);

            // Get tenants for the house
            $tenants = $this->house->tenants;

            // Use a database transaction for atomicity
            DB::transaction(function () use ($tenants) {
                foreach ($tenants as $tenant) {
                    // Create RentPayment
                    $rentPayment = new RentPayment;
                    $rentPayment->tenant_id = $tenant->id;
                    $rentPayment->due_date = now()->addMonth();
                    $rentPayment->amount = $tenant->rent; // Ensure this is not null
                    $rentPayment->save();
                    Log::info("Rent payment created for tenant {$tenant->id}, amount: {$rentPayment->amount}");

                    // send reminder
                    Mail::to($tenant->email)->send(new RentReminderMail($tenant, $rentPayment));
                    Log::info("Rent reminder sent to tenant {$tenant->id}");
                }
            });

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error processing rent reminders for house: '.$this->house->id.' - '.$e->getMessage());
            //  Re-throw the exception to mark the job as failed
            throw $e;
        }
    }
}
