<?php

namespace App\Jobs;

use App\Models\House;
use App\Models\Payment; // Keep import for individual payment listing
use App\Models\Tenant;  // Keep import for individual payment listing
use Carbon\Carbon;
use Illuminate\Bus\Queueable; // Add this back
use Illuminate\Contracts\Queue\ShouldQueue; // Add this back
use Illuminate\Foundation\Bus\Dispatchable; // Add this back
use Illuminate\Queue\InteractsWithQueue; // Add this back
use Illuminate\Queue\SerializesModels; // Add this back
use Illuminate\Support\Facades\Log;

class GenerateMonthlyReportJob implements ShouldQueue // Add this back
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // Add this back

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // No data needed in the constructor for this basic report
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GenerateMonthlyReportJob started (queued).'); // Adjusted log message for clarity

        // 1. Determine the report period (previous month)
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // 2. Fetch payments for the last month
        $lastMonthPayments = Payment::whereBetween('payment_date', [$startOfLastMonth, $endOfLastMonth])
            ->get();

        // 3. Calculate key metrics
        $totalAmountCollected = $lastMonthPayments->sum('amount');
        $numberOfPayments = $lastMonthPayments->count();

        // You could also add logic here to get details like:
        // - Payments grouped by house: $lastMonthPayments->groupBy('house_id')
        // - Payments grouped by tenant: $lastMonthPayments->groupBy('tenant_id')
        // - List individual payments: $lastMonthPayments->each(...)

        // 4. Format the report content (simple string for now)
        $reportContent = "--- Monthly Financial Report ---\n";
        $reportContent .= 'Period: '.$startOfLastMonth->format('Y-m-d').' to '.$endOfLastMonth->format('Y-m-d')."\n";
        $reportContent .= 'Total Payments Recorded: '.$numberOfPayments."\n";
        $reportContent .= 'Total Amount Collected: $'.number_format($totalAmountCollected, 2)."\n";
        $reportContent .= "--------------------------------\n";

        // Optionally, list payments (can be verbose for many payments)
        if ($numberOfPayments > 0) {
            $reportContent .= "Individual Payments:\n";
            foreach ($lastMonthPayments as $payment) {
                // Assuming Tenant and House models exist and have appropriate attributes like 'name' or 'address'
                $tenantName = \App\Models\Tenant::find($payment->tenant_id)->name ?? 'Unknown Tenant';
                $houseAddress = \App\Models\House::find($payment->house_id)->address ?? 'Unknown House';
                $reportContent .= "- Payment ID: {$payment->id}, Amount: \${$payment->amount}, Date: {$payment->payment_date}, Tenant: {$tenantName}, House: {$houseAddress}\n";
            }
            $reportContent .= "--------------------------------\n";
        }

        // 5. Deliver or store the report (Logging for now)
        Log::info($reportContent);

        // You could add email delivery here later if mail is configured:
        // \Illuminate\Support\Facades\Mail::raw($reportContent, function ($message) use ($startOfLastMonth) {
        //     $message->to('your-email@example.com') // Change to the recipient's email
        //             ->subject('Monthly Financial Report for ' . $startOfLastMonth->format('Y-m'));
        // });
    }
}
