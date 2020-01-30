<?php

namespace App\Support\Service\Container\Contract;

use Psr\Container\ContainerInterface;

interface FactoryContract
{
    public function __invoke(ContainerInterface $container, string $requestedName, array $parameters = []);
}
