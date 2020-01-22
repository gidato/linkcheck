<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Support\Value\Url;

class ApprovedRedirect extends Model
{
    protected $fillable = [
        'from_url', 'to_url'
    ];

    private $cache;

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function getFromUrlAttribute($value)
    {
        return $this->getUrlType($value, 'from_url');
    }

    public function setFromUrlAttribute(Url $url)
    {
        $this->setUrlType($url, 'from_url');
    }

    public function getToUrlAttribute($value)
    {
        return $this->getUrlType($value, 'to_url');
    }

    public function setToUrlAttribute(Url $url)
    {
        $this->setUrlType($url, 'to_url');
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

}
