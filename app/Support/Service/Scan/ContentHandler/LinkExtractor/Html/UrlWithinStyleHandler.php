<?php
declare(strict_types=1);

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;

use DOMXPath;
use App\Page;
use App\Support\Value\Path;
use App\Support\Value\Link;
use App\Support\Value\Reference\HtmlReference;
use App\Support\Service\UrlGenerator;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Css\CssHandler;
use Illuminate\Support\Collection;

class UrlWithinStyleHandler implements UrlHandlerInterface
{
    private $cssHandler;

    public function __construct(
        CssHandler $cssHandler
    ) {
        $this->cssHandler = $cssHandler;
    }

    public function findLinks(Path $basePath, Page $page, DOMXPath $xPath) : Collection
    {
        $links = new Collection([]);
        $links = $links->merge($this->findStyleAttributePages($basePath, $page, $xPath));
        $links = $links->merge($this->findStyleTagPages($basePath, $page, $xPath));
        return $links;
    }

    private function findStyleAttributePages(Path $basePath, Page $page, DOMXPath $xPath) : Collection
    {
        $links = new Collection([]);
        foreach ($xPath->query('//*[@style]') as $found) {
            $style = $found->getAttribute('style');
            $tag = $found->nodeName;
            foreach ($this->cssHandler->findLinks($page, trim($style), $basePath) as $link) {
                $links[] = new Link( $link->url, new HtmlReference(null, $tag, 'style'));
            }
        }
        return $links;
    }

    private function findStyleTagPages(Path $basePath, Page $page, DOMXPath $xPath) : Collection
    {
        $links = new Collection([]);
        foreach ($xPath->query('//style') as $found) {
            $style = $found->nodeValue;
            foreach ($this->cssHandler->findLinks($page, trim($style), $basePath) as $link) {
                $links[] = new Link( $link->url, new HtmlReference(null, 'style'));
            }
        }
        return $links;
    }

}
