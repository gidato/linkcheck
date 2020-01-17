<?php

namespace App\Support\Service\Scan;

use App\Site;
use App\Scan;
use App\Page;
use App\Filter;

class RescanErrorsScanGenerator
{
    private $duplicator;

    public function __construct(DuplicateScan $duplicator)
    {
        $this->duplicator = $duplicator;
    }

    public function generateScan(Scan $scan) : Scan
    {
        $newScan = $this->duplicator->duplicate($scan);
        $newScan->refresh();

        // set errored pages as "not checked";
        foreach ($newScan->pages as $page)
        {
            if ($page->isError() || $page->isRedirect() || $page->hasHtmlErrors()) {
                $page->checked = false;
                $page->mime_type = null;
                $page->status_code = null;
                $page->redirect = null;
                $page->html_errors = null;
                $page->save();
            }
        }

        return $newScan;
    }

}
