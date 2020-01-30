<?php

namespace Tests\Feature\Http\SitesController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Value\Throttle;

class RefreshValidationCodeTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create(['validated' => true]);
    }

    /** @test */
    public function refresh_the_code()
    {
        $response = $this->patch('sites/'. $this->site->id.'/verification-refresh');
        $response->assertRedirect();
        $site = \App\Site::find($this->site->id);
        $this->assertFalse((bool) $site->validated);
        $this->assertNotEquals($this->site->validation_code, $site->validation_code);
    }
}
