<?php

namespace Tests\Feature\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Scan;
use App\Jobs\ProcessScan;
use App\Support\Service\Scan\ScanProcessor;
use Mockery;

class ProcessScanJobTest extends TestCase
{
    /** @test */
    public function scan_status_is_updated_when_job_runs()
    {
        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('hasBeenAborted')->once()->andReturn(false);
        $scan->shouldReceive('setAttribute')->once()->with('status', 'processing');
        $scan->shouldReceive('save')->once();

        $processor = Mockery::mock(ScanProcessor::class);
        $processor->shouldReceive('handle')->with($scan);

        $job = new ProcessScan($scan);
        $job->handle($processor);
    }

    /** @test */
    public function scan_status_is_not_updated_when_status_is_aborted()
    {
        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('hasBeenAborted')->once()->andReturn(true);

        $processor = Mockery::mock(ScanProcessor::class);
        $processor->shouldReceive('handle')->never();

        $job = new ProcessScan($scan);
        $job->handle($processor);
    }
}
