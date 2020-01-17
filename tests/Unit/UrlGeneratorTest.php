<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Service\UrlGenerator;
use App\Support\Value\Url;
use App\Support\Value\Path;
use App\Page;
use Mockery;

class UrlGeneratorTest extends TestCase
{
    private $urlGenerator;
    private $base;
    private $page;

    public function setup() : void
    {
        $this->urlGenerator = new UrlGenerator;
        $this->base = new Path(new Url('http://localhost/one/'));
        $this->page = Mockery::mock(Page::class);
        $this->page->shouldReceive('getAttribute')->andReturn(new Url('http://localhost/four/xxx?abc=456#def'));
    }

    /** @test */
    public function relative_path_added_to_base()
    {
        $url = $this->urlGenerator->getUrlForString('two/three', $this->base, $this->page);
        $this->assertEquals('http://localhost/one/two/three', (string) $url);
    }

    /** @test */
    public function absolute_path_added_without_domain_added_to_base_domain()
    {
        $url = $this->urlGenerator->getUrlForString('/two/three', $this->base, $this->page);
        $this->assertEquals('http://localhost/two/three', (string) $url);
    }

    /** @test */
    public function absolute_path_with_schema_returned_when_http()
    {
        $url = $this->urlGenerator->getUrlForString('http://example.com/two/three', $this->base, $this->page);
        $this->assertEquals('http://example.com/two/three', (string) $url);
    }

    /** @test */
    public function absolute_path_with_schema_returned_when_https()
    {
        $url = $this->urlGenerator->getUrlForString('https://example.com/two/three', $this->base, $this->page);
        $this->assertEquals('https://example.com/two/three', (string) $url);
    }

    /** @test */
    public function null_returned_when_url_has_schema_returned_not_http_or_https()
    {
        $url = $this->urlGenerator->getUrlForString('mailto://test@example.com', $this->base, $this->page);
        $this->assertEquals(null, $url);
    }

    /** @test */
    public function trims_quotes_and_spaces()
    {
        $url = $this->urlGenerator->getUrlForString('"two/three"', $this->base, $this->page);
        $this->assertEquals('http://localhost/one/two/three', (string) $url);

        $url = $this->urlGenerator->getUrlForString("'two/three'", $this->base, $this->page);
        $this->assertEquals('http://localhost/one/two/three', (string) $url);

        $url = $this->urlGenerator->getUrlForString("  'two/three'  ", $this->base, $this->page);
        $this->assertEquals('http://localhost/one/two/three', (string) $url);
    }

    /** @test */
    public function empty_string_returns_last_part_added_to_base()
    {
        $url = $this->urlGenerator->getUrlForString('', $this->base, $this->page);
        $this->assertEquals((string) $this->base->url . 'xxx?abc=456', (string) $url);
    }

    /** @test */
    public function anything_after_the_hash_is_removed()
    {
        $url = $this->urlGenerator->getUrlForString('two/three?a=2#666', $this->base, $this->page);
        $this->assertEquals('http://localhost/one/two/three?a=2', (string) $url);
    }

    /** @test */
    public function double_dots_directories_are_removed_along_with_receding_directory()
    {
        $url = $this->urlGenerator->getUrlForString('../fred/john/../bill', $this->base, $this->page);
        $this->assertEquals('http://localhost/fred/bill', (string) $url);
    }

    /** @test */
    public function single_dot_directories_are_removed()
    {
        $url = $this->urlGenerator->getUrlForString('./fred/john/./././././bill', $this->base, $this->page);
        $this->assertEquals('http://localhost/one/fred/john/bill', (string) $url);
    }

    /** @test */
    public function accidential_empty_directories_are_removed()
    {
        $url = $this->urlGenerator->getUrlForString('/////////////////bill', $this->base, $this->page);
        $this->assertEquals('http://localhost/bill', (string) $url);
    }
}
