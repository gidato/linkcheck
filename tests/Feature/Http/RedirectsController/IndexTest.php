<?php

namespace Tests\Feature\Http\RedirectsController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

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
    public function load_manage_redirects_page()
    {
        $response = $this->get('/sites/1/redirects');
        $response->assertStatus(200);
        foreach ($this->site->approvedRedirects as $redirect) {
            $response->assertSee($redirect->from_url);
            $response->assertSee($redirect->to_url);
        }
        $this->assertCount(5, $this->site->approvedRedirects);
    }
}
