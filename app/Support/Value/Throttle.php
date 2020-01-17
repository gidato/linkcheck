<?php

namespace App\Support\Value;

class Throttle
{
    private $internal;
    private $external;

    public function __construct(string $throttle)
    {
        if (!preg_match('/^([0-9]+|default)\s*\:\s*([0-9]+|default)$/is', $throttle, $matches)) {
            throw new \Exception('Invalid Throttle');
        }

        $this->internal = ('default' == $matches[1]) ? null : (int) $matches[1];
        $this->external = ('default' == $matches[2]) ? null : (int) $matches[2];
    }

    public function __get($name) {
        if (!in_array($name, ['internal', 'external'])) {
            throw new \Exception(sprint('Invalid property requested (%s)', $name));
        }
        return $this->$name;
    }

    public function __toString()
    {
        return sprintf('%s : %s', $this->getInternalAsString(), $this->getExternalAsString());
    }

    public function getInternalAsString() : string
    {
        return (null === $this->internal) ? 'default' : (string) $this->internal;
    }

    public function getExternalAsString() : string
    {
        return (null === $this->external) ? 'default' : (string) $this->external;
    }
}
