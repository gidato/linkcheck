<?php

namespace App\Support\Service\Scan\ContentHandler;

use Psr\Container\ContainerInterface;
use Gidato\Container\Contract\FactoryContract;

class ContentHandlerManagerFactory implements FactoryContract
{
    public function __invoke(ContainerInterface $app, string $requestedName, array $params = [])
    {
        $handlers = $app->container('page-content-handlers')->getAllBound();
        return new ContentHandlerManager($handlers);
    }
}
