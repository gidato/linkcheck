<?php
declare(strict_types=1);

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;

use DOMXPath;
use App\Page;
use App\Support\Value\Path;
use Illuminate\Support\Collection;

interface UrlHandlerInterface
{
    public function findLinks(Path $basePath, Page $page, DOMXPath $xPath) : Collection;
}
