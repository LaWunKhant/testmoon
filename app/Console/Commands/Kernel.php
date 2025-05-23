<?php

namespace App\Console;

// --- Add necessary Use statements here at the top, correctly ---
use App\Http\Controllers\ReminderController; // For existing call schedules
use App\Jobs\GenerateMonthlyReportJob;      // For your job schedule
use Exception; // For defining schedules
use Illuminate\Console\Scheduling\Schedule; // For extending the base Kernel
use Illuminate\Foundation\Console\Kernel as ConsoleKernel; // For database transactions in the closure
use Illuminate\Support\Facades\DB; // For logging in the closure
use Illuminate\Support\Facades\Log; // For catching exceptions in the closure

// --- End Use statements ---

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Add any custom commands you have here, e.g.:
        \App\Console\Commands\ProcessRentRemindersCommand::class,
        // If the 'app:kernel' command you showed is a separate command you want to keep, list it here too:
        // \App\Console\Commands\Kernel::class, // Assuming your custom command is in app/Console/Commands/Kernel.php
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // --- Your existing schedules moved from the custom command ---
        // Send rent reminders on the 25th of each month
        $schedule->call(function () {
            $controller = app()->make(ReminderController::class);
            $controller->dispatchRentReminders();
        })->monthlyOn(25, '9:00');

        // Check for maintenance daily
        $schedule->call(function () {
            $controller = app()->make(ReminderController::class);
            $controller->sendMaintenanceReminders();
        })->daily()->at('8:00');

        // Check for overdue payments every Monday
        $schedule->call(function () {
            $controller = app()->make(ReminderController::class);
            $controller->processOverduePayments();
        })->weekly()->mondays()->at('10:00');
        // --- End of your existing schedules ---

        // --- Your new monthly report job schedule, using ->call with explicit transaction ---
        // Schedule the GenerateMonthlyReportJob to run monthly, ensuring transaction commit
        $schedule->call(function () {
            // --- No Use statements needed inside the closure ---
            // The Use statements at the top cover these classes

            Log::info('Scheduled GenerateMonthlyReportJob task started.'); // Log at the very start of this scheduled task

            DB::beginTransaction(); // Start a manual database transaction
            try {
                // Dispatch the job within the transaction
                Log::info('Attempting to dispatch GenerateMonthlyReportJob within transaction (scheduled task)...');
                GenerateMonthlyReportJob::dispatch(); // Dispatch the job
                Log::info('Dispatch call completed within transaction (scheduled task).');

                DB::commit(); // Commit the transaction to save the job to the 'jobs' table
                Log::info('Transaction committed. Job should be in the queue.');

            } catch (Exception $e) { // Catch Exception class
                // Rollback the transaction if an error occurs during dispatch
                DB::rollBack();
                Log::error('GenerateMonthlyReportJob dispatch failed and rolled back from scheduled task: '.$e->getMessage());
                // Optionally re-throw the exception if you want Laravel's scheduler to mark the task as failed
                // throw $e;
            }

            Log::info('Scheduled GenerateMonthlyReportJob task finished.'); // Log at the very end of this scheduled task

            // Temporarily set to everyMinute() for testing, change back to monthly() or monthlyOn() later
        })->everyMinute(); // <--- Set to everyMinute() for testing

        // Example for running at a specific time on the first day of the month:
        // ->monthlyOn(1, '3:00'); // Change ->everyMinute() to this for actual monthly scheduling

        // You might also chain methods like ->withoutOverlapping() if you don't want it to run if a previous instance is still going
        // ->onConnection('database') // Optional: Explicitly set connection if needed, but default should work based on .env
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
