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
