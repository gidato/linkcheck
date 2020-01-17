<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Support\Value\Url;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Page extends Model
{
    protected $fillable = [
        'scan_id',
        'url',
        'method',
        'depth'
    ];

    /* holds generated value objects so that they do not need to be recreated */
    private $cache = [];

    public static function boot()
   {
       parent::boot();

       self::creating(function($page){
           $page->establishIfExternal();
       });
   }

    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }

    public function referencedPages()
    {
        return $this->belongsToMany(Page::class,'page_references','referrer_id','referred_id')->using(ReferencedPagesPivot::class)->withPivot(['type', 'target','tag','attribute', 'times']);
    }

    public function referredInPages()
    {
        return $this->belongsToMany(Page::class,'page_references','referred_id','referrer_id')->using(ReferencedPagesPivot::class)->withPivot('type', 'target','tag','attribute', 'times');
    }

    public function getUrlAttribute($value)
    {
        return $this->getUrlType($value, 'url');
    }

    public function setUrlAttribute(Url $url)
    {
        $this->setUrlType($url, 'url');
    }

    public function getRedirectAttribute($value)
    {
        return $this->getUrlType($value, 'redirect');
    }

    public function setRedirectAttribute(?Url $url)
    {
        $this->setUrlType($url, 'redirect');
    }

    private function getUrlType($value, string $name)
    {
        if (!empty($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (is_string($value)) {
            $value = new Url($value);
        }

        $this->cache[$name] = $value;

        return $this->cache[$name];
    }

    private function setUrlType(?Url $url, string $name)
    {
        if (!is_null($url)) {
            $this->cache[$name] = $url;
            $this->attributes[$name] = (string) $url;
            return;
        }

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        $this->attributes[$name] = null;
    }

    public function establishIfExternal() : void
    {
        $siteUrl = $this->scan->site->url;
        $internal =
            $this->url == $siteUrl
            ||
            strpos($this->url, (string) $siteUrl->getDirectory()) === 0;

        $this->is_external = !$internal;
    }

    public function isError() : bool
    {
        return $this->checked && !in_array($this->status_code, [200, 301, 302, 303, 304, 305, 306, 307, 308]);
    }

    public function isRedirect() : bool
    {
        return $this->checked && in_array($this->status_code, [301, 302, 303, 304, 305, 306, 307, 308]);
    }

    public function hasHtmlErrors() : bool
    {
        return $this->checked
            && $this->status_code == 200
            && $this->mime_type = 'text/html'
            && $this->html_errors != '[]';
    }

    public function getStatusText() : string
    {
        return HttpResponse::$statusTexts[$this->status_code] ?? 'Unknown';
    }

    public function getShortUrl() : string
    {
        if ($this->is_external) {
            return (string) $this->url;
        }

        return $this->url->getLocal();

    }

    public function getShortRedirectUrl() : string
    {
        $siteUrl = $this->scan->site->url;
        $internal =
            $this->redirect == $siteUrl
            ||
            strpos($this->redirect, (string) $siteUrl->getDirectory()) === 0;

        if ($internal) {
            $url = new Url($this->redirect);
            return $url->getLocal();
        }

        return $this->redirect;


    }

}
