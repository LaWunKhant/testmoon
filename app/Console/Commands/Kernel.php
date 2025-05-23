<?php

namespace App\Console;

// --- Add necessary Use statements here at the top, correctly ---
// *** Import your new command ***
use App\Http\Controllers\ReminderController; // For existing call schedules
use App\Jobs\GenerateMonthlyReportJob;      // For your job schedule
use Exception; // For catching exceptions in closures
use Illuminate\Console\Scheduling\Schedule; // For defining schedules
use Illuminate\Foundation\Console\Kernel as ConsoleKernel; // For extending the base Kernel
use Illuminate\Support\Facades\DB; // For database transactions in the closure
use Illuminate\Support\Facades\Log; // For logging in closures

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
        // *** Ensure your new command is listed here ***
        \App\Console\Commands\SendOverdueRemindersCommand::class,
        // If the 'app:kernel' command you showed is a separate command you want to keep, list it here too:
        // \App\Console\Commands\Kernel::class, // Assuming your custom command is in app/Console/Commands/Kernel.php
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // --- Your existing schedules ---
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
        // --- End of existing schedules ---

        // --- Your monthly report job schedule (using ->call with explicit transaction) ---
        // Schedule the GenerateMonthlyReportJob to run monthly, ensuring transaction commit
        $schedule->call(function () {
            // Use Use statements at the top
            Log::info('Scheduled GenerateMonthlyReportJob task started.');

            DB::beginTransaction();
            try {
                Log::info('Attempting to dispatch GenerateMonthlyReportJob within transaction (scheduled task)...');
                GenerateMonthlyReportJob::dispatch();
                Log::info('Dispatch call completed within transaction (scheduled task).');
                DB::commit();
                Log::info('Transaction committed. Job should be in the queue.');
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('GenerateMonthlyReportJob dispatch failed and rolled back from scheduled task: '.$e->getMessage());
            }
            Log::info('Scheduled GenerateMonthlyReportJob task finished.');

            // Temporarily set to everyMinute() for testing, change back to monthly() or monthlyOn() later
        })->everyMinute(); // <--- Monthly Report Job: Set to everyMinute() for testing

        // --- Schedule your new Overdue Payment Reminders Command ---
        // Schedule the Artisan command that sends overdue reminders
        $schedule->command('app:send-overdue-reminders')
            ->everyMinute(); // *** ADD THIS LINE, set to everyMinute() for testing ***

        // Example for running daily at 9:30 AM:
        // $schedule->command('app:send-overdue-reminders')->daily()->at('9:30');

        // Example to run weekly on Mondays at 10:00 AM:
        // $schedule->command('app:send-overdue-reminders')->weekly()->mondays()->at('10:00');

        // You might also chain methods like ->withoutOverlapping() if you don't want it to run if a previous instance is still going
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
