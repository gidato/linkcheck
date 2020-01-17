<?php

namespace App\Support\Value;

class Path
{
    private $url;
    private $target;

    public function __construct(Url $url, ?string $target = null)
    {
        $this->url = $url;
        $this->target = $target;
    }

    public function __get($name) {
        if (!in_array($name, ['url', 'target'])) {
            throw new \Exception(sprint('Invalid property requested (%s)', $name));
        }
        return $this->$name;
    }

}
