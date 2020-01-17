<?php
declare(strict_types=1);

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;

use DOMXPath;
use App\Page;
use App\Support\Value\Path;
use App\Support\Value\Link;
use App\Support\Value\Reference\HtmlReference;
use App\Support\Service\UrlGenerator;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Javascript\JavascriptHandler;
use Illuminate\Support\Collection;

class UrlWithinInlineJavascriptHandler implements UrlHandlerInterface
{
    private $jsHandler;

    public function __construct(
        JavascriptHandler $jsHandler
    ) {
        $this->jsHandler = $jsHandler;
    }

    public function findLinks(Path $basePath, Page $page, DOMXPath $xPath) : Collection
    {
        $links = new Collection([]);
        foreach ($xPath->query('//script') as $found) {
            $script = trim($found->nodeValue);
            if (empty($script)) {
                continue;
            }

            $jsLinks = $this->jsHandler->findLinks($page, $script, $basePath);
            foreach($jsLinks as $fileLink) {
                $links[] = new Link($fileLink->url, new HtmlReference(null, 'script'));
            }
        }

        return $links;

    }

}
