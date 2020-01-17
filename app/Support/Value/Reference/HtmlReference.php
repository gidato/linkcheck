<?php
namespace App\Support\Value\Reference;

use InvalidArgumentException;

class HtmlReference extends BaseReference implements ReferenceInterface
{
    /* from https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods */
    private $allowableMethods = ['get', 'head', 'post', 'put', 'delete', 'connect', 'options', 'trace', 'patch'];
    private $method;
    private $tag;
    private $attribute;
    private $target;

    public function __construct(?string $target = null, string $tag, ?string $attribute=null, ?string $method = 'get')
    {
        $this->method = strtolower($method);
        $this->tag = strtolower($tag);
        $this->attribute = $attribute;
        $this->target = $target;
        if (!in_array($this->method, $this->allowableMethods)) {
            // allow eroneous data to be passed in, and just assume they meant POST if it's not existent.
            // POST is assumed so that the page is then ignored in the checking;
            $this->method = 'post';
        }
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getAttributes() : array
    {
        return collect(['tag', 'attribute', 'target'])
            ->mapWithKeys(function ($attribute) {
                return ($this->$attribute)
                    ? [$attribute => $this->$attribute]
                    : [];
            })
            ->filter()
            ->merge(['type' => $this->getType() ])
            ->toArray();
    }

    public static function fromArray(array $parameters) : ReferenceInterface
    {
        if (!isset($parameters['tag'])) {
            throw new InvalidArgumentException('Tag must be included');
        }

        return new self(
            $parameters['target'] ?? null,
            $parameters['tag'],
            $parameters['attribute'] ?? null,
            $parameters['method'] ?? null
        );
    }

    public function getTagAndAttribute() : string
    {
        if (empty($this->attribute)) {
            return $this->tag;
        }

        return sprintf('<%s %s="...">', $this->tag, $this->attribute);
    }

    public function opensInNewWindow() : bool
    {
        return '_blank' == strtolower($this->target);
    }

}
