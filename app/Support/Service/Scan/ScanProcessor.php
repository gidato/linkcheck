<?php

namespace App\Support\Service\Scan;

use App\Scan;
use App\Page;
use App\Support\Service\Scan\PageProcessor;
use App\Support\Service\SiteValidation\SiteValidator;
use App\Support\Service\Scan\Filter\FilterManager;

class ScanProcessor
{

    /* service to fetch and process one page */
    private $pageProcessor;

    /* service to check if site allows access for scanning */
    private $siteValidator;

    /* service to manage all possible filters */
    private $filterManager;


    public function __construct(
        PageProcessor $pageProcessor,
        SiteValidator $siteValidator,
        FilterManager $filterManager,
        ThrottleManager $throttleManager
    ){
        $this->pageProcessor = $pageProcessor;
        $this->siteValidator = $siteValidator;
        $this->filterManager = $filterManager;
        $this->throttleManager = $throttleManager;
    }

    /**
     * returns a delay if all pages are being delayed
     */
    public function handle(Scan $scan) : ?int
    {
        $site = $scan->site;
        $response = $this->siteValidator->validate($site);
        if (!$response->isOk()) {
            $scan->status = 'errors';
            $scan->message = 'Site no longer accepting LinkCheck scans - please re-validated site';
            $scan->save();

            $site->validated = false;
            $site->save();
            return null;
        }

        $pagesToProcess = $this->getPagesToProcess($scan);
        while ($pagesToProcess && $pagesToProcess->count() > 0) {
            $page = $this->getPageAtLowestDepthReadyToProcess($pagesToProcess);
            if (!$page) {
                return $this->getShortestDelayPossible($pagesToProcess);
            }
            $this->throttleManager->recordAccessingDomainNow($page);
            $this->pageProcessor->handle($page);
            $pagesToProcess = $this->getPagesToProcess($scan);
        }

        if (!$scan->hasBeenAborted()) {
            if ($scan->hasLinkErrors()) {
                $scan->status = 'errors';
            } elseif ($scan->hasWarnings()) {
                $scan->status = 'warnings';
            } else {
                $scan->status = 'success';
            }
            $scan->save();
        }

        return null;
    }

    private function getPageAtLowestDepthReadyToProcess($pages) : ?Page
    {
        $depth = $pages->first()->depth;
        foreach($pages->where('depth', $depth)->get() as $page) {
            if (!$this->throttleManager->shouldThrottle($page)) {
                return $page;
            }
        }
        return null;
    }

    private function getShortestDelayPossible($pages) : int
    {
        $depth = $pages->first()->depth;
        $smallestDelay = 1000000; // a long time
        foreach( $pages->where('depth', $depth)->get() as $page) {
            $delay = $this->throttleManager->throttleDelay($page);
            if ($delay < $smallestDelay) {
                $smallestDelay = $delay;
            }
        }
        return $smallestDelay;
    }

    private function getPagesToProcess(Scan $scan)
    {
        $scan->refresh();
        if ($scan->hasBeenAborted()) {
            return null;
        }

        // only ever process "get" and not "post" pages, etc. as anything else should change the database, and we don't want that
        $pages = Page::where('scan_id', $scan->id)
            ->where('checked', false)
            ->where('method', 'get')
            ->orderBy('depth');

        foreach ($scan->filters as $filter) {
            $pages = $this->filterManager->filter($pages, $scan, $filter);
        }

        return $pages;
    }
}
