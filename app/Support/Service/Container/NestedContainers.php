<?php

namespace App\Support\Service\Container;

use Illuminate\Container\Container as LaravelContainer;

trait NestedContainers
{

    private $parent;

    public function container(string $name) : Container
    {
        if (!$this->has('container:'.$name)) {
            $container = new Container();
            $container->setParent($this);
            $this->instance('container:'.$name, $container);
        }
        return $this->get('container:'.$name);
    }

    /**
     * overwrite standard to check if we've got it - if so, ask inherited (normal) fucntion to resolve it
     * if not and we're in a child container, ask parent
     */
    public function make($abstract, array $parameters = [])
    {
        if (!$this->has($abstract) && $this->hasParent()) {
            return $this->getParent()->make($abstract, $parameters);
        }
        return parent::make($abstract, $parameters);
    }

    /**
     * overwrite standard to check if we've got it - if so, ask inherited (normal) fucntion to resolve it
     * if not and we're in a child container, ask parent
     */
    public function get($id)
    {
        if (!$this->has($id) && $this->hasParent()) {
            return $this->getParent()->get($id);
        }
        return parent::get($id);
    }

    public function setParent(LaravelContainer $container) : void
    {
        $this->parent = $container;
    }

    public function hasParent() : bool
    {
        return !empty($this->parent);
    }

    public function getParent() : LaravelContainer
    {
        return $this->parent;
    }

    public function getAllBound() : array
    {
        return collect(array_keys($this->bindings))
            ->merge(array_keys($this->instances))
            ->merge(array_keys($this->aliases))
            ->unique()
            ->mapWithKeys(function ($key) {
                return [$key => $this->get($key)];
            })
            ->toArray();
    }

    public function getAllDirectlyBound() : array
    {
        return collect(array_keys($this->bindings))
            ->merge(array_keys($this->instances))
            ->merge(array_keys($this->aliases))
            ->unique()
            ->reject(function ($key) {
                return strpos($key, 'container:') === 0;
            })
            ->mapWithKeys(function ($key) {
                return [$key => $this->get($key)];
            })
            ->toArray();
    }

}
