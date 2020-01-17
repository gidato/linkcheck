<?php

namespace App\Support\Service\Scan;

use App\Site;
use App\Scan;
use App\Page;
use App\Filter;

class RescanReferrersScanGenerator
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
            if ($page->isError()) {
                foreach ($page->referredInPages as $referrer) {
                    $referrer->checked = false;
                    $referrer->mime_type = null;
                    $referrer->status_code = null;
                    $referrer->redirect = null;
                    $referrer->html_errors = null;
                    $referrer->save();
                }
                $page->delete();
            }
        }

        return $newScan;
    }

}
