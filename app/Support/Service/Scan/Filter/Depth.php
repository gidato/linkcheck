<?php

namespace App\Support\Service\Scan\Filter;

use Illuminate\Database\Eloquent\Builder;
use App\Support\Service\Scan\Filter\ScanFilterInterface;
use App\Scan;
use App\Filter;
use InvalidArgumentException;
use Illuminate\Support\Pluralizer;

class Depth extends FilterAbstract implements ScanFilterInterface
{
    protected $name = 'Limit depth of scan';
    protected $rules = [ 'max' => 'required|integer|min:1' ];
    protected $messages = [
        'required' => 'Requires parameter ":attribute" to be set',
        'integer' => 'Requires parameter ":attribute" to be an integer',
        'min' => 'Requires parameter "max" to be at least :min'
    ];
    protected $attributes = [
        'max' => 'depth',
    ];

    public function getDescription(?Filter $filter = null) : string
    {
        $value = ($filter)
            ? $filter->parameters['max']
            : '<em>"not-set"</em>';

        return 'Limit depth of scan to ' . $value . Pluralizer::plural(' level', $value);
    }

    public function filter(Builder $query, Scan $scan, ?array $parameters) : Builder
    {
        $this->validateParameters($parameters);
        return $query->where('depth', '<', $parameters['max']);
    }
}
