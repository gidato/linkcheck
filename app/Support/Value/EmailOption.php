<?php

namespace App\Support\Value;

use InvalidArgumentException;

class EmailOption
{
    private $all = false;
    private $self = false;

    public function __construct(?string $option = null)
    {
        $option = trim(strtolower($option ?? ''), " \t\n\r\0\x0B\"\'");
        if (empty($option)) {
            return;
        }

        if (!in_array($option, ['self','all'])) {
            throw new InvalidArgumentException('Only "SELF" or "ALL" allowed');
        }

        $this->all = 'all' == $option;
        $this->self = 'self' == $option;
    }

    public function __get($name) {
        if (!in_array($name, ['all', 'self'])) {
            throw new \Exception(sprintf('Invalid property requested (%s)', $name));
        }
        return $this->$name;
    }
}
