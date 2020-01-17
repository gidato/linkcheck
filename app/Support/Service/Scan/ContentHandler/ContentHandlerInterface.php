<?php

namespace App\Support\Service\Scan\ContentHandler;

use App\Page;
use App\Support\Value\Path;

interface ContentHandlerInterface
{
    public function handle(Page $page, string $content, ?Path $basePath = null) : void;

}
