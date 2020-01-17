<?php

namespace App\Support\Service\Scan\ContentHandler;

use App\Page;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlHandlerInterface;
use App\Support\Service\LinkInserter;
use InvalidArgumentException;
use DOMDocument;
use DOMXPath;
use App\Support\Value\Path;

class HtmlContentHandler implements ContentHandlerInterface
{
    private $baseHandler;
    private $handlers;
    private $linkInserter;

    private $domDocument;
    private $xPath;

    public function __construct(
        UrlHandlerInterface $baseHandler,
        array $handlers,
        LinkInserter $linkInserter
    ) {
        $this->validateHandlers($handlers);
        $this->baseHandler = $baseHandler;
        $this->handlers = $handlers;
        $this->linkInserter = $linkInserter;

        libxml_use_internal_errors(true);
        $this->domDocument = new DOMDocument();
    }

    private function validateHandlers(array $handlers) : void
    {
        array_filter($handlers, function ($value, $key) {
            if (!$value instanceof UrlHandlerInterface) {
                throw new InvalidArgumentException('Handlers must implement ' . UrlHandlerInterface::class);
            }

            if (empty($key)) {
                throw new InvalidArgumentException('Mimetype for handler must be set');
            }

            if (!is_string($key)) {
                throw new InvalidArgumentException('Mimetype for handler must be a string');
            }
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function handle(Page $page, string $content, ?Path $basePath = null) : void
    {
        $this->domDocument->loadHTML($content);
        $this->recordErrorsForPage($page);

        $xPath = new DOMXPath($this->domDocument);
        $basePath = $basePath ?? $this->getBasePath($page, $xPath);

        foreach ($this->handlers as $handler) {
            $handler->findLinks($basePath, $page, $xPath)->each(function ($link) use ($page) {
                $this->linkInserter->linkFromPage($page, $link);
            });
        }
    }

    private function recordErrorsForPage(Page $page) : void
    {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if (empty($errors)) {
            return;
        }

        $page->html_errors = collect($errors)
            ->map(function ($error) {

                if (preg_match('/Tag [^\s]+ invalid/is',$error->message)) {
                    return null;
                }

                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $level = 'warning';
                        break;
                    case LIBXML_ERR_ERROR:
                        $level = 'error';
                        break;
                    case LIBXML_ERR_FATAL:
                        $level = 'fatal';
                        break;
                    default:
                        $level = 'unknown';
                }


                return [
                    'line' => $error->line,
                    'column' => $error->column,
                    'level' => $level,
                    'message' => $error->message
                ];
            })
            ->filter()
            ->toJson();

        $page->save();

    }

    private function getBasePath(Page $page, DOMXPath $xPath) : Path
    {
        $base = new Path($page->url->getDirectory());
        $baseOverrideLinks = $this->baseHandler->findLinks($base, $page, $xPath);
        if ($baseOverrideLinks->count() == 0) {
            return $base;
        }

        if ($baseOverrideLinks->count() > 1) {
            throw new \Exception('Only one base can be set');
        }

        return $baseOverrideLinks->first();
    }

}
