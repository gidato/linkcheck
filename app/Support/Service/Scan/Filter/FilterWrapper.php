<?php

namespace App\Support\Service\Scan\Filter;

use App\Filter;
use App\Site;

class FilterWrapper
{
    private $key;
    private $filterHandler;
    private $filterSettings;

    public function __construct(
        string $key,
        ScanFilterInterface $filterHandler,
        ?Filter $filterSettings = null
    ) {
        $this->key = $key;
        $this->filterHandler = $filterHandler;
        $this->filterSettings = $filterSettings;
    }

    /**
     * accessed through ->on
     */
    private function getOn() : bool
    {
        if (empty($this->filterSettings)) {
            return false;
        }

        return (bool) $this->filterSettings->on;
    }

    /**
     * accessed through ->description
     */
    private function getDescription() : string
    {
        return $this->filterHandler->getDescription($this->filterSettings);
    }

    /**
     * accessed through ->key
     */
    private function getKey() : string
    {
        return $this->key;
    }

    /**
     * accessed through ->name
     */
    private function getName() : string
    {
        return $this->filterHandler->getName();
    }

    /**
     * accessed through ->parameters
     */
    private function getParameters() : array
    {
        if (empty($this->filterSettings)) {
            return [];
        }

        return $this->filterSettings->parameters;
    }

    public function getValidator(array $data)
    {
        return $this->filterHandler->getInputValidator($data);
    }

    public function updateFilterValues(Site $site, array $parameters) : void
    {
        if (empty($this->filterSettings)) {
            $this->createFilterRecord($site, $parameters);
            return;
        }

        $this->updateFilterRecord($parameters);

    }

    private function createFilterRecord(Site $site, array $parameters) : void
    {
        $filter = new Filter();
        $filter->filterable_type = Site::class;
        $filter->filterable_id = $site->id;
        $filter->key = $this->key;
        $filter->parameters = $parameters;
        $filter->on = true;
        $filter->save();
    }

    private function updateFilterRecord(array $parameters) : void
    {
        $this->filterSettings->on = true;
        $this->filterSettings->parameters = $parameters;
        $this->filterSettings->save();
    }

    public function turnFilterOff() : void
    {
        if (empty($this->filterSettings)) {
            return;
        }

        $this->filterSettings->on = false;
        $this->filterSettings->save();
    }

    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new \Exception(sprintf('Invalid property requested (%s)', $name));
    }

}
