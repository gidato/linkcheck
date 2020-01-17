<?php

namespace App\Support\Service\Scan\Filter;

use Illuminate\Database\Eloquent\Builder;
use App\Support\Service\Scan\Filter\ScanFilterInterface;
use App\Scan;
use App\Filter;

class InternalOnly extends FilterAbstract implements ScanFilterInterface
{
    protected $name = 'Only check internal pages';

    public function getDescription(?Filter $filter = null) : string
    {
        return 'Limit scan to internal pages only';
    }

    public function filter(Builder $query, Scan $scan, ?array $parameters) : Builder
    {
        return $query->where('is_external', false);
    }
}
