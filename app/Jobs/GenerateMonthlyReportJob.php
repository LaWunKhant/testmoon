<?php

namespace App\Jobs;

use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // Import the Mail facade

class GenerateMonthlyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        Log::info('GenerateMonthlyReportJob: Constructor called.');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GenerateMonthlyReportJob: About to enter try block in handle method.');

        try {
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
                    $tenantName = \App\Models\Tenant::find($payment->tenant_id)->name ?? 'Unknown Tenant';
                    $houseAddress = \App\Models\House::find($payment->house_id)->address ?? 'Unknown House';
                    $reportContent .= "- Payment ID: {$payment->id}, Amount: \${$payment->amount}, Date: {$payment->payment_date}, Tenant: {$tenantName}, House: {$houseAddress}\n";
                }
                $reportContent .= "--------------------------------\n";
            }
            Log::info('GenerateMonthlyReportJob: Finished formatting report content.');

            // 5. Deliver or store the report (Logging and Email)
            Log::info('GenerateMonthlyReportJob: Logging report content.');
            Log::info($reportContent); // Keep logging to the file
            Log::info('GenerateMonthlyReportJob: Finished logging report content.');

            // Uncomment and modify the email delivery here:
            Log::info('GenerateMonthlyReportJob: Attempting to send email...');
            Mail::raw($reportContent, function ($message) use ($startOfLastMonth) {
                $message->to('lawunkhant@gmail.com') // !!! Change this to the actual recipient's email address !!!
                    ->subject('Monthly Financial Report for '.$startOfLastMonth->format('Y-m'));
            });
            Log::info('GenerateMonthlyReportJob: Email sending attempt finished.');

        } catch (Exception $e) {
            Log::error('GenerateMonthlyReportJob: Exception caught during handle: '.$e->getMessage());
            Log::error('GenerateMonthlyReportJob: Exception trace: '.$e->getTraceAsString());
        }

        Log::info('GenerateMonthlyReportJob: Handle method finished.');
    }
}
