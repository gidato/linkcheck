<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Support\Value\Url;
use App\Support\Value\Throttle;

class Site extends Model
{
    protected $fillable = [
        'url',
        'throttle',
        'validation_code'
    ];

    private $cache;

    public function scans()
    {
        return $this->hasMany(Scan::class)->orderBy('id','DESC');
    }

    public function owners()
    {
        return $this->hasMany(Owner::class);
    }

    public function filters()
    {
        return $this->morphMany(Filter::class, 'filterable');
    }

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
        $this->cache['url'] = $url;
        $this->attributes['url'] = (string) $url;
    }

    public function isExternal() : boolean
    {
        return !empty($this->external);
    }

    public function getThrottleAttribute($value)
    {
        if (!empty($this->cache['throttle'])) {
            return $this->cache['throttle'];
        }

        if (is_string($value)) {
            $value = new Throttle($value);
        }

        $this->cache['throttle'] = $value;

        return $this->cache['throttle'];
    }

    public function setThrottleAttribute(Throttle $Throttle)
    {
        $this->cache['throttle'] = $Throttle;
        $this->attributes['throttle'] = (string) $Throttle;
    }

    public function getFilterMethodsAttribute($value)
    {
        return collect(explode(':', $value));
    }

    public function setFilterMethodsAttribute($value)
    {
        $value = collect($value);
        $this->attributes['filter_methods'] = $value->join(':');
    }

}
