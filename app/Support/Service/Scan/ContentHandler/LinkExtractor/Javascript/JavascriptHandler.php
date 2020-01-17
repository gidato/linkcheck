<?php

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Javascript;

use App\Page;
use App\Support\Value\Link;
use App\Support\Value\Reference\JavascriptReference;
use App\Support\Service\UrlGenerator;
use App\Support\Value\Path;
use Illuminate\Support\Collection;

class JavascriptHandler
{
    private $urlGenerator;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function findLinks(Page $page, string $content, ?Path $basePath = null) : Collection
    {
        $links = new Collection([]);

        $basePath = $basePath ?? new Path($page->url->getDirectory());

        while (preg_match('/import\s([^\;]*?\sfrom\s)?[\'\"](.+?)[\'\"](.*)/is', $content, $matches)) {
            $content = $matches[3];
            $url = $this->urlGenerator->getUrlForString($matches[2], $basePath, $page);
            if (null !== $url) {
                $links[] = new Link($url, new JavascriptReference());
            }
        }

        while (preg_match('/import\([\'\"](.+?)[\'\"]\)(.*)/is', $content, $matches)) {
            $content = $matches[2];
            $url = $this->urlGenerator->getUrlForString($matches[1], $basePath, $page);
            if (null !== $url) {
                $links[] = new Link($url, new JavascriptReference());
            }
        }
        return $links;
    }
}
