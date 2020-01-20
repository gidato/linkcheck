<?php

namespace App\Support\Service\Scan\ContentHandler;

use Psr\Container\ContainerInterface;
use App\Support\Service\Container\Contract\ContainerFactory;

class ContentHandlerManagerFactory implements ContainerFactory
{
    public function __invoke(ContainerInterface $app, string $requestedName, array $params = [])
    {
        $handlers = $app->container('page-content-handlers')->getAllDirectlyBound();
        return new ContentHandlerManager($handlers);
    }
}
