<?php

namespace App\Support\Service\Scan;

/**
 * controls throttling of access to pages
 */

use App\Support\Value\Url;
use App\Throttle;
use App\Page;
use App\Support\Value\Throttle as ThrottleConfig;


class ThrottleManager
{
    private $defaultThrottle;

    public function __construct(ThrottleConfig $defaultThrottle)
    {
        $this->defaultThrottle = $defaultThrottle;
        $this->clearElapsedThrottles();
    }

    private function clearElapsedThrottles() : void
    {
        Throttle::where('not_before', '<', now())->delete();
    }

    public function shouldThrottle(Page $page) : bool
    {
        return Throttle::where('not_before', '>', now())->where('url',$page->url->getDomainUrl())->count() > 0;
    }

    public function throttleDelay(Page $page) : int
    {
        $throttle = Throttle::where('not_before', '>', now())->where('url',$page->url->getDomainUrl())->first();
        if ($throttle) {
            return $throttle->not_before->diffInSeconds(now()->startOfSecond());
        }
        return 0;
    }

    public function recordAccessingDomainNow(Page $page) : void
    {
        $throttle = Throttle::where('url', $page->url->getDomainUrl())->first();

        if (!$throttle) {
            $throttle = new Throttle;
            $throttle->url = $page->url->getDomainUrl();
        }

        $throttle->not_before = now()->addSeconds($this->getWaitDurationInSeconds($page));
        $throttle->save();

    }

    private function getWaitDurationInSeconds(Page $page) : int
    {
        return ($page->is_external)
            ? $this->getExternalWaitDurationInSeconds($page->scan->site->throttle)
            : $this->getInternalWaitDurationInSeconds($page->scan->site->throttle);
    }

    private function getInternalWaitDurationInSeconds(ThrottleConfig $throttle) : int
    {
        return $throttle->internal ?? $this->defaultThrottle->internal;
    }

    private function getExternalWaitDurationInSeconds(ThrottleConfig $throttle) : int
    {
        return $throttle->external ?? $this->defaultThrottle->external;
    }

}
