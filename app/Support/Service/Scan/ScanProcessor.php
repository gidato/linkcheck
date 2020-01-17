<?php

namespace App\Support\Service\Scan;

use App\Scan;
use App\Page;
use App\Support\Service\Scan\ThrottledPageProcessor;
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
        ThrottledPageProcessor $pageProcessor,
        SiteValidator $siteValidator,
        FilterManager $filterManager
    ){
        $this->pageProcessor = $pageProcessor;
        $this->siteValidator = $siteValidator;
        $this->filterManager = $filterManager;
    }

    public function handle(Scan $scan)
    {
        $site = $scan->site;
        $response = $this->siteValidator->validate($site);
        if (!$response->isOk()) {
            $scan->status = 'errors';
            $scan->message = 'Site no longer accepting LinkCheck scans - please re-validated site';
            $scan->save();

            $site->validated = false;
            $site->save();
            return;
        }

        while($page = $this->getPageToProcess($scan)) {
            $this->pageProcessor->handle($page);
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
    }

    private function getPageToProcess(Scan $scan) : ?Page
    {
        $scan->refresh();
        if ($scan->hasBeenAborted()) {
            return null;
        }

        // only ever process get pages, as anything else should change the database, and we don't want that
        $pages = Page::where('scan_id', $scan->id)
            ->where('checked', false)
            ->where('method', 'get')
            ->orderBy('depth');

        foreach ($scan->filters as $filter) {
            $pages = $this->filterManager->filter($pages, $scan, $filter);
        }

        return $pages->first();
    }
}
