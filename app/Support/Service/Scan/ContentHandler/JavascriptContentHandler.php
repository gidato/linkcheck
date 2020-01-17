<?php

namespace App\Support\Service\Scan\ContentHandler;

use App\Page;
use App\Support\Service\LinkInserter;
use App\Support\Value\Link;
use App\Support\Value\Reference\JavascriptReference;
use App\Support\Service\UrlGenerator;
use App\Support\Value\Path;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Javascript\JavascriptHandler;

class JavascriptContentHandler implements ContentHandlerInterface
{
    private $javascriptLinksExtractor;
    private $linkInserter;

    public function __construct(
        JavascriptHandler $javascriptLinksExtractor,
        LinkInserter $linkInserter
    ) {
        $this->javascriptLinksExtractor = $javascriptLinksExtractor;
        $this->linkInserter = $linkInserter;
    }

    public function handle(Page $page, string $content, ?Path $basePath = null) : void
    {
        $links = $this->javascriptLinksExtractor->findLinks($page, $content, $basePath);
        $links->each(function ($link) use ($page) {
            $this->linkInserter->linkFromPage($page, $link);
        });

    }

}
