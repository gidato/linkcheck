<?php

namespace Tests\Feature\Http\OwnersController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddTest extends TestCase
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
    public function create_form()
    {
        $response = $this->get('owners/'. $this->site->id.'/create');
        $response->assertOk();
        $response->assertViewIs('owners.create');
    }

    /** @test */
    public function store_with_no_data()
    {
        $response = $this->post('owners/'. $this->site->id, []);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name','email']);
    }

    /** @test */
    public function store_with_invalid_data()
    {
        $response = $this->post('owners/'. $this->site->id, ['name'=>'a', 'email' => 'b']);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function store_successful()
    {
        $this->assertEquals(5, \App\Owner::count());
        $response = $this->post('owners/'. $this->site->id, ['name'=>'a', 'email' => 'b@example.com']);
        $response->assertRedirect();
        $this->assertEquals(6, \App\Owner::count());
        $this->assertEquals($this->site->id, \App\Owner::where('email', 'b@example.com')->first()->site->id);
    }
}
