<?php
declare(strict_types=1);

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;

use DOMXPath;
use App\Page;
use App\Support\Value\Path;
use App\Support\Value\Link;
use App\Support\Value\Reference\HtmlReference;
use App\Support\Service\UrlGenerator;
use Illuminate\Support\Collection;

class UrlFormHandler implements UrlHandlerInterface
{
    private $urlGenerator;

    public function __construct(
        UrlGenerator $urlGenerator
    ) {
        $this->urlGenerator = $urlGenerator;
    }

    public function findLinks(Path $basePath, Page $page, DOMXPath $xPath) : Collection
    {
        $links = new Collection([]);
        foreach ($xPath->query('//form') as $found) {
            if ($found->hasAttribute('action')) {
                $urlString = $found->getAttribute('action');
            } else {
                $urlString = '';
            }

            $url = $this->urlGenerator->getUrlForString($urlString, $basePath, $page);

            $method = ($found->hasAttribute('method')) ? $found->getAttribute('method') : 'get';
            $links[] = new Link ($url, new HtmlReference($basePath->target, 'form', 'action', $method));

        }

        return $links;
    }



}
