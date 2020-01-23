<?php

namespace App\Support\Value;

class JobException
{
    private $exception;

    public function __construct(string $exception)
    {
        $this->exception = trim($exception);
    }

    public function __get($name) {
        if ($name == 'firstLine') {
            return trim(explode("\n",$this->exception)[0]);
        }

        throw new \Exception(sprintf('Invalid property requested (%s)', $name));
    }

    public function __toString()
    {
        return $this->exception;
    }

}
