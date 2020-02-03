<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\Service\Scan\ContentHandler\ContentHandlerManager;
use App\Support\Service\Scan\ContentHandler\ContentHandlerManagerFactory;
use App\Support\Service\Scan\ThrottleManager;
use App\Support\Service\Scan\ThrottleManagerFactory;
use App\Support\Service\Scan\Filter\FilterManager;
use App\Support\Service\Scan\Filter\FilterManagerFactory;
use App\Support\Service\Scan\Filter\InternalOnly;
use App\Support\Service\Scan\Filter\Depth;
use App\Support\Service\Scan\Filter\CheckedCount;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singletonFromFactory(ContentHandlerManager::class, ContentHandlerManagerFactory::class);
        $this->app->singletonFromFactory(ThrottleManager::class, ThrottleManagerFactory::class);
        $this->app->singletonFromFactory(FilterManager::class, FilterManagerFactory::class);
        $this->app->container('scan-filters')->singleton('internal-only', InternalOnly::class);
        $this->app->container('scan-filters')->singleton('max-depth', Depth::class);
        $this->app->container('scan-filters')->singleton('max-pages', CheckedCount::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
