<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    protected $fillable = [
        'site_id',
        'status',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class)->orderBy('url');
    }

    public function filters()
    {
        return $this->morphMany(Filter::class, 'filterable');
    }

    public function isComplete() : bool
    {
        return ('success' == $this->status || 'errors' == $this->status || 'aborted' == $this->status || 'warnings' == $this->status);
    }

    public function hasLinkErrors() : bool
    {
        return ($this->getLinkErrors()->count() > 0);
    }

    public function getLinkErrors()
    {
        return $this->pages->where('checked',true)->whereNotIn('status_code',[200,301, 302, 303, 304, 305, 306, 307, 308]);
    }

    public function hasHtmlErrors() : bool
    {
        return ($this->getHtmlErrors()->count() > 0);
    }

    public function getHtmlErrors()
    {
        return $this->pages->where('checked',true)->where('mime_type','text/html')->where('html_errors','<>', '[]')->where('is_external', 0);
    }

    public function hasRedirects() : bool
    {
        return ($this->getRedirects()->count() > 0);
    }

    public function getRedirects()
    {
        return $this->pages->where('checked',true)->whereIn('status_code',[301, 302, 303, 304, 305, 306, 307, 308]);
    }


    public function hasBeenAborted() : bool
    {
        return 'aborted' == $this->status;
    }

    public function hasWarnings() : bool
    {
        return ($this->getWarnings()->count() > 0);
    }

    public function getWarnings()
    {
        return $this->getRedirects()->merge($this->getHtmlErrors());
    }
}
