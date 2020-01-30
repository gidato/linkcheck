<?php

namespace Tests\Feature\Http\SitesController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Value\Throttle;

class UpdateThrottleTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
        $this->site->throttle = new Throttle('100:200');
        $this->site->save();
        $this->site->refresh();
    }

    /** @test */
    public function load_edit_form()
    {
        $response = $this->get('sites/'. $this->site->id.'/throttling');
        $response->assertOk();
        $response->assertViewIs('sites.settings.edit-throttling');
        $response->assertSee($this->site->url);
    }

    /** @test */
    public function store_with_no_data()
    {
        $response = $this->patch('sites/'. $this->site->id.'/throttling', []);
        $response->assertRedirect();
        $site = \App\Site::find($this->site->id);
        $this->assertEquals('default : default', (string) $site->throttle);
    }

    /** @test */
    public function store_with_good_data()
    {
        $response = $this->patch('sites/'. $this->site->id.'/throttling', ['internal'=>1, 'external'=>5]);
        $response->assertRedirect();
        $site = \App\Site::find($this->site->id);
        $this->assertEquals('1 : 5', (string) $site->throttle);
    }

    /** @test */
    public function store_with_invalid_data()
    {
        $response = $this->patch('sites/'. $this->site->id.'/throttling', ['internal'=>1.1, 'external'=>'fred']);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['internal','external']);
    }

}
