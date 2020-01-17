<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Scan;
use App\Page;
use App\Site;
use App\Filter;
use App\Support\Service\Scan\ScanProcessor;
use App\Support\Service\Scan\ThrottledPageProcessor;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Service\SiteValidation\SiteValidator;
use App\Support\Service\SiteValidation\Response\ResponseOk;
use App\Support\Service\SiteValidation\Response\ResponseInvalid;
use App\Support\Service\Scan\Filter\FilterManager;
use Illuminate\Support\Collection;
use Mockery;

class ScanProcessorTest extends TestCase
{
    /** @test */
    public function scan_processor_runs_until_filtered_pages_is_empty()
    {

        $methods = collect(['get']);

        $filter1 = Mockery::mock(Filter::class);
        $filters = new Collection([
            $filter1
        ]);

        $site = Mockery::mock(Site::class);

        $page = Mockery::mock(Page::class);

        $queryBuilder = Mockery::mock(Builder::class);
        $queryBuilder->shouldReceive('first')->twice()->andReturn($page, null);

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);
        $scan->shouldReceive('hasLinkErrors')->once()->andReturn(false);
        $scan->shouldReceive('setAttribute')->once()->with('status', 'success');
        $scan->shouldReceive('save')->once();
        $scan->shouldReceive('refresh');
        $scan->shouldReceive('hasBeenAborted')->andReturn(false);
        $scan->shouldReceive('hasWarnings')->once()->andReturn(false);
        $scan->shouldReceive('getAttribute')->with('filters')->andReturn($filters);

        $pageProcessor = Mockery::mock(ThrottledPageProcessor::class);
        $pageProcessor->shouldReceive('handle')->once()->with($page);

        $siteValidator = Mockery::mock(SiteValidator::class);
        $siteValidator->shouldReceive('validate')->once()->with($site)->andReturn(new ResponseOk());

        $filterManager = Mockery::mock(FilterManager::class);
        $filterManager->shouldReceive('filter')->with(Mockery::any(), $scan, $filter1)->andReturn($queryBuilder);

        $processor = new ScanProcessor($pageProcessor, $siteValidator, $filterManager);
        $processor->handle($scan);
    }

    /** @test */
    public function scan_processor_should_fail_to_tun_when_SiteValidator_rejects_site()
    {
        $methods = collect(['get']);
        $site = Mockery::mock(Site::class);
        $site->shouldReceive('setAttribute')->once()->with('validated',false);
        $site->shouldReceive('save')->once();

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->once()->with('site')->andReturn($site);
        $scan->shouldReceive('setAttribute')->once()->with('status','errors');
        $scan->shouldReceive('setAttribute')->once()->with('message','Site no longer accepting LinkCheck scans - please re-validated site');
        $scan->shouldReceive('save')->once();

        $pageProcessor = Mockery::mock(ThrottledPageProcessor::class);

        $siteValidator = Mockery::mock(SiteValidator::class);
        $siteValidator->shouldReceive('validate')->once()->with($site)->andReturn(new ResponseInvalid());

        $filterManager = Mockery::mock(FilterManager::class);

        $processor = new ScanProcessor($pageProcessor, $siteValidator, $filterManager);
        $processor->handle($scan);
    }

    /** @test */
    public function scan_should_stop_if_status_changed_to_aborted()
    {
        $methods = collect(['get']);

        $filter1 = Mockery::mock(Filter::class);
        $filters = new Collection([
            $filter1
        ]);

        $site = Mockery::mock(Site::class);

        $page = Mockery::mock(Page::class);

        $queryBuilder = Mockery::mock(Builder::class);
        $queryBuilder->shouldReceive('first')->once()->andReturn($page);

        $scan = Mockery::mock(Scan::class);
        $scan->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $scan->shouldReceive('getAttribute')->with('site')->andReturn($site);
        $scan->shouldReceive('hasLinkErrors')->never();
        $scan->shouldReceive('setAttribute')->never();
        $scan->shouldReceive('save')->never();
        $scan->shouldReceive('refresh');
        $scan->shouldReceive('hasBeenAborted')->times(3)->andReturn(false, true, true); // rechecked after loop, so true needed twice
        $scan->shouldReceive('getAttribute')->with('filters')->andReturn($filters);

        $pageProcessor = Mockery::mock(ThrottledPageProcessor::class);
        $pageProcessor->shouldReceive('handle')->once()->with($page);

        $siteValidator = Mockery::mock(SiteValidator::class);
        $siteValidator->shouldReceive('validate')->once()->with($site)->andReturn(new ResponseOk());

        $filterManager = Mockery::mock(FilterManager::class);
        $filterManager->shouldReceive('filter')->with(Mockery::any(), $scan, $filter1)->andReturn($queryBuilder);

        $processor = new ScanProcessor($pageProcessor, $siteValidator, $filterManager);
        $processor->handle($scan);
    }


}
