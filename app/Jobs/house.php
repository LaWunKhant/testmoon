<?php

namespace App\Jobs;

use App\Models\RentPayment; // Ensure the Tenant class exists in this namespace or update to the correct namespace
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; // Ensure the RentPayment class exists in this namespace or update to the correct namespace

class house implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    protected $rentPayment;

    public function __construct(Tenant $tenant, RentPayment $rentPayment)
    {
        $this->tenant = $tenant;
        $this->rentPayment = $rentPayment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {}
}
