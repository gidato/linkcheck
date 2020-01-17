<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Site;
use App\Support\Value\Url;
use Ramsey\Uuid\Uuid;
use Mockery;
use App\Support\Service\HttpClient;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use App\Support\Service\SiteValidation\SiteValidator;

class SiteValidatorTest extends TestCase
{
    private $site;
    private $headers;

    protected function setUp() : void
    {
        parent::setUp();

        $this->site = new Site();
        $this->site->url = new Url('http://localhost/fred/');
        $this->site->validation_code = Uuid::Uuid4();
    }

    /** @test */
    public function should_return_OK_if_code_found_in_url_dir_json_file()
    {
        $response = Mockery::mock(HttpResponse::class);
        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody')->once()->andReturn(
            json_encode([(string) $this->site->url => $this->site->validation_code])
        );

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url . 'linkcheck_verification.json')
            ->andReturn($response);

        $siteValidator = new SiteValidator($httpClient);
        $response = $siteValidator->validate($this->site);
        $this->assertTrue($response->isOk());
    }

    /** @test */
    public function should_return_OK_if_code_found_in_base_url_dir_json_file()
    {
        $response1 = Mockery::mock(HttpResponse::class);
        $response1->shouldReceive('getStatusCode')->once()->andReturn(404);

        $response2 = Mockery::mock(HttpResponse::class);
        $response2->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response2->shouldReceive('getBody')->once()->andReturn(
            json_encode([(string) $this->site->url => $this->site->validation_code])
        );

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url . 'linkcheck_verification.json')
            ->andReturn($response1);

        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url->getDomainUrl() . 'linkcheck_verification.json')
            ->andReturn($response2);

        $siteValidator = new SiteValidator($httpClient);
        $response = $siteValidator->validate($this->site);
        $this->assertTrue($response->isOk());
    }

    /** @test */
    public function should_return_Invalid_if_no_verifation_file_found()
    {
        $response1 = Mockery::mock(HttpResponse::class);
        $response1->shouldReceive('getStatusCode')->once()->andReturn(404);

        $response2 = Mockery::mock(HttpResponse::class);
        $response2->shouldReceive('getStatusCode')->once()->andReturn(404);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url . 'linkcheck_verification.json')
            ->andReturn($response1);

        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url->getDomainUrl() . 'linkcheck_verification.json')
            ->andReturn($response2);

        $siteValidator = new SiteValidator($httpClient);
        $response = $siteValidator->validate($this->site);
        $this->assertFalse($response->isOk());
    }

    /** @test */
    public function should_return_Invalid_if_data_not_valid_json()
    {
        $response1 = Mockery::mock(HttpResponse::class);
        $response1->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response1->shouldReceive('getBody')->once()->andReturn('Rubbish');

        $response2 = Mockery::mock(HttpResponse::class);
        $response2->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response2->shouldReceive('getBody')->once()->andReturn('Rubbish');

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url.'linkcheck_verification.json')
            ->andReturn($response1);

        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url->getDomainUrl() . 'linkcheck_verification.json')
            ->andReturn($response2);


        $siteValidator = new SiteValidator($httpClient);
        $response = $siteValidator->validate($this->site);
        $this->assertFalse($response->isOk());
    }

    /** @test */
    public function should_return_Invalid_if_url_not_in_json_keys()
    {
        $response1 = Mockery::mock(HttpResponse::class);
        $response1->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response1->shouldReceive('getBody')->once()->andReturn(
            json_encode(['unknown' => $this->site->validation_code])
        );

        $response2 = Mockery::mock(HttpResponse::class);
        $response2->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response2->shouldReceive('getBody')->once()->andReturn(
            json_encode(['unknown' => $this->site->validation_code])
        );

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url.'linkcheck_verification.json')
            ->andReturn($response1);

        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url->getDomainUrl() . 'linkcheck_verification.json')
            ->andReturn($response2);


        $siteValidator = new SiteValidator($httpClient);
        $response = $siteValidator->validate($this->site);
        $this->assertFalse($response->isOk());
    }

    /** @test */
    public function should_return_Invalid_if_validation_code_is_incorrect()
    {
        $response1 = Mockery::mock(HttpResponse::class);
        $response1->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response1->shouldReceive('getBody')->once()->andReturn(
            json_encode([(string) $this->site->url => 'wrong'])
        );

        $response2 = Mockery::mock(HttpResponse::class);
        $response2->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response2->shouldReceive('getBody')->once()->andReturn(
            json_encode([(string) $this->site->url => 'wrong'])
        );

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url.'linkcheck_verification.json')
            ->andReturn($response1);

        $httpClient
            ->shouldReceive('getUrl')
            ->once()
            ->with((string) $this->site->url->getDomainUrl() . 'linkcheck_verification.json')
            ->andReturn($response2);


        $siteValidator = new SiteValidator($httpClient);
        $response = $siteValidator->validate($this->site);
        $this->assertFalse($response->isOk());
    }
}
