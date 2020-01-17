<?php
namespace App\Support\Value\Reference;

use InvalidArgumentException;

abstract class BaseReference implements ReferenceInterface
{
    public function getMethod() : string
    {
        return 'get';
    }

    public function getAttributes() : array
    {
        return ['type' => $this->getType() ];
    }

    public function getType() : string
    {
        $type = array_reverse(explode('\\', get_class($this)))[0];
        if (substr($type, -9) != 'Reference') {
            return __CLASS__;
        }
        return strtolower(substr($type,0,-9));

    }

    public static function fromArray(array $parameters) : ReferenceInterface
    {
        return new static;
    }

}
