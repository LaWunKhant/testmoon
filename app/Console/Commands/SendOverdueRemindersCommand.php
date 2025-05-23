<?php

namespace App\Console\Commands;

use App\Jobs\SendOverduePaymentReminderJob;
// --- Add necessary Use statements here at the top, correctly ---
use App\Models\RentPayment;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection; // For database transactions
use Illuminate\Support\Facades\DB; // For logging
use Illuminate\Support\Facades\Log; // For catching exceptions

// --- End Use statements ---

class SendOverdueRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-overdue-reminders'; // Define the command name

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds overdue rent payments and sends reminder emails to tenants.'; // Describe the command

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to find and send overdue payment reminders...');

        // --- Query to find overdue payments needing reminders ---
        // Find RentPayment records where:
        // 1. Due date is in the past
        // 2. Paid is false
        // 3. Reminder_sent is false
        // Eager load the tenant relationship (assuming you have tenant() relationship on RentPayment)
        // Assuming your Tenant model has a 'house' relationship and you want propertyAddress in email
        $overduePaymentsNeedingReminders = RentPayment::where('due_date', '<', Carbon::now()) // Select records where due_date is in the past
            ->where('paid', false) // Where the payment is not yet paid
            ->where('reminder_sent', false) // And where a reminder hasn't been sent yet
            ->with(['tenant', 'tenant.house']) // Ensure tenant and house relationships are defined in your models
            ->get();

        $this->info('Found '.$overduePaymentsNeedingReminders->count().' overdue payments needing reminders.');

        // --- Group payments by tenant and dispatch jobs with explicit transaction ---
        $overduePaymentsByTenant = $overduePaymentsNeedingReminders->groupBy('tenant_id');

        $this->info('Found '.$overduePaymentsByTenant->count().' tenants with overdue payments.');

        $dispatchedJobCount = 0;

        // Loop through each group (each tenant)
        foreach ($overduePaymentsByTenant as $tenantId => $paymentsCollection) {
            // Find the tenant model
            $tenant = $paymentsCollection->first()->tenant;

            // Ensure tenant and email exist before attempting to dispatch
            if ($tenant && $tenant->email) {

                // *** Wrap the dispatch call in an explicit database transaction ***
                DB::beginTransaction();
                try {
                    $this->info("Attempting to dispatch reminder job for tenant ID: {$tenantId} ({$tenant->email})...");

                    // Dispatch the SendOverduePaymentReminderJob for this tenant with their overdue payments collection
                    // Dispatching to the queue is recommended
                    SendOverduePaymentReminderJob::dispatch($tenant, $paymentsCollection);

                    DB::commit(); // Commit the transaction to ensure the job is saved to the 'jobs' table
                    $this->info("Successfully dispatched and committed reminder job for tenant ID: {$tenantId}.");

                    $dispatchedJobCount++;

                } catch (Exception $e) {
                    // Rollback the transaction if an error occurs during dispatch
                    DB::rollBack();
                    $this->error("Failed to dispatch reminder job for tenant ID: {$tenantId}: ".$e->getMessage());
                    // Log the exception for more details
                    Log::error("Overdue reminder job dispatch failed for tenant ID: {$tenantId}: ".$e->getMessage(), ['exception' => $e]);
                }

            } else {
                $this->warn("Skipping reminder dispatch for tenant ID: {$tenantId}. Tenant not found or has no email.");
            }
        }

        $this->info('Finished processing for '.$overduePaymentsByTenant->count().' tenants.');
        $this->info('Total dispatched jobs: '.$dispatchedJobCount);

        // Note: The SendOverduePaymentReminderJob itself updates the reminder_sent flags after sending the email.
    }
}
