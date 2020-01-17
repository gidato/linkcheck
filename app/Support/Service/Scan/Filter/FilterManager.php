<?php

namespace App\Support\Service\Scan\Filter;

use InvalidArgumentException;
use App\Filter;
use App\Scan;
use App\Site;
use Illuminate\Database\Eloquent\Builder;

class FilterManager
{
    private $filters;

    public function __construct(array $filters)
    {
        $this->validateFilters($filters);
        $this->filters = $filters;
    }

    private function validateFilters($filters) : void
    {
        array_filter($filters, function ($value, $key) {
            if (!$value instanceof ScanFilterInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Filters must implement %s. %s given.',
                        ScanFilterInterface::class,
                        is_object($value) ? get_class($value) : gettype($value)
                    )
                 );
            }

            if (empty($key)) {
                throw new InvalidArgumentException('Key for filter must be set');
            }

            if (!is_string($key)) {
                throw new InvalidArgumentException('Key for filter must be a string');
            }
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function filter(Builder $query, Scan $scan, Filter $filter) : Builder
    {
        if (!$filter->on) {
            return $query;
        }

        if (!isset($this->filters[$filter->key])) {
            throw new InvalidArgumentException(
                sprintf('Unknown filter key "%s"', $filter->key)
             );
        }

        return $this->filters[$filter->key]->filter($query, $scan, $filter->parameters);
    }

    public function getFilterSettingsForSite(Site $site) : array
    {
        $existingFilters =
            $site->filters
                ->mapWithKeys(function($filter) {
                    if (isset($this->filters[$filter->key])) {
                        return [
                            $filter->key => new FilterWrapper($filter->key, $this->filters[$filter->key], $filter)
                        ];
                    }
                })
                ->filter();

        $newFilters =
            collect($this->filters)
                ->mapWithKeys(function($filter, $key) {
                    return [
                        $key => new FilterWrapper($key, $this->filters[$key], null)
                    ];
                });

        return $newFilters->merge($existingFilters)->toArray();
    }
}
