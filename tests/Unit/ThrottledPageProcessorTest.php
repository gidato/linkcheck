<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Page;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
use App\Support\Service\Sleeper;
use App\Support\Service\Scan\PageProcessor;
use App\Support\Service\Scan\ThrottledPageProcessor;
use Mockery;

class ThrottledPageProcessorTest extends TestCase
{

    /** @test */
    public function no_delay_when_domain_never_visited()
    {
        $page = Mockery::mock(Page::class);

        $url = new Url('http://localhost');
        $page->shouldReceive('getAttribute')->once()->with('url')->andReturn($url);

        $pageProcessor = Mockery::mock(PageProcessor::class);
        $pageProcessor->shouldReceive('handle')->once()->with($page);

        $sleeper = Mockery::mock(Sleeper::class);
        $sleeper->shouldReceive('sleep')->never();

        $processor = new ThrottledPageProcessor($pageProcessor, $sleeper, new Throttle('1:2'));
        $processor->handle($page);
    }

    /** @test */
    public function internal_site_using_default_when_already_accessed()
    {
        $page = Mockery::mock(Page::class);

        $url = new Url('http://localhost');
        $page->shouldReceive('getAttribute')->twice()->with('url')->andReturn($url);

        $scan = (object) ['site' => (object) ['throttle' => new Throttle('default:default')]];
        $page->shouldReceive('getAttribute')->once()->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->once()->with('is_external')->andReturn(false);

        $pageProcessor = Mockery::mock(PageProcessor::class);
        $pageProcessor->shouldReceive('handle')->twice()->with($page);

        $sleeper = Mockery::mock(Sleeper::class);
        $sleeper->shouldReceive('sleep')->once()->with(Mockery::on(function($arg) {
            $expect = 1;
            return ($arg < $expect * 1000000 && $arg > ($expect - 1) * 1000000);
        }));

        $processor = new ThrottledPageProcessor($pageProcessor, $sleeper, new Throttle('1:2'));
        $processor->handle($page); // to set up the first access of the domain;
        $processor->handle($page);
    }

    /** @test */
    public function external_site_using_default_when_already_accessed()
    {
        $page = Mockery::mock(Page::class);

        $url = new Url('http://localhost');
        $page->shouldReceive('getAttribute')->twice()->with('url')->andReturn($url);

        $scan = (object) ['site' => (object) ['throttle' => new Throttle('default:default')]];
        $page->shouldReceive('getAttribute')->once()->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->once()->with('is_external')->andReturn(true);

        $pageProcessor = Mockery::mock(PageProcessor::class);
        $pageProcessor->shouldReceive('handle')->twice()->with($page);

        $sleeper = Mockery::mock(Sleeper::class);
        $sleeper->shouldReceive('sleep')->once()->with(Mockery::on(function($arg) {
            $expect = 2;
            return ($arg < $expect * 1000000 && $arg > ($expect - 1) * 1000000);
        }));

        $processor = new ThrottledPageProcessor($pageProcessor, $sleeper, new Throttle('1:2'));
        $processor->handle($page); // to set up the first access of the domain;
        $processor->handle($page);
    }

    /** @test */
    public function internal_site_overriding_default_when_already_accessed()
    {
        $page = Mockery::mock(Page::class);

        $url = new Url('http://localhost');
        $page->shouldReceive('getAttribute')->twice()->with('url')->andReturn($url);

        $scan = (object) ['site' => (object) ['throttle' => new Throttle('3:4')]];
        $page->shouldReceive('getAttribute')->once()->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->once()->with('is_external')->andReturn(false);

        $pageProcessor = Mockery::mock(PageProcessor::class);
        $pageProcessor->shouldReceive('handle')->twice()->with($page);

        $sleeper = Mockery::mock(Sleeper::class);
        $sleeper->shouldReceive('sleep')->once()->with(Mockery::on(function($arg) {
            $expect = 3;
            return ($arg < $expect * 1000000 && $arg > ($expect - 1) * 1000000);
        }));

        $processor = new ThrottledPageProcessor($pageProcessor, $sleeper, new Throttle('1:2'));
        $processor->handle($page); // to set up the first access of the domain;
        $processor->handle($page);
    }

    /** @test */
    public function external_site_overriding_default_when_already_accessed()
    {
        $page = Mockery::mock(Page::class);

        $url = new Url('http://localhost');
        $page->shouldReceive('getAttribute')->twice()->with('url')->andReturn($url);

        $scan = (object) ['site' => (object) ['throttle' => new Throttle('3:4')]];
        $page->shouldReceive('getAttribute')->once()->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->once()->with('is_external')->andReturn(true);

        $pageProcessor = Mockery::mock(PageProcessor::class);
        $pageProcessor->shouldReceive('handle')->twice()->with($page);

        $sleeper = Mockery::mock(Sleeper::class);
        $sleeper->shouldReceive('sleep')->once()->with(Mockery::on(function($arg) {
            $expect = 4;
            return ($arg < $expect * 1000000 && $arg > ($expect - 1) * 1000000);
        }));

        $processor = new ThrottledPageProcessor($pageProcessor, $sleeper, new Throttle('1:2'));
        $processor->handle($page); // to set up the first access of the domain;
        $processor->handle($page);
    }

}
