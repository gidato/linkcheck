<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Support\Value\Url;
use App\Site;
use InvalidArgumentException;

class UniqueUrl implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $url = new Url($value);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return Site::where('url', (string) $url)->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must not exist already.';
    }
}
