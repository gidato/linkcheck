<?php

namespace Tests\Feature\Http\SitesController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create(['password' => Hash::make('ValidPassword')]);
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $scan->id]);

    }

    /** @test */
    public function load_delete_form()
    {
        $response = $this->get('sites/'. $this->site->id.'/delete');
        $response->assertOk();
        $response->assertViewIs('sites.delete');
        $response->assertSee($this->site->url);
    }

    /** @test */
    public function delete_invalid_password()
    {
        $response = $this->delete('sites/'. $this->site->id, ['password' => 'invalid']);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function delete_valid()
    {
        // confirm set up before start
        $this->assertEquals(1, \App\Site::count());
        $this->assertEquals(1, \App\Scan::count());
        $this->assertEquals(10, \App\Page::count());


        $response = $this->delete('sites/'. $this->site->id, ['password' => 'ValidPassword']);
        $response->assertRedirect();

        $this->assertEquals(0, \App\Site::count());
        $this->assertEquals(0, \App\Scan::count());
        $this->assertEquals(0, \App\Page::count());
    }

}
