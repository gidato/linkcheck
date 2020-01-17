<?php

namespace App\Support\Service\Container;

use Illuminate\Foundation\Application as LaravelApplication;

class Application extends LaravelApplication
{
    use ContainerExtensions;
}
