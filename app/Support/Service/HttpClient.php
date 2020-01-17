<?php

namespace App\Support\Service;

use App\Page;
use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Http\Message\ResponseInterface as HttpResponse;

class HttpClient
{
    private $guzzle;

    public function __construct(GuzzleHttpClient $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function getPage(Page $page) : HttpResponse
    {
        $headers = [];

        if ($referer = $page->referredInPages->first()) {
            $headers['Referer'] = (string) $referer->url;
        }

        return $this->getUrl($page->url, $headers);
    }

    public function getUrl(string $url, $headers = []) : HttpResponse
    {
        $headers['User-Agent'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36';

        return $this->guzzle->request(
            'GET',
            $url,
            [
                'headers' => $headers,
                'allow_redirects' => [
                    'track_redirects' => true
                ],
                'force_ip_resolve' => 'v4',
                'http_errors' => false
            ]
        );
    }
}
