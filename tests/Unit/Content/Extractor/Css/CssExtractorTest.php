<?php

namespace Tests\Unit\Content\Extractor\Css;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use App\Support\Service\UrlGenerator;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Css\CssHandler;
use App\Page;
use App\Support\Value\Url;
use App\Support\Value\Path;
use App\Support\Value\Link;
use App\Support\Value\Reference\CssReference;
use App\Support\Service\LinkInserter;

class CssExtractorTest extends TestCase
{

    /** @test */
    public function uses_basepath_if_set()
    {
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $extractor = new CssHandler($urlGenerator);

        $page = Mockery::mock(Page::class);
        $path = new Path(new Url('http://localhost/example/'));
        $url = new Url('http://localhost/example/test/picture.jpg');
        $urlGenerator->shouldReceive('getUrlForString')
            ->with('test/picture.jpg',$path, $page)
            ->andReturn($url);

        $links = $extractor->findLinks($page, 'fred: {background: url(test/picture.jpg);}', $path);
        $this->assertCount(1, $links);
        $this->assertEquals($url, $links[0]->url);
        $this->assertEquals(new CssReference, $links[0]->reference);

    }

    /** @test */
    public function uses_pagepath_if_basepath_notset()
    {
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $extractor = new CssHandler($urlGenerator);

        $page = Mockery::mock(Page::class);
        $pageUrl = new Url('http://localhost/another_example/');
        $page->shouldReceive('getAttribute')->with('url')->andReturn($pageUrl);

        $url = new Url('http://localhost/another_example/test/picture.jpg');
        $urlGenerator->shouldReceive('getUrlForString')
            ->withArgs(function ($str, $path, $parmPage) use ($page, $pageUrl) {
                return 'test/picture.jpg' == $str
                    && $path instanceof Path
                    && $path->url == $pageUrl
                    && $parmPage == $page;
                },
                $page
            )
            ->andReturn($url);

        $links = $extractor->findLinks($page, 'fred: {background: url(test/picture.jpg);}');
        $this->assertCount(1, $links);
        $this->assertEquals($url, $links[0]->url);
        $this->assertEquals(new CssReference, $links[0]->reference);
    }

    /** @test */
    public function multiple_returned_when_found()
    {
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $extractor = new CssHandler($urlGenerator);

        $page = Mockery::mock(Page::class);
        $path = new Path(new Url('http://localhost/another_example/'));

        $url1 = new Url('http://localhost/another_example/test/picture1.jpg');
        $url2 = new Url('http://localhost/another_example/test/picture2.jpg');
        $urlGenerator->shouldReceive('getUrlForString')
            ->with('test/picture1.jpg', $path, $page)->andReturn($url1);
        $urlGenerator->shouldReceive('getUrlForString')
            ->with('test/picture2.jpg', $path, $page)->andReturn($url2);

        $links = $extractor->findLinks($page, '
            .john {background: url(test/picture1.jpg);}
            .fred {background: url(test/picture2.jpg);}
        ', $path);

        $this->assertCount(2, $links);
        $this->assertEquals($url1, $links[0]->url);
        $this->assertEquals(new CssReference, $links[0]->reference);
        $this->assertEquals($url2, $links[1]->url);
        $this->assertEquals(new CssReference, $links[1]->reference);
    }

}
