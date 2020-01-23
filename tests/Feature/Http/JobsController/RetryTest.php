<?php

namespace Tests\Feature\Http\JobsController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Jobs\ProcessScan;
use App\FailedJob;
use Illuminate\Queue\QueueManager;
use Mockery;

class RetryTest extends TestCase
{
    use RefreshDatabase;

    private $site;
    private $failedJob;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id]);

        $failedJob = new FailedJob;
        $failedJob->connection = 'database';
        $failedJob->queue = 'default';
        $failedJob->failed_at = now();
        $failedJob->payload = json_encode(
            [
             "displayName" => ProcessScan::class,
             "job" => "Illuminate\Queue\CallQueuedHandler@call",
             "maxTries" => null,
             "delay" => null,
             "timeout" => 1200,
             "timeoutAt" => null,
             "data" => [
               "commandName" => ProcessScan::class,
               "command" => serialize(new ProcessScan($scan))
             ],
           ]
        );
        $failedJob->exception = "line1\nline2\nline3";
        $failedJob->save();

        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id]);
        $this->failedJob = new FailedJob;
        $this->failedJob->connection = 'database';
        $this->failedJob->queue = 'default';
        $this->failedJob->failed_at = now();
        $this->failedJob->payload = json_encode(
            [
             "displayName" => ProcessScan::class,
             "job" => "Illuminate\Queue\CallQueuedHandler@call",
             "maxTries" => null,
             "delay" => null,
             "timeout" => 1200,
             "timeoutAt" => null,
             "data" => [
               "commandName" => ProcessScan::class,
               "command" => serialize(new ProcessScan($scan))
             ],
           ]
        );
        $this->failedJob->exception = "line1\nline2\nline3";
        $this->failedJob->save();
        $this->failedJob->refresh();
    }

    /** @test */
    public function retry_an_existing_job()
    {
        $queueManager = Mockery::mock(QueueManager::class);
        $queueManager->shouldReceive('connection')->with($this->failedJob->connection)->once()->andReturn($queueManager);
        $queueManager->shouldReceive('pushRaw')->with(json_encode($this->failedJob->payload), $this->failedJob->queue)->once();

        $this->app->instance(QueueManager::class, $queueManager);
        $response = $this->post('/failed-jobs/' . $this->failedJob->id . '/retry');
        $response->assertRedirect();
        $this->assertNull(FailedJob::find($this->failedJob->id));

    }
}
