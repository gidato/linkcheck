<?php

namespace App\Support\Service\Scan\Filter;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Scan;
use App\Filter;
use InvalidArgumentException;

abstract class FilterAbstract implements ScanFilterInterface
{


    /**
     * @var Illuminate\Contracts\Validation\Factory
     * used to create the validation rules;
     */
    protected $validator;

    /**
     * @var string - name of the filter
     */
    protected $name;

    /**
     * @var array - validation rules
     * can be overwritten in classes that inherit, or can overwrite the rules() function
     */
    protected $rules = [];

    /**
     * @var array - validation messages
     * can be overwritten in classes that inherit, or can overwrite the messages() function
     */
    protected $messages = [];

    /**
     * @var array - validation attributes
     * can be overwritten in classes that inherit, or can overwrite the attributes() function
     */
    protected $attributes = [];

    public function __construct(ValidationFactory $validator)
    {
        $this->validator = $validator;
    }

    protected function validateParameters(?array $parameters) : void
    {
        $validator = $this->validator->make($parameters ?? [], $this->rules(), $this->messages());
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function getInputValidator(?array $parameters)
    {
        return $this->validator->make($parameters ?? [], $this->rules(), [], $this->attributes());
    }

    abstract public function getDescription(?Filter $filter = null) : string;
    abstract public function filter(Builder $query, Scan $scan, ?array $parameters) : Builder;

    protected function rules() : array
    {
        return $this->rules;
    }

    protected function messages() : array
    {
        return $this->messages;
    }

    protected function attributes() : array
    {
        return $this->attributes;
    }

    public function getName() : string
    {
        return $this->name;
    }

}
