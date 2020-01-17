<?php

namespace App\Support\Value;

class Url
{
    private $schema;
    private $domain;
    private $path;
    private $query;

    public function __construct(string $url)
    {
        $url = trim($url, " \t\n\r\0\x0B\"\'");
        
        if (!preg_match('/^(https?):\/\/([^\/]+)(.*)(\?.*)?(\#.*)?$/is', $url, $matches)) {
            throw new \Exception('Invalid URL');
        }

        $this->schema = strtolower($matches[1]);
        $this->domain = strtolower($matches[2]);
        $this->path = $matches[3] ?: '/';
        $this->query = $matches[4] ?? '';
    }

    public function __get($name) {
        if (!in_array($name, ['domain', 'schema', 'path', 'query'])) {
            throw new \Exception(sprintf('Invalid property requested (%s)', $name));
        }
        return $this->$name;
    }

    public function __toString()
    {
        return sprintf('%s://%s%s%s', $this->schema, $this->domain, $this->path, $this->query);
    }

    public function getDomainUrl() : Url
    {
        return new Url(sprintf('%s://%s/', $this->schema, $this->domain));
    }

    public function getSuffix() : string
    {
        return array_reverse(explode('/',(string) $this))[0];
    }

    public function getDirectory() : Url
    {
        $url = explode('/', sprintf('%s://%s%s', $this->schema, $this->domain, $this->path));
        array_pop($url);
        return new self(implode('/', $url) . '/');
    }

    public function getLocal() : string
    {
        return sprintf('%s%s', $this->path, $this->query);
    }
}
