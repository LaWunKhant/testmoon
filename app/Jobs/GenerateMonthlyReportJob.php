<?php

namespace App\Jobs;

use App\Models\House;
use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Add logging in the constructor
        Log::info('GenerateMonthlyReportJob: Constructor called.');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Add logging right before the try block
        Log::info('GenerateMonthlyReportJob: About to enter try block in handle method.');

        try {
            // Log at the very beginning of the try block
            Log::info('GenerateMonthlyReportJob: Handle method started inside try block.');

            // 1. Determine the report period (previous month)
            Log::info('GenerateMonthlyReportJob: Determining report period.');
            $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
            $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
            Log::info('GenerateMonthlyReportJob: Report period: '.$startOfLastMonth->format('Y-m-d').' to '.$endOfLastMonth->format('Y-m-d'));

            // 2. Fetch payments for the last month
            Log::info('GenerateMonthlyReportJob: Fetching payments.');
            $lastMonthPayments = Payment::whereBetween('payment_date', [$startOfLastMonth, $endOfLastMonth])
                ->get();
            Log::info('GenerateMonthlyReportJob: Finished fetching payments. Count: '.$lastMonthPayments->count());

            // 3. Calculate key metrics
            Log::info('GenerateMonthlyReportJob: Calculating metrics.');
            $totalAmountCollected = $lastMonthPayments->sum('amount');
            $numberOfPayments = $lastMonthPayments->count();
            Log::info('GenerateMonthlyReportJob: Finished calculating metrics.');

            // 4. Format the report content
            Log::info('GenerateMonthlyReportJob: Formatting report content.');
            $reportContent = "--- Monthly Financial Report ---\n";
            $reportContent .= 'Period: '.$startOfLastMonth->format('Y-m-d').' to '.$endOfLastMonth->format('Y-m-d')."\n";
            $reportContent .= 'Total Payments Recorded: '.$numberOfPayments."\n";
            $reportContent .= 'Total Amount Collected: $'.number_format($totalAmountCollected, 2)."\n";
            $reportContent .= "--------------------------------\n";

            // Optionally, list payments
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
            Log::info('GenerateMonthlyReportJob: Finished formatting report content.');

            // 5. Deliver or store the report (Logging)
            Log::info('GenerateMonthlyReportJob: Logging report content.');
            Log::info($reportContent); // Log the actual report
            Log::info('GenerateMonthlyReportJob: Finished logging report content.');

            // The email delivery part is commented out, keep it that way
            // \Illuminate\Support\Facades\Mail::raw(...);

        } catch (Exception $e) {
            // Catch any exception and log it with details
            Log::error('GenerateMonthlyReportJob: Exception caught during handle: '.$e->getMessage());
            Log::error('GenerateMonthlyReportJob: Exception trace: '.$e->getTraceAsString());
        }

        Log::info('GenerateMonthlyReportJob: Handle method finished.');
    }
}
