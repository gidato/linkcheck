<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Page;
use App\Scan;
use App\Site;
use App\Throttle;
use App\Support\Value\Url;
use App\Support\Value\Throttle as ThrottleConfig;
use App\Support\Service\Scan\ThrottleManager;
use Mockery;

class ThrottleManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function clears_old_records_on_start_up()
    {
        $domainUrl = new Url('http://localhost');
        Throttle::create(['url' => $domainUrl, 'not_before'=>now()->subSeconds(10)]);
        $this->assertCount(1, Throttle::all());  // confirm none before start test
        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));
        $this->assertCount(0, Throttle::all());
    }

    /** @test */
    public function should_throttle_returns_false_if_no_matching_throttle_records()
    {
        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        Throttle::create(['url' => new Url('http://localhost'), 'not_before'=>now()->addSeconds(10)]);
        Throttle::create(['url' => new Url('http://example.com'), 'not_before'=>now()->subSeconds(10)]);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://example.com/fred') );

        $this->assertFalse($throttleManager->shouldThrottle($page));

    }

    /** @test */
    public function should_throttle_returns_true_if_matching_throttle_records_and_time_in_future()
    {
        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        Throttle::create(['url' => new Url('http://localhost'), 'not_before'=>now()->addSeconds(10)]);
        Throttle::create(['url' => new Url('http://example.com'), 'not_before'=>now()->subSeconds(10)]);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://localhost/fred') );

        $this->assertTrue($throttleManager->shouldThrottle($page));

    }

    /** @test */
    public function throttle_delay_zero_if_no_matching_throttle_records()
    {
        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        Throttle::create(['url' => new Url('http://localhost'), 'not_before'=>now()->addSeconds(10)]);
        Throttle::create(['url' => new Url('http://example.com'), 'not_before'=>now()->subSeconds(10)]);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://example.com/fred') );

        $this->assertEquals(0, $throttleManager->throttleDelay($page));
    }

    /** @test */
    public function throttle_delay_not_zero_if_matching_throttle_records_and_time_in_future()
    {
        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        Throttle::create(['url' => new Url('http://localhost'), 'not_before'=>now()->addSeconds(10)]);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn(new Url('http://localhost/fred') );

        $this->assertTrue($throttleManager->throttleDelay($page) >= 9);
        $this->assertTrue($throttleManager->throttleDelay($page) <= 10);
    }

    /** @test */
    public function throttle_record_created_internal()
    {
        $domainUrl = new Url('http://localhost');

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('10 : 20'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(false);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(0, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 9);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 10);
    }

    /** @test */
    public function throttle_record_created_external()
    {
        $domainUrl = new Url('http://localhost');

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('10 : 20'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(true);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(0, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 19);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 20);
    }

    /** @test */
    public function throttle_record_updated_internal()
    {
        $domainUrl = new Url('http://localhost');
        Throttle::create(['url' => $domainUrl, 'not_before'=>now()]);

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('10 : 20'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(false);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(1, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 9);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 10);
    }

    /** @test */
    public function throttle_record_updated_external()
    {
        $domainUrl = new Url('http://localhost');
        Throttle::create(['url' => $domainUrl, 'not_before'=>now()]);

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('10 : 20'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(true);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(1, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 19);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 20);
    }

    /** @test */
    public function throttle_record_created_internal_default()
    {
        $domainUrl = new Url('http://localhost');

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('default : default'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(false);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(0, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 98);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 99);
    }

    /** @test */
    public function throttle_record_created_external_default()
    {
        $domainUrl = new Url('http://localhost');

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('default : default'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(true);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(0, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 998);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 999);
    }

    /** @test */
    public function throttle_record_updated_internal_default()
    {
        $domainUrl = new Url('http://localhost');
        Throttle::create(['url' => $domainUrl, 'not_before'=>now()]);

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('default : default'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(false);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(1, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 98);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 99);
    }

    /** @test */
    public function throttle_record_updated_external_default()
    {
        $domainUrl = new Url('http://localhost');
        Throttle::create(['url' => $domainUrl, 'not_before'=>now()]);

        $url = Mockery::mock(Url::class);
        $url->shouldReceive('getDomainUrl')->andReturn($domainUrl);

        $site = Mockery::mock(Site::class);
        $site->shouldReceive('getAttribute')->with('throttle')->andReturn(new ThrottleConfig('default : default'));

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('getAttribute')->with('url')->andReturn($url);
        $page->shouldReceive('getAttribute')->with('scan')->andReturn($scan);
        $page->shouldReceive('getAttribute')->with('is_external')->andReturn(true);

        $throttleManager = new ThrottleManager(new ThrottleConfig('99 : 999'));

        $this->assertCount(1, Throttle::all());  // confirm none before start test
        $throttleManager->recordAccessingDomainNow($page);

        $this->assertCount(1, Throttle::all());
        $throttleRec = Throttle::first();
        $this->assertEquals('http://localhost/', $throttleRec->url);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) >= 998);
        $this->assertTrue($throttleRec->not_before->diffInSeconds(now()) < 999);
    }

}
