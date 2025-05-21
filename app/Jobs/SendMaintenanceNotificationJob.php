<?php

namespace App\Jobs;

use App\Mail\MaintenanceNotificationEmail;
use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMaintenanceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $maintenanceRequest;

    protected $isUpdate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MaintenanceRequest $maintenanceRequest, $isUpdate = false)
    {
        $this->maintenanceRequest = $maintenanceRequest;
        $this->isUpdate = $isUpdate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $house = $this->maintenanceRequest->house;
            $tenant = $this->maintenanceRequest->tenant; // Access the tenant

            if ($this->isUpdate) {
                Mail::to($house->email)->send(new MaintenanceNotificationEmail($this->maintenanceRequest, $tenant, true));
                Log::info("Maintenance request update notification sent to house {$house->id} for request {$this->maintenanceRequest->id}");
            } else {
                // Send email to the house
                Mail::to($house->email)->send(new MaintenanceNotificationEmail($this->maintenanceRequest, $tenant));
                Log::info("Maintenance request notification sent to house {$house->id} for request {$this->maintenanceRequest->id}");
            }

        } catch (\Exception $e) {
            Log::error('Failed to send maintenance notification: '.$e->getMessage());
            // Consider retrying the job or handling the error more gracefully
            throw $e; // Re-throw the exception to mark the job as failed
        }
    }
}
