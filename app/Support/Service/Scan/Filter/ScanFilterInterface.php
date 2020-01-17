<?php

namespace App\Support\Service\Scan\Filter;

use Illuminate\Database\Eloquent\Builder;
use App\Scan;
use App\Filter;

interface ScanFilterInterface
{
    public function getName() : string;
    public function getDescription(?Filter $filter = null) : string;
    public function getInputValidator(?array $parameters);
    public function filter(Builder $query, Scan $scan, ?array $parameters) : Builder;
}
