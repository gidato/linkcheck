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
use App\Support\Value\Reference\JavascriptReference;
use App\Support\Value\Reference\HtmlReference;
use Mockery;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlWithinInlineJavascriptHandler;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Javascript\JavascriptHandler;
use Illuminate\Support\Collection;

class InlineJavascriptTest extends TestCase
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
        $javascriptExtractor = Mockery::mock(JavascriptHandler::class);
        $javascriptExtractor
            ->shouldReceive('findLinks')
            ->with($this->page, 'import "somefile.js";
        alert(\'hello world\');', $this->basePath)
            ->andReturn(new Collection([new Link(new Url('http://localhost/somefile.js'), new JavascriptReference())]));

        $extractor = new UrlWithinInlineJavascriptHandler($javascriptExtractor);
        $links = $extractor->findLinks($this->basePath, $this->page, $this->xPath);
        $this->assertCount(1, $links);
        $this->assertEquals(new HtmlReference(null,'script',null,'get'), $links[0]->reference);
        $this->assertEquals(new Url('http://localhost/somefile.js'), $links[0]->url);
    }
}
