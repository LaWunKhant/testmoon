<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMonthlyRentReminders;
use App\Models\House;
use Illuminate\Console\Command; // Import the House model

class ProcessRentRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rent:process-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the ProcessMonthlyRentReminders job for each house';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Dispatching ProcessMonthlyRentReminders job for each house...');

        // Get all houses
        $houses = House::all();

        // Dispatch the job for each house
        foreach ($houses as $house) {
            dispatch(new ProcessMonthlyRentReminders($house)); // Pass the $house object here
            $this->info("Dispatched ProcessMonthlyRentReminders job for house: {$house->id}");
        }

        $this->info('All ProcessMonthlyRentReminders jobs dispatched successfully!');

        return 0;
    }
}
