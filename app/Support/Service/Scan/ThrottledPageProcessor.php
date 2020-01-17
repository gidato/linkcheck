<?php

namespace App\Support\Service\Scan;

use App\Page;
use App\Support\Service\Scan\PageProcessor;
use App\Support\Service\Sleeper;
use App\Support\Value\Throttle;
use Carbon\Carbon;

class ThrottledPageProcessor implements PageProcessorInterface
{
    /* keeps tack of when a domain was lsat visited */
    private $domainsVisited;

    /* processor to actually process the page when the tiem comes */
    private $pageProcessor;

    /* wrapper for the sleep function */
    private $sleeper;

    /* default throttling for service if site throttle states "default" */
    private $defaultThrottle;

    public function __construct(
        PageProcessor $pageProcessor,
        Sleeper $sleeper,
        Throttle $defaultThrottle
    ) {
        $this->domainsVisited = [];
        $this->pageProcessor = $pageProcessor;
        $this->sleeper = $sleeper;
        $this->defaultThrottle = $defaultThrottle;
    }

    public function handle(Page $page) : void
    {
        $domain = $page->url->domain;
        if (isset($this->domainsVisited[$domain])) {
            $this->wait($this->domainsVisited[$domain], $this->getWaitDurationInSeconds($page));
        }
        $this->pageProcessor->handle($page);
        $this->domainsVisited[$domain] = now();
    }

    private function getWaitDurationInSeconds(Page $page) : int
    {
        return ($page->is_external)
            ? $this->getExternalWaitDurationInSeconds($page->scan->site->throttle)
            : $this->getInternalWaitDurationInSeconds($page->scan->site->throttle);
    }

    private function getInternalWaitDurationInSeconds(Throttle $throttle) : int
    {
        return $throttle->internal ?? $this->defaultThrottle->internal;
    }

    private function getExternalWaitDurationInSeconds(Throttle $throttle) : int
    {
        return $throttle->external ?? $this->defaultThrottle->external;
    }

    private function wait(Carbon $lastVisit, int $seconds) : void
    {
        $waitUntil = $lastVisit->add($seconds, 'seconds');
        // use milliseconds otherwise any partial second is missed;
        $sleepTime = now()->diffInMicroseconds($waitUntil);
        $this->sleeper->sleep($sleepTime);
    }

}
