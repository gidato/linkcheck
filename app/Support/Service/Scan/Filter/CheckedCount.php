<?php

namespace App\Support\Service\Scan\Filter;

use Illuminate\Database\Eloquent\Builder;
use App\Support\Service\Scan\Filter\ScanFilterInterface;
use App\Scan;
use App\Page;
use App\Filter;
use InvalidArgumentException;
use DB;
use Illuminate\Support\Pluralizer;

class CheckedCount extends FilterAbstract implements ScanFilterInterface
{
    protected $name = 'Limit number of pages checked';
    protected $rules = [ 'max' => 'required|integer|min:1' ];
    protected $messages = [
        'required' => 'Requires parameter ":attribute" to be set',
        'integer' => 'Requires parameter ":attribute" to be an integer',
        'min' => 'Requires parameter "max" to be at least :min'
    ];
    protected $attributes = [
        'max' => 'number of pages',
    ];


    public function getDescription(?Filter $filter = null) : string
    {
        $value = ($filter)
            ? $filter->parameters['max']
            : '<em>"not-set"</em>';

        return 'Limit checks to a maximum of ' . $value . Pluralizer::plural(' page', $value);
    }

    public function filter(Builder $query, Scan $scan, ?array $parameters) : Builder
    {
        $this->validateParameters($parameters);

        $checked = Page::where('scan_id', $scan->id)
            ->where('checked', true)
            ->count();

        if ($checked < $parameters['max']) {
            return $query;
        }

        return $query->where('scan_id',null);
    }

}
