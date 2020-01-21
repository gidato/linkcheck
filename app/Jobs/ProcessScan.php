<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Scan;
use App\Support\Service\Scan\ScanProcessor;

class ProcessScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // seconds allowed to run the job
    public $timeout = 20 * 60; // 20 seconds - each page takes a second due to one second delays between checks

    private $scan;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    public function handle(ScanProcessor $processor)
    {
        if ($this->scan->hasBeenAborted()) {
            // allows abort before even starting
            return;
        }

        $this->scan->status = 'processing';
        $this->scan->save();

        try {
            $processor->handle($this->scan);
        } catch (\Exception $e) {
            $this->scan->status = 'errors';
            $this->scan->message = 'Exception: '. $e->getMessage();
            $this->scan->save();
        }
    }
}
