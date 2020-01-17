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

class UrlMetaHttpEquivHandler implements UrlHandlerInterface
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
        foreach ($xPath->query('//meta[@http-equiv="refresh"]') as $found) {
            $content = $found->getAttribute('content');
            $contentParts = array_map('trim',explode(';', $content, 2));

            if (!isset($contentParts[1])) {
                continue;
            }

            $urlString = $contentParts[1];
            if ('url=' == strtolower(substr($urlString,0,4))) {
                $urlString=trim(substr($urlString,4), " \t\n\r\0\x0B\"'");
            }

            if (empty($urlString)) {
                continue;
            }

            $url = $this->urlGenerator->getUrlForString($urlString, $basePath, $page);
            if (null !== $url) {
                $links[] = new Link ($url, new HtmlReference(null, 'meta', 'content'));
            }
        }
        return $links;
    }



}
