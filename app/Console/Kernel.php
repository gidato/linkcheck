<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\GarbageCollectionPdfs;
use App\Console\Commands\GarbageCollectionScans;
use App\Console\Commands\StartQueueWorker;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(GarbageCollectionPdfs::class)->dailyAt("02:30");
        $schedule->command(GarbageCollectionScans::class)->dailyAt("02:30");
        if ($this->howManyTimesIsOsProcessIsRunning('queue:work') < config('queue.workers')) {
            // this will only run, when not already running
            // and locks up the command, so probably should go last in the list of scheduled jobs
            $schedule->command('queue:work', ['--tries' => 3, '--queue' => 'high,default,low'])->everyMinute();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected function howManyTimesIsOsProcessIsRunning($needle) : int
    {
        // get process status. the "-ww"-option is important to get the full output!
        exec('ps aux -ww', $process_status);

        // search $needle in process status
        $result = array_filter($process_status, function($var) use ($needle) {
            return strpos($var, $needle);
        });

        return count($result);
    }
}
