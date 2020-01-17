<?php

namespace App\Support\Service\Scan\ContentHandler;

use App\Page;
use App\Support\Value\Link;
use App\Support\Service\Scan\PageProcessorInterface;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use App\Support\Service\Scan\ContentHandler\ContentHandlerInterface;
use InvalidArgumentException;

class ContentHandlerManager
{
    private $linkExtractors;

    public function __construct(array $linkExtractors)
    {
        $this->validateExtractors($linkExtractors);
        $this->linkExtractors = $linkExtractors;
    }

    private function validateExtractors($linkExtractors) : void
    {
        array_filter($linkExtractors, function ($value, $key) {
            if (!$value instanceof ContentHandlerInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Extractors must implement %s.  %s given',
                        ContentHandlerInterface::class,
                        is_object($value) ? get_class($value) : gettype($value)
                    )
                 );
            }

            if (empty($key)) {
                throw new InvalidArgumentException('Mimetype for extractor must be set');
            }

            if (!is_string($key)) {
                throw new InvalidArgumentException('Mimetype for extractor must be a string');
            }
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function handle(Page $page, HttpResponse $response) : void
    {
        if (!isset($this->linkExtractors[$page->mime_type])) {
            return;
        }

        $this->linkExtractors[$page->mime_type]->handle($page, $response->getBody());
    }
}
