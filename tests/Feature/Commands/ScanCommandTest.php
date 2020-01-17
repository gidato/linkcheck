<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;
use App\Support\Value\Url;
use App\Jobs\ProcessScan;
use App\Jobs\SendEmail;
use Illuminate\Support\Facades\Queue;

class ScanCommandTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();
        $this->site = factory(\App\Site::class)->create(['url' => new Url('http://example.com')]);
    }

    /** @test */
    public function fail_if_invalid_url()
    {
        $this->artisan('scan:new', ['url' => 'invalid:format',])
            ->expectsOutput('Invalid URL')
            ->assertExitCode(-1);
    }

    /** @test */
    public function fail_if_site_not_found_url()
    {
        $this->artisan('scan:new', ['url' => 'http://localhost',])
            ->expectsOutput('This site has not been setup.')
            ->assertExitCode(-1);
    }

    /** @test */
    public function fail_if_invalid_option_passed()
    {
        $this->artisan('scan:new', ['url' => 'http://example.com', '--email' => 'unknown'])
            ->expectsOutput('Only "SELF" or "ALL" allowed')
            ->assertExitCode(-1);

        $this->artisan('scan:new', ['url' => 'http://example.com', '-E' => 'unknown'])
            ->expectsOutput('Only "SELF" or "ALL" allowed')
            ->assertExitCode(-1);
    }

    /** @test */
    public function queues_scan_and_email_if_all_valid()
    {
        Queue::fake();

        $this->artisan('scan:new', ['url' => 'http://example.com', '--email' => 'self'])
            ->assertExitCode(0)
            ->run();

        $scan = $this->site->scans[0];
        Queue::assertPushedWithChain(ProcessScan::class, [
            SendEmail::class
        ]);
    }

}
