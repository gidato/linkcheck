<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Scan;
use InvalidArgumentException;

class GarbageCollectionScans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gc:scans
                            {--age= : string for amount of time to keep scans, eg. 6 months}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Garbage collection - Delete Old Scans';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $duration = $this->option('age') ?? config('gc.scan_age');
            try {
                $date = Carbon::now()->sub($duration);
            } catch (\Exception $e) {
                throw new InvalidArgumentException('Invalid age format');
            }

            Scan::where('updated_at','<', $date)->each(function($scan, $key) {
                $scan->delete();
            });

        } catch (\Exception $e) {
           $this->error($e->getMessage());
           return -1;
       }

    }
}
