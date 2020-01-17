<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Throttling for Internal Pages
    |--------------------------------------------------------------------------
    |
    | This value is the the number of seconds to delay between requests to
    | the internal site. These are pages within the requested scan
    |
    */

    'internal' => env('THROTTLE_INTERNAL', 1),

    /*
    |--------------------------------------------------------------------------
    | Throttling for External Pages
    |--------------------------------------------------------------------------
    |
    | This value is the the number of seconds to delay between requests to
    | the external sites. These are pages not within the requested scan, and
    | which will not be processed themselves.
    |
    */

    'external' => env('THROTTLE_EXTERNAL', 10),


];
