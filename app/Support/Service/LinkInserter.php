<?php

namespace App\Support\Service;

/**
 * inserts a refrence from page to link, creating the "link" page if it doesn't exist
 * returns the linked page
 */

 use App\Support\Value\Url;
 use App\Support\Value\Link;
 use App\Page;
 use App\ReferencedPagesPivot;

class LinkInserter
{
    public function linkFromPage(Page $page, Link $link) : Page
    {
        $linkedPage = $this->findOrCreatePage($page, $link);
        if ($reference = $this->getReference($page, $linkedPage, $link)) {
            $reference->times += 1;
            $reference->save();
        } else {
            $page->referencedPages()->attach($linkedPage, $link->getLinkAttributes());
        }
        return $linkedPage;
    }

    private function findOrCreatePage(Page $page, Link $link) : Page
    {
        $existingRecords = Page::where('scan_id', $page->scan->id)
                               ->where('url',(string) $link->url)
                               ->where('method', $link->method);
        if ($existingRecords->count() > 0) {
            return $existingRecords->first();
        }

        $linkedPage = Page::create([
            'scan_id' => $page->scan->id,
            'url' => $link->url,
            'method' => $link->method,
            'depth' => $page->depth + 1,
        ]);

        return $linkedPage;
    }

    private function getReference(Page $page, Page $linkedPage, Link $link) : ?ReferencedPagesPivot
    {
        $page->refresh();
        $linkReference = $link->reference;

        foreach ($page->referencedPages as $referredPage)
        {
            if ($referredPage->id != $linkedPage->id) {
                continue;
            }

            if ($referredPage->pivot->getReference($linkedPage->method) == $linkReference) {
                return $referredPage->pivot;
            }
        }

        return null;
    }
}
