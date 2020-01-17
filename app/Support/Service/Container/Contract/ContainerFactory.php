<?php

namespace App\Support\Service\Container\Contract;

use Psr\Container\ContainerInterface;

interface ContainerFactory
{
    public function __invoke(ContainerInterface $container, string $requestedName, array $parameters = []);
}
