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

    // job gets pushed back to queue for each throttle delay (which is probably each page), so allow for plenty
    public $tries = 100000;

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
        $this->scan->message = '';
        $this->scan->save();

        $releaseAndDelay = $processor->handle($this->scan);
        if (null !== $releaseAndDelay) {
            $this->release($releaseAndDelay);
        }
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
