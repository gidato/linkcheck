<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Page;
use App\Scan;
use App\Site;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
use Ramsey\Uuid\Uuid;
use App\Support\Service\LinkInserter;
use App\Support\Service\Scan\PageProcessor;
use App\Support\Service\Scan\ContentHandler\ContentHandlerManager;
use Mockery;
use App\Support\Service\HttpClient;
use Psr\Http\Message\ResponseInterface as HttpResponse;

class PageProcessorTest extends TestCase
{
    use RefreshDatabase;

    private $url;
    private $page;
    private $scan;
    private $headers;

    protected function setUp() : void
    {
        parent::setUp();
        $this->url = new Url('http://localhost');

        $this->site = Site::create([
            'url' => new Url('http://localhost'),
            'throttle' => new Throttle('default:default'),
            'filter_internal_only' => true,
            'validation_code' => Uuid::Uuid4()
        ]);

        $this->scan = Scan::create([
            'site_id' => $this->site->id,
            'status' => 'processing',
        ]);

        $this->page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => $this->url,
            'method' => 'get', // all redirects are GET
            'depth' => 1,
        ]);
    }

    /** @test */
    public function should_fetch_page_and_update_details_for_found_page()
    {
        $page = $this->page;

        $response = Mockery::mock(HttpResponse::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-History')->andReturn([]);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-Status-History')->andReturn([]);
        $response->shouldReceive('getHeader')->twice()->with('Content-Type')->andReturn(['text/html; charsetUTF-8;']);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('getPage')->with($page)->andReturn($response);

        $linkExtractionManager = Mockery::mock(ContentHandlerManager::class);
        $linkExtractionManager->shouldReceive('handle')->once()->with($page, $response);

        $processor = new PageProcessor($httpClient, $linkExtractionManager, new LinkInserter);
        $processor->handle($page);

        $this->assertCount(1, Page::all());
        $this->assertEquals(200, $page->status_code);
        $this->assertEquals('text/html', $page->mime_type);
    }

    /** @test */
    public function should_fetch_page_and_update_details_for_found_page_with_redirects()
    {
        $page = $this->page;

        $response = Mockery::mock(HttpResponse::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-History')->andReturn(['http://link1.com/','http://link2.com/']);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-Status-History')->andReturn([301, 302]);
        $response->shouldReceive('getHeader')->twice()->with('Content-Type')->andReturn(['text/html; charsetUTF-8;']);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('getPage')->with($page)->andReturn($response);

        $linkExtractionManager = Mockery::mock(ContentHandlerManager::class);
        $linkExtractionManager->shouldReceive('handle')->never();

        $processor = new PageProcessor($httpClient, $linkExtractionManager, new LinkInserter);
        $processor->handle($page);

        $this->assertCount(3, Page::all());
        $this->assertEquals(301, $page->status_code);
        $this->assertEquals('http://link1.com/', $page->redirect);

        $page = Page::where('id',2)->first();
        $this->assertEquals('http://link1.com/', $page->url);
        $this->assertEquals(302, $page->status_code);
        $this->assertEquals('http://link2.com/', $page->redirect);
        $this->assertEquals('get', $page->method);
        $this->assertEquals($this->scan->id, $page->scan->id);
        $this->assertEquals(1, $page->is_external);
        $this->assertEquals(2, $page->depth);


        $page = Page::where('id',3)->first();
        $this->assertEquals('http://link2.com/', $page->url);
        $this->assertEquals(200, $page->status_code);
        $this->assertEquals('get', $page->method);
        $this->assertEquals('text/html', $page->mime_type);
        $this->assertEquals($this->scan->id, $page->scan->id);
        $this->assertEquals(1, $page->is_external);
        $this->assertEquals(3, $page->depth);

        $this->assertCount(1,Page::where('id',1)->first()->referencedPages);
    }

    /** @test */
    public function should_try_to_fetch_page_and_not_found()
    {
        $page = $this->page;

        $response = Mockery::mock(HttpResponse::class);
        $response->shouldReceive('getStatusCode')->andReturn(404);

        // don't check redirect if status is not 200
        $response->shouldReceive('getHeader')->never()->with('X-Guzzle-Redirect-History');
        $response->shouldReceive('getHeader')->never()->with('X-Guzzle-Redirect-Status-History');

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('getPage')->with($page)->andReturn($response);

        $linkExtractionManager = Mockery::mock(ContentHandlerManager::class);
        $linkExtractionManager->shouldReceive('handle')->never();

        $processor = new PageProcessor($httpClient, $linkExtractionManager, new LinkInserter);
        $processor->handle($page);

        $this->assertCount(1, Page::all());
        $this->assertEquals(404, $page->status_code);

    }

    /** @test */
    public function should_fetch_html_page_and_process_content_status_200_and_not_external()
    {
        $page = $this->page;

        $response = Mockery::mock(HttpResponse::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-History')->andReturn([]);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-Status-History')->andReturn([]);
        $response->shouldReceive('getHeader')->twice()->with('Content-Type')->andReturn(['text/html; charsetUTF-8;']);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('getPage')->with($page)->andReturn($response);

        $linkExtractionManager = Mockery::mock(ContentHandlerManager::class);
        $linkExtractionManager->shouldReceive('handle')->once()->with($page, $response);

        $processor = new PageProcessor($httpClient, $linkExtractionManager, new LinkInserter);
        $processor->handle($page);

        $this->assertCount(1, Page::all());
        $this->assertEquals(200, $page->status_code);
        $this->assertEquals('text/html', $page->mime_type);

    }


    /** @test */
    public function should_not_process_content_when_external_even_when_status_200()
    {
        $page = $this->page;
        $page->is_external = true;

        $response = Mockery::mock(HttpResponse::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-History')->andReturn([]);
        $response->shouldReceive('getHeader')->once()->with('X-Guzzle-Redirect-Status-History')->andReturn([]);
        $response->shouldReceive('getHeader')->with('Content-Type')->andReturn(['text/html; charsetUTF-8;']);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('getPage')->with($page)->andReturn($response);

        $linkExtractionManager = Mockery::mock(ContentHandlerManager::class);
        $linkExtractionManager->shouldReceive('handle')->never();

        $processor = new PageProcessor($httpClient, $linkExtractionManager, new LinkInserter);
        $processor->handle($page);

        $this->assertCount(1, Page::all());
        $this->assertEquals(200, $page->status_code);
        $this->assertEquals('text/html', $page->mime_type);
    }
}
