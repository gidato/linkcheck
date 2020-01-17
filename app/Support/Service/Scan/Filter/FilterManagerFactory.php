<?php

namespace App\Support\Service\Scan\Filter;

use Psr\Container\ContainerInterface;

class FilterManagerFactory
{
    public function __invoke(ContainerInterface $app, string $requestedName, array $params = [])
    {
        $filters = $app->container('scan-filters')->getAllDirectlyBound();
        return new FilterManager($filters);
    }
}
