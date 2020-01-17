<?php

namespace App\Support\Service;

/**
 * generates the full url using base path and relative (or absolute) path given
 */

 use App\Support\Value\Url;
 use App\Support\Value\Path;
 use App\Page;

class UrlGenerator
{
    public function getUrlForString(string $url, Path $base, Page $page) : ?Url
    {
        $url = $this->getAbsoluteUrlString($url, $base, $page);
        $url = (null === $url) ? null : $this->trimDoubleSlash($url);
        $url = (null === $url) ? null : $this->trimPageAnchors($url);
        $url = (null === $url) ? null : $this->trimDots($url);
        return (null === $url) ? null : new Url($url);
    }

    private function getAbsoluteUrlString(string $url, Path $base, Page $page) : ?string
    {
        $url = trim($url, " \"\'" );

        if ('#' == substr($url,0,1)) {
            return $base->url . '/' . $page->url->getSuffix();
        }

        if (empty($url)) {
            return $base->url . $page->url->getSuffix();
        }

        $colonPos = strpos($url,':');
        if ($colonPos !== false) {
            $prefix = strtolower(substr($url, 0, $colonPos));
            if ($prefix != 'http' && $prefix != 'https') {
                return NULL;
            }
        }

        if (preg_match('/^https?:\/\//is', $url)) {
            return $url;
        }

        if ('/' == substr($url,0,1)) {
            return rtrim($base->url->getDomainUrl(),'/') . $url;
        }

        return $base->url->getDirectory() . '/' . $url;
    }

    private function trimDoubleSlash(string $url) : string
    {
        while (preg_match('/^(.*?\/\/.*?)\/\/(.*)$/',$url, $matches)) {
            $url = $matches[1] . '/' . $matches[2];
        }

        return $url;
    }

    private function trimPageAnchors(string $url) : string
    {
        if (preg_match('/^(.*?)\#/',$url, $matches)) {
            return $matches[1];
        }
        return $url;
    }

    private function trimDots(string $url) : string
    {
        if (empty($url)) {
            return $url;
        }

        // trim parent directory double dots
        while (preg_match('#^(.*?)/[^/]+/\.\.(/.*)?$#', $url, $matches)) {
            $url = $matches[1] . ($matches[2] ?? '');
        }

        // trim current directory single dots at end
        while (preg_match('#^(.*?)/\.$#', $url, $matches)) {
            $url = $matches[1];
        }

        // trim current directory single dots not at end
        do {
            $url = str_replace('/./', '/', $url, $count);
        } while ($count > 0);

        return $url;
    }

}
