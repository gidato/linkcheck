<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Support\Value\Url;

class Throttle extends Model
{
    protected $fillable = [
        'url',
        'not_before'
    ];

    protected $dates = [
        'not_before',
    ];

    /* holds generated value objects so that they do not need to be recreated */
    private $cache = [];

    public function getUrlAttribute($value)
    {
        if (!empty($this->cache['url'])) {
            return $this->cache['url'];
        }

        if (is_string($value)) {
            $value = new Url($value);
        }

        $this->cache['url'] = $value;

        return $this->cache['url'];
    }

    public function setUrlAttribute(Url $url)
    {
        if (!is_null($url)) {
            $this->cache['url'] = $url;
            $this->attributes['url'] = (string) $url;
            return;
        }

        if (isset($this->cache['url'])) {
            unset($this->cache['url']);
        }

        $this->attributes['url'] = null;
    }

}
