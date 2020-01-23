<?php

namespace Tests\Feature\Http\ScanController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use App\Scan;
use App\Page;
use App\Site;
use App\FailedJob;
use App\Jobs\ProcessScan;
use App\Jobs\SendEmail;
use App\Support\Value\EmailOption;
use Illuminate\Queue\QueueManager;
use Carbon\Carbon;
use Mockery;

class RestartScanTest extends TestCase
{
    use RefreshDatabase;

    private $scan;

    protected function setUp() : void
    {
        parent::setUp();
        factory(\App\User::class)->create();

        $site = factory(\App\Site::class)->create();

        $this->scan = factory(\App\Scan::class)->create(['site_id' => $site->id, 'updated_at' => now(), 'status'=>'failed']);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
        $errorPage = factory(\App\Page::class)->create(['scan_id' => $this->scan->id, 'status_code' => 404]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
        Page::first()->referencedPages()->attach($errorPage,['type'=>'html','tag' => 'a', 'attribute' => 'href']);
    }

    /** @test */
    public function restart_scan_where_matching_job_found_on_failed_queue()
    {
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
               "command" => serialize(new ProcessScan($this->scan))
             ],
           ]
        );
        $failedJob->exception = "line1\nline2\nline3";
        $failedJob->save();

        $queueManager = Mockery::mock(QueueManager::class);
        $queueManager->shouldReceive('connection')->with($failedJob->connection)->once()->andReturn($queueManager);
        $queueManager->shouldReceive('pushRaw')->with(json_encode($failedJob->payload), $failedJob->queue)->once();

        $this->app->instance(QueueManager::class, $queueManager);

        $user = User::first();
        $this->be($user);

        $response = $this->post('/scans/'.$this->scan->id.'/retry');
        $response->assertRedirect();

        $this->assertCount(1, Scan::all());
        $scan = Scan::find($this->scan->id)->first();

        $this->assertCount(0, FailedJob::all());
    }

    /** @test */
    public function no_restart_where_matching_job_not_found_on_failed_queue()
    {
        /* job to send email, and not to process scan */
        $failedJob = new FailedJob;
        $failedJob->connection = 'database';
        $failedJob->queue = 'default';
        $failedJob->failed_at = now();
        $failedJob->payload = json_encode(
            [
             "displayName" => SendEmail::class,
             "job" => "Illuminate\Queue\CallQueuedHandler@call",
             "maxTries" => null,
             "delay" => null,
             "timeout" => 1200,
             "timeoutAt" => null,
             "data" => [
               "commandName" => SendEmail::class,
               "command" => serialize(new SendEmail($this->scan, new EmailOption()))
             ],
           ]
        );
        $failedJob->exception = "line1\nline2\nline3";
        $failedJob->save();

        /* job without scan */
        $failedJob = new FailedJob;
        $failedJob->connection = 'database';
        $failedJob->queue = 'default';
        $failedJob->failed_at = now();
        $failedJob->payload = json_encode(
            [
             "displayName" => \StdClass::class,
             "job" => "Illuminate\Queue\CallQueuedHandler@call",
             "maxTries" => null,
             "delay" => null,
             "timeout" => 1200,
             "timeoutAt" => null,
             "data" => [
               "commandName" => \StdClass::class,
               "command" => serialize(new \StdClass)
             ],
           ]
        );
        $failedJob->exception = "line1\nline2\nline3";
        $failedJob->save();

        /* job withdifferent scan */
        $scan = factory(\App\Scan::class)->create(['site_id' => $this->scan->site->id, 'updated_at' => now()]);
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

        /* job for same scan, but somehow for a different time - for example if re-run some how */
        $failedJob = new FailedJob;
        $failedJob->connection = 'database';
        $failedJob->queue = 'default';
        $failedJob->failed_at = Carbon::now()->addSeconds(10);
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
               "command" => serialize(new ProcessScan($this->scan))
             ],
           ]
        );
        $failedJob->exception = "line1\nline2\nline3";
        $failedJob->save();

        $queueManager = Mockery::mock(QueueManager::class);
        $queueManager->shouldReceive('connection')->with($failedJob->connection)->never();
        $this->app->instance(QueueManager::class, $queueManager);

        $user = User::first();
        $this->be($user);

        $response = $this->post('/scans/'.$this->scan->id.'/retry');
        $response->assertRedirect();

        $this->assertCount(2, Scan::all());
        $this->scan->refresh();
        $this->assertEquals('errors', $this->scan->status);

        $this->assertCount(4, FailedJob::all());
    }
}
