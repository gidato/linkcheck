<?php

namespace Tests\Feature\Http\JobsController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Jobs\ProcessScan;
use App\FailedJob;

class ShowTest extends TestCase
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
    public function load_detailed_view_page()
    {
        $response = $this->get('/failed-jobs/' . $this->failedJob->id);
        $response->assertStatus(200);
        $response->assertSee($this->failedJob->id);
        $response->assertSee(ProcessScan::class);
        $response->assertSee("line1\nline2\nline3");
        $response->assertSee((string) $this->site->url);
    }
}
