<?php

namespace App\Support\Service\Scan;

use App\Page;
use App\Support\Value\Url;
use App\Support\Value\Link;
use App\Support\Value\Reference\RedirectReference;
use App\Support\Service\Scan\PageProcessorInterface;
use App\Support\Service\HttpClient;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use App\Support\Service\Scan\ContentHandler\ContentHandlerManager;
use App\Support\Service\LinkInserter;

class PageProcessor implements PageProcessorInterface
{
    private $httpClient;
    private $linkExtractionManager;
    private $linkInserter;

    public function __construct(
        HttpClient $httpClient,
        ContentHandlerManager $linkExtractionManager,
        LinkInserter $linkInserter
    ) {
        $this->httpClient = $httpClient;
        $this->linkExtractionManager = $linkExtractionManager;
        $this->linkInserter = $linkInserter;
    }

    public function handle(Page $page) : void
    {
        switch ($page->method) {
            case 'get':
                $response = $this->httpClient->getPage($page);
                break;

            default:
                throw new \Exception('Not implemented');
        }

        if ($response->getStatusCode() == 200) {
            // only handle redirects if the redirect was ultimately found
            $page = $this->processRedirects($page, $response);
        }

        $page->status_code = $response->getStatusCode();
        $page->checked = true;
        if ($page->status_code == 200) {
            $page->mime_type = $this->getContentType($response);
        }

        $page->save();

        if ($this->shouldProcessContent($page)) { // Response OK
            $this->linkExtractionManager->handle($page, $response);
        }

    }

    private function processRedirects(Page $page, HttpResponse $response) : Page
    {
        $redirects = $this->combineRedirects(
            $response->getHeader('X-Guzzle-Redirect-History'),
            $response->getHeader('X-Guzzle-Redirect-Status-History')
        );

        $scan = $page->scan;

        foreach ($redirects as $url => $statusCode) {
            $page->status_code = $statusCode;
            $page->redirect = new Url($url);
            $page->checked = true;
            $page->save();

            $page = $this->linkInserter->linkFromPage($page, new Link(new Url($url), new RedirectReference()));
        }

        return $page;
    }

    private function combineRedirects(array $history, array $statuses) : array
    {
        return array_combine($history, $statuses);
    }


    private function shouldProcessContent(Page $page) : bool
    {
        if ($page->is_external) {
            return false;
        }

        if ($page->status_code != 200) {
            return false;
        }

        return true;
    }

    private function getContentType(HttpResponse $response) : ?string
    {
        if (empty($response->getHeader('Content-Type'))) {
            return null;
        }

        /* should only be one, and need the first */
        $contentType = $response->getHeader('Content-Type')[0];

        /* only need the bit before the ; if charset is set. */
        return explode(';', $contentType)[0];
    }
}
