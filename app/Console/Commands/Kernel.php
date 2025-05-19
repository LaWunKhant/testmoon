<?php

namespace App\Console\Commands;

use App\Http\Controllers\ReminderController;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kernel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }

    protected function schedule(Schedule $schedule)
    {
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
    }

    protected $commands = [
        // ... other commands
        \App\Console\Commands\ProcessRentRemindersCommand::class, // Add this line
    ];
}
