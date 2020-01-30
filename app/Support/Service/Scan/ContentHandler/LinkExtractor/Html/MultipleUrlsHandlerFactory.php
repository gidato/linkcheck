<?php
declare(strict_types=1);

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;

use App\Support\Service\Container\Contract\FactoryContract;
use Psr\Container\ContainerInterface;
use App\Support\Service\UrlGenerator;

class MultipleUrlsHandlerFactory implements FactoryContract
{
    public function __invoke(ContainerInterface $container, string $requestedName, array $parameters = null)
    {
        [$tag, $attribute] = explode(':', $requestedName, 2);
        if (empty($tag) || empty($attribute)) {
            throw new \Exception('Cannot create UrlHandler for requestedName');
        }

        return new MultipleUrlsHandler(
            $tag,
            $attribute,
            $container->get(UrlGenerator::class)
        );
    }
}
