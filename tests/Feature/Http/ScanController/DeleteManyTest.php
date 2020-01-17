<?php

namespace Tests\Feature\Http\ScanController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteManyTest extends TestCase
{
    use RefreshDatabase;

    private $scan1;
    private $scan2;
    private $user;

    public function setup() : void
    {
        parent::setup();
        $site = factory(\App\Site::class)->create();

        $this->scan1 = factory(\App\Scan::class)->create(['site_id' => $site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan1->id]);

        $this->scan2 = factory(\App\Scan::class)->create(['site_id' => $site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan2->id]);

        $this->user = factory(\App\User::class)->create();

        factory(\App\Scan::class)->create(['site_id' => $site->id]);  // add another so there are two to start with
    }


    /** @test */
    public function delete_many_scans_and_all_pages()
    {
        // check two before start§
        $this->assertEquals(3, \App\Scan::count());
        $response = $this->actingAs($this->user)->delete('scans',[
            'id' => [
                $this->scan1->id,
                $this->scan2->id,
                9999 // ignore if already deleted
            ]
        ]);
        $response->assertRedirect('http://localhost');
        $this->assertEquals(1, \App\Scan::count());
        $this->assertNotEquals($this->scan1->id, \App\Scan::first()->id);
        $this->assertNotEquals($this->scan2->id, \App\Scan::first()->id);
        $this->assertEquals(0, \App\Page::count());
    }

}
