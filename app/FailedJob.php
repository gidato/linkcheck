<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Support\Value\JobException;

class FailedJob extends Model
{
    public $timestamps = false;
    protected $dates = [
        'failed_at',
    ];

    private $cache = [];

    public function getPayloadAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getExceptionAttribute($value)
    {
        if (empty($this->cache['exception'])) {
            $this->cache['exception'] = new JobException($value);
        }

        return $this->cache['exception'];
    }

    public function getCommandAttribute()
    {
        if (empty($this->cache['command'])) {
            $this->cache['command'] = unserialize($this->payload['data']['command']);
        }

        return $this->cache['command'];
    }

    public function getJobNameAttribute()
    {
        if ($this->payload && (! isset($this->payload['data']['command']))) {
            return $this->payload['job'] ?? null;
        } elseif ($this->payload && isset($this->payload['data']['command'])) {
            return $this->matchJobName($this->payload);
        }
    }

    private function matchJobName($payload)
    {
        preg_match('/"([^"]+)"/', $payload['data']['command'], $matches);

        return $matches[1] ?? $payload['job'] ?? null;
    }

}
