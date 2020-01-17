<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemManager;
use InvalidArgumentException;

class GarbageCollectionPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gc:pdf
                            {--age= : string for amount of time to keep scans, eg. 5 days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Garbage collection - Delete Old PDFs';

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
    public function handle(FilesystemManager $storage)
    {
        try {
            $duration = $this->option('age') ?? config('gc.pdf_age');
            try {
                $timestamp = Carbon::now()->sub($duration)->timestamp;
            } catch (\Exception $e) {
                throw new InvalidArgumentException('Invalid age format');
            }

            $local = $storage->disk('local');

            foreach ($local->files('scans') as $file) {
                if ($local->lastModified($file) < $timestamp) {
                    $local->delete($file);
                }
            }
        } catch (\Exception $e) {
           $this->error($e->getMessage());
           return -1;
       }
    }
}
