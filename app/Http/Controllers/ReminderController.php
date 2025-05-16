<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMonthlyRentReminders;
use App\Models\House;
use App\Models\MaintenanceRequest;
use App\Models\RentPayment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class ReminderController extends Controller
{
    /**
     * Dispatch batch jobs to process rent reminders for all houses
     */
    public function dispatchRentReminders()
    {
        // Get all houses
        $houses = House::all();

        // Prepare batch jobs - one job per house
        $jobs = [];
        foreach ($houses as $house) {
            $jobs[] = new ProcessMonthlyRentReminders($house);
        }

        // Create a batch
        $batch = Bus::batch($jobs)
            ->name('Monthly Rent Reminders: '.Carbon::now()->format('Y-m-d'))
            ->allowFailures()
            ->onQueue('reminders')
            ->dispatch();

        return response()->json([
            'message' => 'Rent reminder batch has been dispatched',
            'batch_id' => $batch->id,
            'job_count' => count($jobs),
        ]);
    }

    /**
     * Send maintenance reminders for upcoming scheduled maintenance
     */
    public function sendMaintenanceReminders()
    {
        // Find maintenance requests scheduled in the next 48 hours
        $upcomingMaintenance = MaintenanceRequest::where('status', 'scheduled')
            ->whereBetween('scheduled_date', [
                Carbon::now(),
                Carbon::now()->addHours(48),
            ])
            ->get();

        // Group by house to send batch notifications
        $maintenanceByHouse = $upcomingMaintenance->groupBy('house_id');

        // Prepare batch of jobs
        $jobs = [];
        foreach ($maintenanceByHouse as $houseId => $requests) {
            $house = House::find($houseId);
            $tenants = Tenant::where('house_id', $houseId)->where('active', true)->get();

            foreach ($tenants as $tenant) {
                $jobs[] = new \App\Jobs\SendMaintenanceNotificationJob(
                    $tenant,
                    $house,
                    $requests
                );
            }
        }

        // Dispatch as a batch if there are jobs
        if (count($jobs) > 0) {
            $batch = Bus::batch($jobs)
                ->name('Maintenance Notifications: '.Carbon::now()->format('Y-m-d'))
                ->allowFailures()
                ->onQueue('notifications')
                ->dispatch();

            return response()->json([
                'message' => 'Maintenance notification batch has been dispatched',
                'batch_id' => $batch->id,
                'job_count' => count($jobs),
            ]);
        }

        return response()->json([
            'message' => 'No upcoming maintenance to send notifications for',
        ]);
    }

    /**
     * Check and handle overdue payments
     */
    public function processOverduePayments()
    {
        // Find all overdue payments
        $overduePayments = RentPayment::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->get();

        // Group by tenant for batch processing
        $paymentsByTenant = $overduePayments->groupBy('tenant_id');

        // Create jobs for sending overdue notices
        $jobs = [];
        foreach ($paymentsByTenant as $tenantId => $payments) {
            $tenant = Tenant::find($tenantId);
            $jobs[] = new \App\Jobs\SendOverduePaymentReminderJob($tenant, $payments);
        }

        // Dispatch as a batch if there are jobs
        if (count($jobs) > 0) {
            $batch = Bus::batch($jobs)
                ->name('Overdue Payment Notices: '.Carbon::now()->format('Y-m-d'))
                ->allowFailures()
                ->onQueue('reminders')
                ->dispatch();

            return response()->json([
                'message' => 'Overdue payment reminders have been dispatched',
                'batch_id' => $batch->id,
                'job_count' => count($jobs),
            ]);
        }

        return response()->json([
            'message' => 'No overdue payments to process',
        ]);
    }

    public function sendRentRemainders()
    {
        // Logic to send rent reminders
    }
}
