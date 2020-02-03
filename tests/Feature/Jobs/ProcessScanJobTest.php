<?php

namespace Tests\Feature\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Scan;
use App\Jobs\ProcessScan;
use App\Support\Service\Scan\ScanProcessor;
use Illuminate\Queue\Jobs\DatabaseJob;
use Mockery;

class ProcessScanJobTest extends TestCase
{
    /** @test */
    public function scan_status_is_updated_when_job_runs()
    {
        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('hasBeenAborted')->once()->andReturn(false);
        $scan->shouldReceive('setAttribute')->once()->with('status', 'processing');
        $scan->shouldReceive('setAttribute')->once()->with('message', '');
        $scan->shouldReceive('save')->once();

        $processor = Mockery::mock(ScanProcessor::class);
        $processor->shouldReceive('handle')->with($scan)->andReturn(null);

        $databaseJob = Mockery::mock(DatabaseJob::class);
        $databaseJob->shouldReceive('release')->never();

        $job = new ProcessScan($scan);
        $job->setJob($databaseJob);
        $job->handle($processor);
    }

    /** @test */
    public function scan_status_is_not_updated_when_status_is_aborted()
    {
        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('hasBeenAborted')->once()->andReturn(true);

        $processor = Mockery::mock(ScanProcessor::class);
        $processor->shouldReceive('handle')->never();

        $databaseJob = Mockery::mock(DatabaseJob::class);
        $databaseJob->shouldReceive('release')->never();

        $job = new ProcessScan($scan);
        $job->setJob($databaseJob);
        $job->handle($processor);
    }

    /** @test */
    public function scan_requires_delay()
    {
        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('hasBeenAborted')->once()->andReturn(false);
        $scan->shouldReceive('setAttribute')->once()->with('status', 'processing');
        $scan->shouldReceive('setAttribute')->once()->with('message', '');
        $scan->shouldReceive('save')->once();

        $processor = Mockery::mock(ScanProcessor::class);
        $processor->shouldReceive('handle')->with($scan)->andReturn(5);

        $databaseJob = Mockery::mock(DatabaseJob::class);
        $databaseJob->shouldReceive('release')->with(5)->once();

        $job = new ProcessScan($scan);
        $job->setJob($databaseJob);
        $job->handle($processor);
    }

    /** @test */
    public function scan_job_fails()
    {
        $exception = new \Exception('I\'m a failure');

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('setAttribute')->with('status', 'failed')->once();
        $scan->shouldReceive('setAttribute')->with('message', 'Exception: I\'m a failure')->once();
        $scan->shouldReceive('save')->once();

        $processor = Mockery::mock(ScanProcessor::class);
        $processor->shouldReceive('handle')->never();

        $databaseJob = Mockery::mock(DatabaseJob::class);
        $databaseJob->shouldReceive('release')->never();

        $job = new ProcessScan($scan);
        $job->setJob($databaseJob);
        $job->failed($exception);

    }
}
