<?php

namespace Tests\Feature\Http\OwnersController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $site;
    private $owner;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
        factory(\App\Owner::class, 5)->create(['site_id' => $this->site->id]);
        $this->owner = factory(\App\Owner::class)->create(['site_id' => $this->site->id]);
        factory(\App\Owner::class, 5)->create(['site_id' => $this->site->id]);
    }


    /** @test */
    public function load_edit_form()
    {
        $response = $this->get('owners/'. $this->owner->id.'/edit');
        $response->assertOk();
        $response->assertViewIs('owners.edit');
        $response->assertSee($this->owner->name);
    }

    /** @test */
    public function store_with_no_data()
    {
        $response = $this->put('owners/'. $this->owner->id, []);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name','email']);
    }

    /** @test */
    public function store_with_invalid_data()
    {
        $response = $this->put('owners/'. $this->owner->id, ['name'=>'a', 'email' => 'b']);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function store_successful()
    {

        $this->assertEquals(11, \App\Owner::count());
        $response = $this->put('owners/'. $this->owner->id, ['name'=>'a', 'email' => 'b@example.com']);
        $response->assertRedirect();
        $this->assertEquals(11, \App\Owner::count());
        $this->owner->refresh();
        $this->assertEquals('a', $this->owner->name);
        $this->assertEquals('b@example.com', $this->owner->email);
    }
}
