<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GcPdfCommandTest extends TestCase
{
    public function setup() : void
    {
        parent::setup();
        Storage::fake('local');
        $this->generateFiles(5,'3 months');
        $this->generateFiles(5,'3 weeks');
        $this->generateFiles(5,'3 days');
        $this->generateFiles(5,'3 hours');
        $this->generateFiles(5,'3 minutes');
    }

    private function generateFiles(int $count, string $age) : void
    {
        $age = Carbon::now()->sub($age);
        for ($i=1; $i<=$count; $i++) {
            $filename = 'scans/file_'.$age->format('YMD_His').'_'.$i;
            Storage::disk('local')->put($filename,'contents');
            $path = Storage::disk('local')->getAdapter()->applyPathPrefix($filename);
            touch($path, $age->timestamp);
        }
    }

    /** @test */
    public function invalid_request_not_valid_period_fails()
    {
        $this->artisan('gc:pdf', ['--age' => '3.5 dogs',])
            ->expectsOutput('Invalid age format')
            ->assertExitCode(-1);
    }

    /** @test */
    public function deletes_older_than_5_days_default()
    {
        $this->assertCount(25, Storage::disk('local')->files('scans'));
        $this->artisan('gc:pdf', [])
            ->assertExitCode(0)
            ->run();
        $this->assertCount(15, Storage::disk('local')->files('scans'));
    }


    /** @test */
    public function deletes_older_than_period_requested()
    {
        $this->assertCount(25, Storage::disk('local')->files('scans'));
        $this->artisan('gc:pdf', ['--age' => '10 minutes 25 seconds'])
            ->assertExitCode(0)
            ->run();
        $this->assertCount(5, Storage::disk('local')->files('scans'));
    }

}
