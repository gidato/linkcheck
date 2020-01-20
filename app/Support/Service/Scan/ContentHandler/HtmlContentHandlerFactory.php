<?php

namespace App\Support\Service\Scan\ContentHandler;

use Psr\Container\ContainerInterface;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;
use App\Support\Service\LinkInserter;
use App\Support\Service\Container\Contract\ContainerFactory;

class HtmlContentHandlerFactory implements ContainerFactory
{
    public function __invoke(ContainerInterface $app, string $requestedName, array $params = [])
    {
        $handlers = $app->container('html-handlers')->getAllBound();
        $baseHandler = $handlers['base:href'];
        unset($handlers['base:href']);
        return new HtmlContentHandler($baseHandler, $handlers, $app->get(LinkInserter::class));
    }
}
