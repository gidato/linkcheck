<?php

namespace App\Support\Service\Scan;

use App\Site;
use App\Scan;
use App\Page;
use App\Filter;

class NewScanGenerator
{
    public function generateScan(Site $site) : Scan
    {
        $scan = new Scan;
        $scan->site_id = $site->id;
        $scan->status = 'queued';
        $scan->save();

        $page = new Page;
        $page->scan_id = $scan->id;
        $page->url = $site->url;
        $page->method = 'get';
        $page->depth = 0;
        $page->save();

        foreach ($site->filters as $siteFilter)
        {
            $scanFilter = new Filter;
            $scanFilter->filterable_id = $scan->id;
            $scanFilter->filterable_type = Scan::class;
            $scanFilter->on = $siteFilter->on;
            $scanFilter->key = $siteFilter->key;
            $scanFilter->parameters = $siteFilter->parameters ?? [];
            $scanFilter->save();
        }

        return $scan;
    }

}
