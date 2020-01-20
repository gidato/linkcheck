<?php

namespace App\Support\Service\Scan\Filter;

use Psr\Container\ContainerInterface;
use App\Support\Service\Container\Contract\ContainerFactory;

class FilterManagerFactory implements ContainerFactory
{
    public function __invoke(ContainerInterface $app, string $requestedName, array $params = [])
    {
        $filters = $app->container('scan-filters')->getAllDirectlyBound();
        return new FilterManager($filters);
    }
}
