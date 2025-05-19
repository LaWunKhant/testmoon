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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessMonthlyRentReminders implements ShouldQueue // Added implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $house;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(House $house)  // Look here:  It expects a House object.
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

            foreach ($tenants as $tenant) {
                // Create RentPayment
                $rentPayment = new RentPayment;
                $rentPayment->tenant_id = $tenant->id;
                $rentPayment->due_date = now()->addMonth();
                $rentPayment->amount = $tenant->rent;
                $rentPayment->save();
                Log::info("Rent payment created for tenant {$tenant->id}");
                // send reminder
                Mail::to($tenant->email)->send(new RentReminderMail($tenant, $rentPayment));
                Log::info("Rent reminder sent to tenant {$tenant->id}");
            }

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error processing rent reminders for house: '.$this->house->id.' - '.$e->getMessage());
            // Optionally, you can decide whether to retry the job or not.
            //  $this->fail($e); // This will mark the job as failed in the queue.
        }
    }
}
