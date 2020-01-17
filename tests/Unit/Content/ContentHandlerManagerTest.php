<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Page;
use App\Support\Service\Scan\ContentHandler\ContentHandlerManager;
use App\Support\Service\Scan\ContentHandler\ContentHandlerInterface;
use Mockery;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use InvalidArgumentException;

class ContentHandlerManagerTest extends TestCase
{
    private $page;
    private $response;
    private $htmlExtractor;
    private $site;
    private $scan;

    protected function setUp() : void
    {
        parent::setUp();
        $this->page = Mockery::mock(Page::class);
        $this->response = Mockery::mock(HttpResponse::class);
        $this->htmlExtractor = Mockery::mock(ContentHandlerInterface::class);
    }

    /** @test */
    public function extractors_should_implement_Interface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extractors must implement ' . ContentHandlerInterface::class);
        $extractionManager = new ContentHandlerManager([
            'text/html' => 'none'
        ]);
    }

    /** @test */
    public function extractor_keys_should_be_set()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mimetype for extractor must be set');
        $extractionManager = new ContentHandlerManager([
            '' => $this->htmlExtractor
        ]);
    }

    /** @test */
    public function extractor_keys_should_be_string()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mimetype for extractor must be a string');
        $extractionManager = new ContentHandlerManager([
            99 => $this->htmlExtractor
        ]);
    }

    /** @test */
    public function should_ignore_content_if_no_processor_available()
    {
        $page = $this->page;
        $response = $this->response;
        $htmlExtractor = $this->htmlExtractor;

        $page->shouldReceive('getAttribute')->with('mime_type')->andReturn('image/jpeg');
        $response->shouldReceive('getBody')->never();

        $extractionManager = new ContentHandlerManager([
            'text/html' => $htmlExtractor
        ]);
        $extractionManager->handle($page, $response);

    }

    /** @test */
    public function should_pass_content_when_processor_available()
    {
        $page = $this->page;
        $page->shouldReceive('getAttribute')->with('mime_type')->andReturn('text/html');
        $response = $this->response;
        $response->shouldReceive('getBody')->andReturn('HTML CONTENT');
        $htmlExtractor = $this->htmlExtractor;
        $htmlExtractor->shouldReceive('handle')->with($page, 'HTML CONTENT')->once();
        $extractionManager = new ContentHandlerManager([
            'text/html' => $htmlExtractor
        ]);
        $extractionManager->handle($page, $response);
    }

}
