<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlWithTargetHandlerFactory;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlHandlerFactory;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\MultipleUrlsHandlerFactory;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlFormHandler;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlMetaHttpEquivHandler;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlWithinStyleHandler;
use App\Support\Service\Scan\ContentHandler\LinkExtractor\Html\UrlWithinInlineJavascriptHandler;
use App\Support\Service\Scan\ContentHandler\HtmlContentHandlerFactory;
use App\Support\Service\Scan\ContentHandler\CssContentHandler;
use App\Support\Service\Scan\ContentHandler\JavascriptContentHandler;


class PageContentHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $container = $this->app->container('page-content-handlers');
        $this->registerPageContentHandlers($container);

        $container = $container->container('html-handlers');
        $this->registerHtmlTagContentHandlers($container);
    }

    private function registerPageContentHandlers($container)
    {
        $container->singletonFromFactory('text/html', HtmlContentHandlerFactory::class);
        $container->singleton('text/css', CssContentHandler::class);
        $container->singleton('application/javascript', JavascriptContentHandler::class);
    }

    private function registerHtmlTagContentHandlers($container)
    {
        $container->singletonFromFactory('base:href', UrlWithTargetHandlerFactory::class);

        $container->singletonFromFactory('a:href', UrlWithTargetHandlerFactory::class);
        $container->singletonFromFactory('frame:src', UrlWithTargetHandlerFactory::class);
        $container->singletonFromFactory('area:href', UrlWithTargetHandlerFactory::class);

        $container->singletonFromFactory('applet:codebase', UrlHandlerFactory::class);
        $container->singletonFromFactory('blockquote:cite', UrlHandlerFactory::class);
        $container->singletonFromFactory('body:background', UrlHandlerFactory::class);
        $container->singletonFromFactory('del:cite', UrlHandlerFactory::class);
        $container->singletonFromFactory('frame:longdesc', UrlHandlerFactory::class);
        $container->singletonFromFactory('head:profile', UrlHandlerFactory::class);
        $container->singletonFromFactory('iframe:longdesc', UrlHandlerFactory::class);
        $container->singletonFromFactory('iframe:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('image:href', UrlHandlerFactory::class); // inside SVG tags
        $container->singletonFromFactory('img:longdesc', UrlHandlerFactory::class);
        $container->singletonFromFactory('img:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('img:usemap', UrlHandlerFactory::class);
        $container->singletonFromFactory('input:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('input:usemap', UrlHandlerFactory::class);
        $container->singletonFromFactory('input:formaction', UrlHandlerFactory::class);
        $container->singletonFromFactory('ins:cite', UrlHandlerFactory::class);
        $container->singletonFromFactory('link:href', UrlHandlerFactory::class);
        $container->singletonFromFactory('object:classid', UrlHandlerFactory::class);
        $container->singletonFromFactory('object:codebase', UrlHandlerFactory::class);
        $container->singletonFromFactory('object:data', UrlHandlerFactory::class);
        $container->singletonFromFactory('object:usemap', UrlHandlerFactory::class);
        $container->singletonFromFactory('q:cite', UrlHandlerFactory::class);
        $container->singletonFromFactory('script:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('audio:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('button:formaction', UrlHandlerFactory::class);
        $container->singletonFromFactory('command:icon', UrlHandlerFactory::class);
        $container->singletonFromFactory('embed:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('html:manifest', UrlHandlerFactory::class);
        $container->singletonFromFactory('source:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('track:src', UrlHandlerFactory::class);
        $container->singletonFromFactory('video:poster', UrlHandlerFactory::class);
        $container->singletonFromFactory('video:src', UrlHandlerFactory::class);

        $container->singleton('form', UrlFormHandler::class);

        $container->singletonFromFactory('source:srcset', MultipleUrlsHandlerFactory::class);
        $container->singletonFromFactory('img:srcset', MultipleUrlsHandlerFactory::class);
        $container->singletonFromFactory('applet:archive', MultipleUrlsHandlerFactory::class);
        $container->singletonFromFactory('object:archive', MultipleUrlsHandlerFactory::class);

        $container->singleton('meta', UrlMetaHttpEquivHandler::class);

        $container->singleton('style', UrlWithinStyleHandler::class);
        $container->singleton('script', UrlWithinInlineJavascriptHandler::class);
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
