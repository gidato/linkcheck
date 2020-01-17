<?php

namespace App\Support\Value\Reference;

interface ReferenceInterface
{
    public function getMethod() : string;
    public function getAttributes() : array;
    public static function fromArray(array $array) : ReferenceInterface;
}
