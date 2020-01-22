<?php

namespace Tests\Feature\Http\RedirectsController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Value\Url;

class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $site;
    private $redirect;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
        factory(\App\ApprovedRedirect::class, 5)->create(['site_id' => $this->site->id]);

        $this->redirect = factory(\App\ApprovedRedirect::class)->create(['site_id' => $this->site->id]);
    }

    /** @test */
    public function delete_where_site_id_not_matching()
    {
        $response = $this->delete('sites/'. $this->site->id . '/redirects/999');
        $response->assertStatus(404);
    }

    /** @test */
    public function delete_existing_record_and_update_all_matching_scans()
    {
        $scan = factory(\App\Scan::class)->create(['site_id' => $this->site->id]);
        $page = factory(\App\Page::class)->create(['scan_id' => $scan->id, 'url' => $this->redirect->from_url, 'redirect' => $this->redirect->to_url, 'status_code' => 302, 'redirect_approved' => true]);

        $this->assertEquals(6, \App\ApprovedRedirect::count());
        $response = $this->delete('sites/'. $this->site->id . '/redirects/' . $this->redirect->id);
        $response->assertRedirect();
        $this->assertEquals(5, \App\ApprovedRedirect::count());
        $page->refresh();
        $this->assertFalse((bool) $page->redirect_approved);
    }
}
