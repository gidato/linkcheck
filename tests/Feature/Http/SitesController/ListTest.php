<?php

namespace Tests\Feature\Http\SitesController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ListTest extends TestCase
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
    public function successfully_list_all_scans()
    {
        $user = factory(\App\User::class)->create();
        $this->be($user);

        $response = $this->get('/sites');

        $response->assertStatus(200);
        $response->assertViewIs('sites.index');
        $response->assertViewHasAll(['sites']);
        $response->assertSeeText((string) $this->site->url);
        $response->assertSeeText('NEW SCAN');
        $response->assertSee('<input type="hidden" name="site_id" value="' . $this->site->id.'">');

    }
}
