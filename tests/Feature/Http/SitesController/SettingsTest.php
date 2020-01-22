<?php

namespace Tests\Feature\Http\SitesController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();
        $user = factory(\App\User::class)->create();
        $this->be($user);

        $this->site = factory(\App\Site::class)->create();

        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $scan->id]);
    }

    /** @test */
    public function successfully_list_settings()
    {
        $user = factory(\App\User::class)->create();
        $this->be($user);

        $this->withoutExceptionHandling();

        $response = $this->get('/sites/' . $this->site->id);

        $response->assertStatus(200);
        $response->assertViewIs('sites.settings');
        $response->assertViewHasAll(['site']);
        $response->assertSeeText((string) $this->site->url);
        $response->assertSeeText('Owners');
        $response->assertSeeText('Filter');
        $response->assertSeeText('Page Throttling');
        $response->assertSeeText('Approved Redirects');

    }
}
