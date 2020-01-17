<?php

namespace Tests\Feature\Http\OwnersController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
        factory(\App\Owner::class, 5)->create(['site_id' => $this->site->id]);
    }


    /** @test */
    public function delete_owner_from_site()
    {
        // check five before start
        $this->assertEquals(5, \App\Owner::count());
        $owner = \App\Owner::first();
        $response = $this->delete('owners/'. $owner->id);
        $response->assertRedirect('http://localhost/sites/' . $this->site->id);
        $response->assertSessionHas('success','Owner deleted');
        $this->assertEquals(4, \App\Owner::count());
        $this->assertNotEquals($owner->id, \App\Owner::first()->id);
    }

    /** @test */
    public function owner_not_found()
    {
        // check five before start
        $this->assertEquals(5, \App\Owner::count());
        $response = $this->delete('owners/'. 999);
        $response->assertStatus(404);
        $this->assertEquals(5, \App\Owner::count());
    }

}
