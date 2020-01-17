<?php

namespace Tests\Feature\Http\OwnersController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Service\SiteValidation\SiteValidator;
use Mockery;
use App\Support\Service\SiteValidation\Response\ResponseOk;
use App\Support\Service\SiteValidation\Response\ResponseInvalid;

class CheckValidationCodeTest extends TestCase
{
    use RefreshDatabase;

    private $site;
    private $verificationService;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create(['validated' => false]);
        $this->verificationService = Mockery::mock(SiteValidator::class);

        $this->app->instance(SiteValidator::class, $this->verificationService);
    }

    /** @test */
    public function check_the_code_succeeds()
    {
        $this->withoutExceptionHandling();
        $this->verificationService
            ->shouldReceive('validate')
            ->with(Mockery::on(function ($argument) {
                return $argument->id == $this->site->id && $argument instanceof \App\Site;
            }))
            ->andReturn(new ResponseOk);
        $response = $this->patch('sites/'. $this->site->id . '/verification-check');
        $response->assertRedirect();
        $site = \App\Site::find($this->site->id);
        $this->assertTrue((bool) $site->validated);
    }

    /** @test */
    public function check_the_code_fails()
    {
        $this->withoutExceptionHandling();
        $this->verificationService
            ->shouldReceive('validate')
            ->with(Mockery::on(function ($argument) {
                return $argument->id == $this->site->id && $argument instanceof \App\Site;
            }))
            ->andReturn(new ResponseInvalid);
        $response = $this->patch('sites/'. $this->site->id . '/verification-check');
        $response->assertRedirect();
        $site = \App\Site::find($this->site->id);
        $this->assertFalse((bool) $site->validated);
    }
}
