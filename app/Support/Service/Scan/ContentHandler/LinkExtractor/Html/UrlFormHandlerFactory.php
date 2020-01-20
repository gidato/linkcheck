<?php
declare(strict_types=1);

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;

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
