<?php

namespace App\Support\Service\SiteValidation;

use App\Support\Service\SiteValidation\Response\ResponseInterface;
use App\Support\Service\SiteValidation\Response\ResponseOk;
use App\Support\Service\SiteValidation\Response\ResponseInvalid;
use App\Site;
use App\Support\Value\Url;
use App\Support\Service\HttpClient;
use Psr\Http\Message\ResponseInterface as HttpResponse;

class SiteValidator
{
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function validate(Site $site) : ResponseInterface
    {
        $url = $site->url;
        if ($this->checkCodeAtUrl($url, $url, $site->validation_code)) {
            return new ResponseOk();
        }

        $baseUrl = $url->getDomainUrl();
        if ($baseUrl != $url && $this->checkCodeAtUrl($baseUrl, $url, $site->validation_code)) {
            return new ResponseOk();
        }

        return new ResponseInvalid();
    }

    private function checkCodeAtUrl(Url $url, Url $checkUrl, string $code) : bool
    {
        $response = $this->httpClient->getUrl($url->getDirectory() . 'linkcheck_verification.json');

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $data = json_decode($response->getBody(), true);

        if (!isset($data[(string) $checkUrl])) {
            return false;
        }

        return ($data[(string) $checkUrl] == $code);
    }
}
