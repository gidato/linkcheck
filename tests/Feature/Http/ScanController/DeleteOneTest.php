<?php

namespace Tests\Feature\Http\ScanController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteOneTest extends TestCase
{
    use RefreshDatabase;

    private $scan;
    private $user;

    public function setup() : void
    {
        parent::setup();
        $site = factory(\App\Site::class)->create();
        $this->scan = factory(\App\Scan::class)->create(['site_id' => $site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
        $this->user = factory(\App\User::class)->create();

        factory(\App\Scan::class)->create(['site_id' => $site->id]);  // add another so there are two to start with
    }


    /** @test */
    public function delete_a_single_scan_and_all_pages()
    {
        // check two before start§
        $this->assertEquals(2, \App\Scan::count());
        $response = $this->actingAs($this->user)->delete('scans/'. $this->scan->id);
        $response->assertRedirect('http://localhost');
        $this->assertEquals(1, \App\Scan::count());
        $this->assertNotEquals($this->scan->id, \App\Scan::first()->id);
        $this->assertEquals(0, \App\Page::count());
    }

    /** @test */
    public function scan_not_found()
    {
        // check two before start§
        $this->assertEquals(2, \App\Scan::count());
        $response = $this->actingAs($this->user)->delete('scans/'. 999);
        $response->assertStatus(404);
        $this->assertEquals(2, \App\Scan::count());
    }



}
