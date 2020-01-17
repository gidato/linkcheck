<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    public function filterable()
    {
        return $this->morphTo();
    }

    public function getParametersAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        return json_decode($value, true);
    }

    public function setParametersAttribute(?array $parameters)
    {
        if (empty($parameters)) {
            $this->attributes['parameters'] = null;
        }

        $this->attributes['parameters'] = json_encode($parameters);

    }

}
