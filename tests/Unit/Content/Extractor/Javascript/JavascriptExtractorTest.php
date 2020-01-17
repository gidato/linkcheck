<?php

namespace Tests\Unit\Content\Extractor\Javascript;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use App\Support\Service\UrlGenerator;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Javascript\JavascriptHandler;
use App\Page;
use App\Support\Value\Url;
use App\Support\Value\Path;
use App\Support\Value\Link;
use App\Support\Value\Reference\JavascriptReference;

class JavascriptHandlerTest extends TestCase
{
    /** @test */
    public function uses_basepath_if_set()
    {
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $extractor = new JavascriptHandler($urlGenerator);

        $page = Mockery::mock(Page::class);
        $path = new Path(new Url('http://localhost/example/'));
        $url = new Url('http://localhost/example/module-name.js');
        $urlGenerator->shouldReceive('getUrlForString')
            ->with('module-name.js',$path, $page)
            ->andReturn($url);

        $links = $extractor->findLinks($page, 'import "module-name.js";', $path);
        $this->assertCount(1, $links);
        $this->assertEquals($url, $links[0]->url);
        $this->assertEquals(new JavascriptReference, $links[0]->reference);

    }

    /** @test */
    public function uses_pagepath_if_basepath_notset()
    {
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $extractor = new JavascriptHandler($urlGenerator);

        $page = Mockery::mock(Page::class);
        $pageUrl = new Url('http://localhost/another_example/');
        $page->shouldReceive('getAttribute')->with('url')->andReturn($pageUrl);

        $url = new Url('http://localhost/another_example/module-name.js');
        $urlGenerator->shouldReceive('getUrlForString')
            ->withArgs(function ($str, $path, $parmPage) use ($page, $pageUrl) {
                return 'module-name.js' == $str
                    && $path instanceof Path
                    && $path->url == $pageUrl
                    && $parmPage == $page;
                },
                $page
            )
            ->andReturn($url);

        $links = $extractor->findLinks($page, 'import "module-name.js";');
        $this->assertCount(1, $links);
        $this->assertEquals($url, $links[0]->url);
        $this->assertEquals(new JavascriptReference, $links[0]->reference);
    }

    /** @test */
    public function multiple_returned_when_found()
    {
        $examples = [
            'http://localhost/module-name-1' => 'import defaultExport from "module-name-1";',
            'http://localhost/module-name-2' => 'import * as name from "module-name-2";',
            'http://localhost/module-name-3' => 'import { export1 } from "module-name-3";',
            'http://localhost/module-name-4' => 'import { export1 as alias1 } from "module-name-4";',
            'http://localhost/module-name-5' => 'import { export1 , export2 } from "module-name-5";',
            'http://example.com/module-name/path/to/specific/un-exported/file' => 'import { foo , bar } from "http://example.com/module-name/path/to/specific/un-exported/file";',
            'http://localhost/module-name-6' => 'import { export1 , export2 as alias2 , [...] } from "module-name-6";',
            'http://localhost/module-name-7' => 'import defaultExport, { export1 [ , [...] ] } from "module-name-7";',
            'http://localhost/module-name-8' => 'import defaultExport, * as name from "module-name-8";',
            'http://localhost/module-name-9' => 'import "module-name-9";',
            'http://localhost/module-name-10' => 'var promise = import("module-name-10");',
        ];
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $extractor = new JavascriptHandler($urlGenerator);

        $page = Mockery::mock(Page::class);
        $path = new Path(new Url('http://localhost/'));

        foreach ($examples as $key => $url) {
            if ('http://localhost/' == substr($key,0,17)) {
                $urlGenerator->shouldReceive('getUrlForString')->with(substr($key,17), $path, $page)->andReturn(new Url($key));
            } else {
                $urlGenerator->shouldReceive('getUrlForString')->with($key, $path, $page)->andReturn(new Url($key));
            }
        }

        $links = $extractor->findLinks($page, implode("\n", $examples), $path);

        $this->assertCount(11, $links);
        $this->assertEquals(new JavascriptReference, $links[0]->reference);
        foreach (array_keys($examples) as $key => $url)
        $this->assertEquals($url, (string) $links[$key]->url);
    }

}
