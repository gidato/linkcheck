<?php
declare(strict_types=1);

namespace App\Support\Service\Scan\ContentHandler\LinkExtractor\Html;

use Gidato\Container\Contract\FactoryContract;
use Psr\Container\ContainerInterface;
use App\Support\Service\UrlGenerator;

class UrlWithTargetHandlerFactory implements FactoryContract
{
    public function __invoke(ContainerInterface $container, string $requestedName, array $parameters = null)
    {
        [$tag, $attribute] = explode(':', $requestedName, 2);
        if (empty($tag) || empty($attribute)) {
            throw new \Exception('Cannot create UrlHandler for requestedName');
        }

        return new UrlWithTargetHandler(
            $tag,
            $attribute,
            $container->get(UrlGenerator::class)
        );
    }
}
