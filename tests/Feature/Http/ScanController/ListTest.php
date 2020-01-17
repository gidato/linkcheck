<?php

namespace Tests\Feature\Http\ScanController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function successfully_list_all_scans()
    {
        $user = factory(\App\User::class)->create();
        $this->be($user);

        $response = $this->get('/scans');

        $response->assertStatus(200);
        $response->assertViewIs('scans.index');
        $response->assertViewHasAll(['siteId','scans','sites']);
    }
}
