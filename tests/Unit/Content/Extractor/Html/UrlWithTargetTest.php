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
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlWithTargetHandler;

class UrlWithTargetTest extends TestCase
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
    public function all_links_from_sample_html()
    {
        $this->urlGenerator
            ->shouldReceive('getUrlForString')
            ->with('http://bbc.co.uk', $this->basePath, $this->page)
            ->andReturn(new Url('http://bbc.co.uk'));

        $this->urlGenerator
            ->shouldReceive('getUrlForString')
            ->with('about-us', $this->basePath, $this->page)
            ->andReturn(new Url('http://localhost/about-us'));

        $extractor = new UrlWithTargetHandler('a','href', $this->urlGenerator);
        $links = $extractor->findLinks($this->basePath, $this->page, $this->xPath);
        $this->assertCount(2, $links);
        $this->assertEquals(new HtmlReference('_blank','a','href','get'), $links[0]->reference);
        $this->assertEquals(new Url('http://bbc.co.uk'), $links[0]->url);
        $this->assertEquals(new HtmlReference(null,'a','href','get'), $links[1]->reference);
        $this->assertEquals(new Url('http://localhost/about-us'), $links[1]->url);
    }
}
