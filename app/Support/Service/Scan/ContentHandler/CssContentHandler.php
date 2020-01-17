<?php

namespace App\Support\Service\Scan\ContentHandler;

use App\Page;
use App\Support\Service\LinkInserter;
use App\Support\Value\Path;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Css\CssHandler;

class CssContentHandler implements ContentHandlerInterface
{
    private $cssLinksExtractor;
    private $linkInserter;

    public function __construct(
        CssHandler $cssLinksExtractor,
        LinkInserter $linkInserter
    ) {
        $this->cssLinksExtractor = $cssLinksExtractor;
        $this->linkInserter = $linkInserter;
    }

    public function handle(Page $page, string $content, ?Path $basePath = null) : void
    {
        $links = $this->cssLinksExtractor->findLinks($page, $content, $basePath);
        $links->each(function ($link) use ($page) {
            $this->linkInserter->linkFromPage($page, $link);
        });
    }
}
