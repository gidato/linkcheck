<?php

namespace Tests\Feature\Http\ScanController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewDetailTest extends TestCase
{
    use RefreshDatabase;

    private $scan;

    public function setup() : void
    {
        parent::setup();
        $user = factory(\App\User::class)->create();
        $this->be($user);

        $site = factory(\App\Site::class)->create();

        $this->scan = factory(\App\Scan::class)->create(['site_id' => $site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
    }

    /** @test */
    public function successfully_access_scan()
    {
        $response = $this->get('/scans/'.$this->scan->id);

        $response->assertStatus(200);
        $response->assertViewIs('scans.show');
        $response->assertViewHas('scan', $this->scan);
        $response->assertSeeText($this->scan->pages[5]->getShortUrl());  // a random one is on the page - no need to check all
    }

    /** @test */
    public function not_found_when_invalid_scan_id()
    {
        $response = $this->get('/scans/999');
        $response->assertStatus(404);
    }

}
