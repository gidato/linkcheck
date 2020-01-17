<?php

namespace Tests\Unit\Content\Extractor\Html;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use DOMDocument;
use DOMXPath;
use App\Support\Service\UrlGenerator;
use App\Page;
use App\Support\Value\Url;
use App\Support\Value\Path;
use App\Support\Value\Link;
use App\Support\Value\Reference\HtmlReference;
use Mockery;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\MultipleUrlsHandler;

class MultipleUrlsTest extends TestCase
{
    private $xPath;

    public function setup() : void
    {
        parent::setup();
        $domDocument = new DOMDocument;
        $domDocument->loadHtmlFile(__DIR__ . '/sample.html');
        $this->xPath = new DOMXPath($domDocument);
        $this->urlGenerator = Mockery::mock(UrlGenerator::class);
        $this->basePath = Mockery::mock(Path::class);
        $this->page = Mockery::mock(Page::class);
    }

    /** @test */
    public function links_from_img_srcset()
    {
        $this->urlGenerator
            ->shouldReceive('getUrlForString')
            ->with('elva-fairy-480w.jpg', $this->basePath, $this->page)
            ->andReturn(new Url('http://localhost/elva-fairy-480w.jpg'));

        $this->urlGenerator
            ->shouldReceive('getUrlForString')
            ->with('elva-fairy-800w.jpg', $this->basePath, $this->page)
            ->andReturn(new Url('http://localhost/elva-fairy-800w.jpg'));

        $extractor = new MultipleUrlsHandler('img','srcset', $this->urlGenerator);
        $links = $extractor->findLinks($this->basePath, $this->page, $this->xPath);
        $this->assertCount(2, $links);
        $this->assertEquals(new HtmlReference(null,'img','srcset','get'), $links[0]->reference);
        $this->assertEquals(new Url('http://localhost/elva-fairy-480w.jpg'), $links[0]->url);
        $this->assertEquals(new HtmlReference(null,'img','srcset','get'), $links[1]->reference);
        $this->assertEquals(new Url('http://localhost/elva-fairy-800w.jpg'), $links[1]->url);
    }

}
