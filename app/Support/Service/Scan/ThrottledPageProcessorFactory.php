<?php

namespace App\Support\Service\Scan;

use Psr\Container\ContainerInterface;
use App\Support\Service\Scan\PageProcessor;
use App\Support\Service\Sleeper;
use App\Support\Value\Throttle;
use App\Support\Service\Container\Contract\FactoryContract;

class ThrottledPageProcessorFactory implements FactoryContract
{
    public function __invoke(ContainerInterface $app, string $requestedName, array $params = [])
    {
        $defaultThrottle = new Throttle(config('throttle.internal') . ':' . config('throttle.external'));
        return new ThrottledPageProcessor(
            $app->get(PageProcessor::class),
            $app->get(Sleeper::class),
            $defaultThrottle
        );
    }
}
