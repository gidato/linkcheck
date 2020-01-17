<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection Age For Scans
    |--------------------------------------------------------------------------
    |
    | This value is the the time to keep scans. Anything older is deleted during
    | the garbage collection run
    |
    */

    'scan_age' => env('GC_SCAN_AGE', '6 months'),

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection Age For PDFs
    |--------------------------------------------------------------------------
    |
    | This value is the the time to keep PDFs. Anything older is deleted during
    | the garbage collection run
    |
    | Typically this can be low, as they are automatically regenerated if
    | they are needed
    |
    */

    'pdf_age' => env('GC_PDF_AGE', '5 days'),

];
