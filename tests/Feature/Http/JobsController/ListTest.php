<?php

namespace Tests\Feature\Http\JobsController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Jobs\ProcessScan;
use App\FailedJob;

class ListTest extends TestCase
{
    use RefreshDatabase;

    private $site;

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

    }

    /** @test */
    public function load_list_page()
    {
        $response = $this->get('/failed-jobs');
        $response->assertStatus(200);
        foreach (FailedJob::all() as $job) {
            $response->assertSee($job->id);
            $response->assertSee(ProcessScan::class);
            $response->assertSee("line1");
        }
    }
}
