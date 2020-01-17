<?php
namespace App\Support\Value\Reference;

use InvalidArgumentException;

class Reference
{
    public static function fromArray(array $parameters) : ReferenceInterface
    {
        if (!isset($parameters['type'])) {
            throw new InvalidArgumentException('Type must be included');
        }

        $type = $parameters['type'];
        unset($parameters['type']);
        $class = __NAMESPACE__ . '\\' . ucfirst($type) . 'Reference';
        return $class::fromArray($parameters);  // if class doesn't exist we'll get an exception!
    }
}
