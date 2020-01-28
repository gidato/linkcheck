<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Scan;
use App\Support\Service\Scan\ScanProcessor;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Exception;

class ProcessScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // seconds allowed to run the job
    public $timeout = 60 * 60; // 60 minutes - each page takes at least a second due to one second delays between checks

    public $deleteWhenMissingModels = true;

    public $scan;

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

        // indicate we have started...
        $this->scan->status = 'processing';
        $this->scan->save();

        $processor->handle($this->scan);
    }

    /**
     * other errors, eg timeout;
     */
    public function failed(Exception $e)
    {
        $this->scan->status = 'failed';
        $this->scan->message = 'Exception: '. $e->getMessage();
        $this->scan->save();
    }
}
