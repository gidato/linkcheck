<?php

namespace Tests\Feature\Http\SitesController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Value\Url;

class AddTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

    }


    /** @test */
    public function create_form()
    {
        $response = $this->get('sites/create');
        $response->assertOk();
        $response->assertViewIs('sites.create');
    }

    /** @test */
    public function test_with_no_data()
    {
        $response = $this->post('sites', []);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['url']);
    }

    /** @test */
    public function test_with_too_long_data()
    {
        $response = $this->post('sites', ['url' => 'http://' . str_repeat('a',245) . '.com']);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['url']);
    }

    /** @test */
    public function test_with_invalid_url()
    {
        $response = $this->post('sites', ['url' => 'example.com']); // missing http://
        $response->assertRedirect();
        $response->assertSessionHasErrors(['url']);
    }

    /** @test */
    public function test_with_existing_url()
    {
        factory(\App\Site::class)->create(['url' => new Url('http://example.com')]);
        $response = $this->post('sites', ['url' => 'http://example.com']);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['url']);
    }

    /** @test */
    public function store_successful()
    {
        $response = $this->post('sites', ['url' => 'http://example.com']);
        $response->assertRedirect('http://localhost/sites/1');
        $this->assertEquals(1, \App\Site::count());
        $site = \App\Site::first();
        $this->assertEquals('default : default', (string) $site->throttle);
        $this->assertEquals('http://example.com/', (string) $site->url);
        $this->assertFalse((bool) $site->verified);
    }
}
