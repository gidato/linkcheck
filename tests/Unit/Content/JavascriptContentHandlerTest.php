<?php

namespace Tests\Unit\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use InvalidArgumentException;
use App\Support\Service\Scan\ContentHandler\JavascriptContentHandler;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Javascript\JavascriptHandler;
use App\Page;
use App\Support\Value\Path;
use App\Support\Value\Link;
use App\Support\Value\Url;
use App\Support\Value\Reference\JavascriptReference;
use Illuminate\Support\Collection;
use App\Support\Service\LinkInserter;

class JavascriptContentHandlerTest extends TestCase
{
    /** @test */
    public function extract_all_links_and_insert_them()
    {
        $page = Mockery::mock(Page::class);
        $path = Mockery::mock(Path::class);

        $link1 = new Link(new Url('http://example.com'), new JavascriptReference());
        $link2 = new Link(new Url('http://localhost'), new JavascriptReference());

        $links = new Collection([$link1, $link2]);

        $javascriptExtractor = Mockery::mock(JavascriptHandler::class);
        $javascriptExtractor
            ->shouldReceive('findLinks')
            ->once()
            ->with($page, 'SOME JAVASCRIPT CONTENT', $path)
            ->andReturn($links);

        $linkInserter = Mockery::mock(LinkInserter::class);
        $linkInserter
            ->shouldReceive('linkFromPage')
            ->with($page, $link1)
            ->once();

        $linkInserter
            ->shouldReceive('linkFromPage')
            ->with($page, $link2)
            ->once();

        $javascriptContentHandler = new JavascriptContentHandler($javascriptExtractor, $linkInserter);
        $javascriptContentHandler->handle($page, 'SOME JAVASCRIPT CONTENT', $path);
    }


}
