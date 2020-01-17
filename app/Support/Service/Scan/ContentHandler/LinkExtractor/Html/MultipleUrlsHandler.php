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

class MultipleUrlsHandler implements UrlHandlerInterface
{
    private $tag;
    private $attribute;
    private $urlGenerator;

    public function __construct(
        string $tag,
        string $attribute,
        UrlGenerator $urlGenerator
    ) {
        $this->tag = $tag;
        $this->attribute = $attribute;
        $this->urlGenerator = $urlGenerator;
    }

    public function findLinks(Path $basePath, Page $page, DOMXPath $xPath) : Collection
    {
        $links = new Collection([]);
        foreach ($xPath->query('//' . $this->tag . '[@' . $this->attribute . ']') as $found) {
            $srcset = $found->getAttribute($this->attribute);
            foreach (explode(',', $srcset) as $urlSpec) {
                $urlSpec = trim($urlSpec);
                if (empty($urlSpec)) {
                    continue;
                }
                $urlSpec = explode(' ', $urlSpec);
                $urlString = $urlSpec[0];
                $url = $this->urlGenerator->getUrlForString($urlString, $basePath, $page);
                if (null !== $url) {
                    $links[] = new Link($url, new HtmlReference($basePath->target, $this->tag, $this->attribute));
                }
            }
        }

        return $links;
    }

}
