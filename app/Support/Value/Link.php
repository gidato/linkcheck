<?php

namespace App\Support\Value;

use App\Support\Value\Reference\ReferenceInterface;

class Link
{
    private $url;
    private $reference;

    public function __construct(Url $url, ReferenceInterface $reference)
    {
        $this->url = $url;
        $this->reference = $reference;
    }

    public function __get($name) {

        if (method_exists($this,'get'.ucfirst($name).'Attribute')) {
            $method = 'get'.ucfirst($name).'Attribute';
            return $this->$method();
        }

        if (!in_array($name, ['url', 'reference'])) {
            throw new \Exception(sprint('Invalid property requested (%s)', $name));
        }
        return $this->$name;
    }

    public function getMethodAttribute() : string
    {
        return $this->reference->getMethod();
    }

    public function getLinkAttributes() : array
    {
        return $this->reference->getAttributes();
    }

}
