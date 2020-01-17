<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;
use App\Scan;
use App\Site;
use App\Support\Value\Url;

class GcScansCommandTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();
        $this->site = factory(Site::class)->create(['url' => new Url('http://example.com')]);
        factory(Scan::class,10)->create(['site_id' => $this->site->id]);
        factory(Scan::class,10)->create(['site_id' => $this->site->id, 'updated_at' => Carbon::now()->sub(1, 'hours')]);
        factory(Scan::class,10)->create(['site_id' => $this->site->id, 'updated_at' => Carbon::now()->sub(1, 'years')]);
    }

    /** @test */
    public function invalid_request_not_valid_period_fails()
    {
        $this->artisan('gc:scans', ['--age' => '3.5 dogs',])
            ->expectsOutput('Invalid age format')
            ->assertExitCode(-1);
    }

    /** @test */
    public function deletes_older_than_6_months_default()
    {
        $this->assertEquals(30, Scan::count());
        $this->artisan('gc:scans', [])
            ->assertExitCode(0)
            ->run();
        $this->assertEquals(20, Scan::count());
    }


    /** @test */
    public function deletes_older_than_period_requested()
    {
        $this->assertEquals(30, Scan::count());
        $this->artisan('gc:scans', ['--age' => '30 minutes 25 seconds'])
            ->assertExitCode(0)
            ->run();
        $this->assertEquals(10, Scan::count());
    }

}
