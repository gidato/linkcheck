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
use App\Support\Value\Reference\CssReference;
use App\Support\Value\Reference\HtmlReference;
use Mockery;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlWithinStyleHandler;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Css\CssHandler;
use Illuminate\Support\Collection;

class InlineCssTest extends TestCase
{
    private $xPath;

    public function setup() : void
    {
        parent::setup();
        $domDocument = new DOMDocument;
        $domDocument->loadHtmlFile(__DIR__ . '/sample.html');
        $this->xPath = new DOMXPath($domDocument);
        $this->basePath = Mockery::mock(Path::class);
        $this->page = Mockery::mock(Page::class);
    }
    /** @test */
    public function all_links_from_sample_html()
    {
        $cssExtractor = Mockery::mock(CssHandler::class);
        $cssExtractor
            ->shouldReceive('findLinks')
            ->with($this->page, '.bg { background: url(/images/bg.jpeg);}', $this->basePath )
            ->andReturn(new Collection([new Link(new Url('http://localhost/images/bg.jpeg'), new CssReference())]));

        $cssExtractor
            ->shouldReceive('findLinks')
            ->with($this->page, 'background: url(/images/bg.png)', $this->basePath)
            ->andReturn(new Collection([new Link(new Url('http://localhost/images/bg.png'), new CssReference())]));

        $extractor = new UrlWithinStyleHandler($cssExtractor);
        $links = $extractor->findLinks($this->basePath, $this->page, $this->xPath);
        $this->assertCount(2, $links);
        $this->assertEquals(new HtmlReference(null,'style',null,'get'), $links[1]->reference);
        $this->assertEquals(new Url('http://localhost/images/bg.jpeg'), $links[1]->url);
        $this->assertEquals(new HtmlReference(null,'div','style','get'), $links[0]->reference);
        $this->assertEquals(new Url('http://localhost/images/bg.png'), $links[0]->url);
    }
}
