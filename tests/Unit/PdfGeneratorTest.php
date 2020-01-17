<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Service\PdfGenerator;
use Illuminate\Support\Facades\Storage;

class PdfGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private $scan;

    public function setup() : void
    {
        parent::setup();
        $site = factory(\App\Site::class)->create();
        $this->scan = factory(\App\Scan::class)->create(['site_id' => $site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
    }

    /** @test */
    public function testExample()
    {
        Storage::fake('local');

        $generator = app(PdfGenerator::class);
        $generator->generate($this->scan);

        $expectedFilename = sprintf('scans/scan_%s_%s.pdf',
            $this->scan->id,
            $this->scan->updated_at->format('Ymd_His')
        );

        Storage::disk('local')->assertExists($expectedFilename);
        $this->assertGreaterThan(0, Storage::disk('local')->size($expectedFilename));
    }
}
