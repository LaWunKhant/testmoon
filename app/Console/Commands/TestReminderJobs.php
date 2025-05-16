<?php

namespace App\Console\Commands;

use App\Http\Controllers\ReminderController;
use Illuminate\Console\Command;

class TestReminderJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-reminders {type=all : The type of reminder to test (rent, maintenance, overdue, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test reminder jobs functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reminderController = app(ReminderController::class);
        $type = $this->argument('type');

        $this->info("Testing reminder jobs for: $type");

        if ($type === 'rent' || $type === 'all') {
            $this->info('Dispatching rent reminders...');
            $response = $reminderController->dispatchRentReminders();
            $this->info(json_encode($response->original));
        }

        if ($type === 'maintenance' || $type === 'all') {
            $this->info('Dispatching maintenance reminders...');
            $response = $reminderController->sendMaintenanceReminders();
            $this->info(json_encode($response->original));
        }

        if ($type === 'overdue' || $type === 'all') {
            $this->info('Processing overdue payments...');
            $response = $reminderController->processOverduePayments();
            $this->info(json_encode($response->original));
        }

        $this->info('Testing complete!');
    }
}
