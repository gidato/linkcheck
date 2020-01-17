<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = [
        'name', 'email','site_id'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
