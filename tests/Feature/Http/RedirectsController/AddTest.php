<?php

namespace Tests\Feature\Http\RedirectsController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Value\Url;

class AddTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $site;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
        factory(\App\ApprovedRedirect::class, 5)->create(['site_id' => $this->site->id]);
    }

    /** @test */
    public function store_with_no_data()
    {
        $response = $this->post('sites/'. $this->site->id . '/redirects', []);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['from_url','to_url']);
    }

    /** @test */
    public function store_with_invalid_data()
    {
        $response = $this->post('sites/'. $this->site->id . '/redirects', ['from_url' => 'a', 'to_url' => 'b']);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['from_url','to_url']);
    }

    /** @test */
    public function store_where_record_exists()
    {
        $from = new Url($this->faker()->url);
        $to = new Url($this->faker()->url);
        factory(\App\ApprovedRedirect::class, 1)->create(['site_id' => $this->site->id, 'from_url' => $from, 'to_url' => $to]);

        $this->assertEquals(6, \App\ApprovedRedirect::count());
        $response = $this->post('sites/'. $this->site->id . '/redirects', ['from_url' => (string) $from, 'to_url' => (string) $to]);
        $response->assertRedirect();
        $this->assertEquals(6, \App\ApprovedRedirect::count());
    }

    /** @test */
    public function store_where_record_is_new()
    {
        $from = new Url($this->faker()->url);
        $to = new Url($this->faker()->url);

        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id]);
        $page = factory(\App\Page::class)->create(['scan_id' => $scan->id, 'url' => $from, 'redirect' => $to, 'status_code' => 302, 'redirect_approved' => false]);

        $this->assertEquals(5, \App\ApprovedRedirect::count());
        $response = $this->post('sites/'. $this->site->id . '/redirects', ['from_url' => (string) $from, 'to_url' => (string) $to]);
        $response->assertRedirect();
        $this->assertEquals(6, \App\ApprovedRedirect::count());
        $page->refresh();
        $this->assertTrue((bool) $page->redirect_approved);
    }
}
