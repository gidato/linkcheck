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
        $this->bind(
            $abstract,
            function($container, $params = []) use ($abstract, $factoryClass) {
                $factory = new $factoryClass;
                $this->validateFactory($factory);
                return $factory($container, $abstract, $params);
            },
            $shared
        );
    }

    private function validateFactory(FactoryContract $factory)
    {
        // type hinting used to validate
    }




}
