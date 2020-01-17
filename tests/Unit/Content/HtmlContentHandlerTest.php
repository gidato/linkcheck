<?php

namespace Tests\Unit\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use InvalidArgumentException;
use App\Support\Service\Scan\ContentHandler\HtmlContentHandler;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlHandlerInterface;
use App\Page;
use App\Support\Value\Url;
use App\Support\Value\Path;
use App\Support\Value\Link;
use DOMXPath;
use Illuminate\Support\Collection;
use App\Support\Service\LinkInserter;

class HtmlContentHandlerTest extends TestCase
{
    private $linkInserter;

    public function setup() : void
    {
        $this->linkInserter = Mockery::mock(LinkInserter::class);
    }

    /** @test */
    public function throws_errors_if_handlers_invalid_on_creation()
    {
        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Handlers must implement ' . UrlHandlerInterface::class);
        $handler = new HtmlContentHandler($baseHandler, ['a:href' => new \StdClass], $this->linkInserter);
    }

    /** @test */
    public function throws_errors_if_handlers_not_keyed_on_creation()
    {
        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mimetype for handler must be set');
        $handler = new HtmlContentHandler($baseHandler, [$baseHandler], $this->linkInserter);
    }

    /** @test */
    public function throws_errors_if_handlers_invalid_keyed_on_creation()
    {
        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mimetype for handler must be a string');
        $handler = new HtmlContentHandler($baseHandler, [3 => $baseHandler], $this->linkInserter);
    }

    /** @test */
    public function records_errors_if_page_badly_formed()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('setAttribute')->with('html_errors',Mockery::any())->once();
        $page->shouldReceive('save')->once();
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://localhost'));

        $path = Mockery::mock(Path::class);

        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $baseHandler->shouldReceive('findLinks')->with(Mockery::any(), $page, Mockery::any())->andReturn(new Collection);

        $otherHandler = Mockery::mock(UrlHandlerInterface::class);
        $otherHandler->shouldReceive('findLinks')->with(Mockery::any(), $page, Mockery::any())->andReturn(new Collection);

        $this->linkInserter
            ->shouldReceive('linkFromPage')
            ->never();

        $handler = new HtmlContentHandler($baseHandler, ['a:href' => $otherHandler], $this->linkInserter);
        $content = '<html><fred></fred><bob></harry></html>';
        $handler->handle($page, $content);
    }

    /** @test */
    public function records_no_errors_if_html_well_formed()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://localhost'));

        $path = Mockery::mock(Path::class);

        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $baseHandler->shouldReceive('findLinks')->with(Mockery::any(), $page, Mockery::any())->andReturn(new Collection);

        $otherHandler = Mockery::mock(UrlHandlerInterface::class);
        $otherHandler->shouldReceive('findLinks')->with(Mockery::any(), $page, Mockery::any())->andReturn(new Collection);

        $this->linkInserter
            ->shouldReceive('linkFromPage')
            ->never();

        $handler = new HtmlContentHandler($baseHandler, ['a:href' => $otherHandler], $this->linkInserter);
        $content = '<html><head></head><body></body></html>';
        $handler->handle($page, $content);
    }

    /** @test */
    public function uses_page_as_basepath_if_not_set_in_html()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://localhost'));

        $path = Mockery::mock(Path::class);

        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $baseHandler->shouldReceive('findLinks')->withArgs(function ($path, $parmPage, $xpath) use ($page) {
            return ($page === $parmPage) && ($path->url == new Url('http://localhost/'));
        })->andReturn(new Collection);

        $otherHandler = Mockery::mock(UrlHandlerInterface::class);
        $otherHandler->shouldReceive('findLinks')->withArgs(function ($path, $parmPage, $xpath) use ($page) {
            return ($page === $parmPage) && ($path->url == new Url('http://localhost/'));
        })->andReturn(new Collection);

        $this->linkInserter
            ->shouldReceive('linkFromPage')
            ->never();

        $handler = new HtmlContentHandler($baseHandler, ['a:href' => $otherHandler], $this->linkInserter);
        $content = '<html><head></head><body></body></html>';
        $handler->handle($page, $content);
    }

    /** @test */
    public function uses_basepath_when_set_in_html()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://localhost'));

        $path = Mockery::mock(Path::class);

        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $baseHandler->shouldReceive('findLinks')->withArgs(function ($path, $parmPage, $xpath) use ($page) {
            return ($page === $parmPage) && ($path->url == new Url('http://localhost/'));
        })->andReturn(new Collection([
            $path
        ]));

        $otherHandler = Mockery::mock(UrlHandlerInterface::class);
        $otherHandler->shouldReceive('findLinks')->withArgs(function ($parmPath, $parmPage, $xpath) use ($page, $path) {
            return ($page === $parmPage) && ($path == $parmPath);
        })->andReturn(new Collection);

        $this->linkInserter
            ->shouldReceive('linkFromPage')
            ->never();

        $handler = new HtmlContentHandler($baseHandler, ['a:href' => $otherHandler], $this->linkInserter);
        $content = '<html><head><meta name="base" href="http://localhost/one/" target="_blank"></head><body></body></html>';
        $handler->handle($page, $content);
    }

    /** @test */
    public function all_links_merged_when_multiple_found()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://localhost'));

        $path = Mockery::mock(Path::class);

        $baseHandler = Mockery::mock(UrlHandlerInterface::class);
        $baseHandler->shouldReceive('findLinks')->with(Mockery::any(), $page, Mockery::any())->andReturn(new Collection);

        $link1 = Mockery::mock(Link::class);
        $link2 = Mockery::mock(Link::class);

        $handler1 = Mockery::mock(UrlHandlerInterface::class);
        $handler1->shouldReceive('findLinks')
            ->with(Mockery::any(), $page, Mockery::any())
            ->andReturn(new Collection([$link1, $link2]));

        $link3 = Mockery::mock(Link::class);
        $link4 = Mockery::mock(Link::class);
        $handler2 = Mockery::mock(UrlHandlerInterface::class);
        $handler2->shouldReceive('findLinks')
            ->with(Mockery::any(), $page, Mockery::any())
            ->andReturn(new Collection([$link3, $link4]));

        $this->linkInserter->shouldReceive('linkFromPage')->with($page, $link1)->once();
        $this->linkInserter->shouldReceive('linkFromPage')->with($page, $link2)->once();
        $this->linkInserter->shouldReceive('linkFromPage')->with($page, $link3)->once();
        $this->linkInserter->shouldReceive('linkFromPage')->with($page, $link4)->once();

        $handler = new HtmlContentHandler($baseHandler, ['a:href' => $handler1, 'img:src' => $handler2], $this->linkInserter);
        $content = '<html><head></head><body></body></html>';
        $handler->handle($page, $content);
    }
}
