<?php

namespace App\Support\Service\Scan;

use App\Site;
use App\Scan;
use App\Page;
use App\Filter;

class DuplicateScan
{
    public function duplicate(Scan $scan) : Scan
    {
        $newScan = new Scan;
        $newScan->site_id = $scan->site->id;
        $newScan->status = 'queued';
        $newScan->save();

        // first insert all of the pages
        foreach ($scan->pages as $page)
        {
            $newPage = new Page;
            $newPage->scan_id = $newScan->id;
            $newPage->url = $page->url;
            $newPage->method = $page->method;
            $newPage->depth = $page->depth;
            $newPage->is_external = $page->is_external;
            $newPage->checked = $page->checked;
            $newPage->mime_type = $page->mime_type;
            $newPage->status_code = $page->status_code;
            if ($page->redirect) {
                $newPage->redirect = $page->redirect;
            }
            $newPage->html_errors = $page->html_errors;
            $newPage->save();
        }

        // now inesert all of the page links
        foreach ($scan->pages as $page)
        {
            $newPage = $this->getPage($newScan, $page->url, $page->method);
            foreach ($page->referencedPages as $reference) {
                $linkedPage =  $this->getPage($newScan, $reference->url, $reference->method);
                $newPage->referencedPages()->attach($linkedPage, [
                    'type' => $reference->pivot->type,
                    'target' => $reference->pivot->target,
                    'tag' => $reference->pivot->tag,
                    'attribute' => $reference->pivot->attribute,
                    'times' => $reference->pivot->times
                ]);
            }
        }

        foreach ($scan->filters as $siteFilter)
        {
            $newScanFilter = new Filter;
            $newScanFilter->filterable_id = $newScan->id;
            $newScanFilter->filterable_type = Scan::class;
            $newScanFilter->on = $siteFilter->on;
            $newScanFilter->key = $siteFilter->key;
            $newScanFilter->parameters = $siteFilter->parameters;
            $newScanFilter->save();
        }

        return $newScan;
    }

    private function getPage(Scan $scan, $url, string $method) : Page
    {
        return Page::where('scan_id', $scan->id)
                   ->where('url', (string) $url)
                   ->where('method', $method)
                   ->first();
    }

}
