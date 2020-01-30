<?php

namespace App\Support\Service\Container;

use App\Support\Service\Container\Contract\FactoryContract;

trait FactoryBinding
{

    public function singletonFromFactory($abstract, $factoryClass)
    {
        $this->bindFromFactory($abstract, $factoryClass, true);
    }

    public function bindFromFactory($abstract, $factoryClass, $shared = false)
    {
        $this->validateFactory($factoryClass);
        $this->bind(
            $abstract,
            function($container, $params = []) use ($abstract, $factoryClass) {
                $factory = new $factoryClass;
                return $factory($container, $abstract, $params);
            },
            $shared
        );
    }

    private function validateFactory(string $factoryClass)
    {
        $implements = class_implements($factoryClass);
        if (empty($implements) || empty($implements[FactoryContract::class])) {
            throw new \Exception(sprintf(
                'Invalid container factory (%s) - Must implement %s',
                $factoryClass,
                FactoryContract::class
            ));
        }
    }




}
