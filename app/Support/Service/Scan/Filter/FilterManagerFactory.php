<?php

namespace App\Support\Service\Scan\Filter;

use Psr\Container\ContainerInterface;
use Gidato\Container\Contract\FactoryContract;

class FilterManagerFactory implements FactoryContract
{
    public function __invoke(ContainerInterface $app, string $requestedName, array $params = [])
    {
        $filters = $app->container('scan-filters')->getAllBound();
        return new FilterManager($filters);
    }
}
