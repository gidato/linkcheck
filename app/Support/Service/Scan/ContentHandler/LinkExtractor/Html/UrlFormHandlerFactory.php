<?php
declare(strict_types=1);

namespace App\Service\UrlAttribute;

use Interop\Container\ContainerInterface;
use App\Service\UriGenerator;

class UrlFormHandlerFactory
{
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null)
    {
        return new UrlFormHandler(
            $container->get(UriGenerator::class)
        );
    }
}
