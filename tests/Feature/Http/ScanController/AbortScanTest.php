<?php

namespace Tests\Feature\Http\ScanController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AbortScanTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();
        $user = factory(\App\User::class)->create();
        $this->be($user);

        $this->site = factory(\App\Site::class)->create();
    }

    /** @test */
    public function successfully_abort_queued_scan()
    {
        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id, 'status'=>'queued']);
        factory(\App\Page::class,10)->create(['scan_id' => $scan->id]);

        $response = $this->post('/scans/' . $scan->id . '/abort');

        $response->assertRedirect();
        $response->assertSessionHas('success','Scan aborted');
        $scan->refresh();
        $this->assertEquals('aborted', $scan->status);
    }

    /** @test */
    public function successfully_abort_processing_scan()
    {
        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id, 'status'=>'processing']);
        factory(\App\Page::class,10)->create(['scan_id' => $scan->id]);

        $response = $this->post('/scans/' . $scan->id . '/abort');

        $response->assertRedirect();
        $response->assertSessionHas('success','Scan aborted');
        $scan->refresh();
        $this->assertEquals('aborted', $scan->status);
    }

    /** @test */
    public function successfully_not_abort_success_scan()
    {
        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id, 'status'=>'success']);
        factory(\App\Page::class,10)->create(['scan_id' => $scan->id]);

        $response = $this->post('/scans/' . $scan->id . '/abort');

        $response->assertRedirect();
        $response->assertSessionHas('success','Scan already completed');
        $scan->refresh();
        $this->assertEquals('success', $scan->status);
    }

    /** @test */
    public function not_found_when_invalid_scan_id()
    {
        $response = $this->post('/scans/999/abort');
        $response->assertStatus(404);
    }
}
