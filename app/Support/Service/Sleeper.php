<?php

namespace App\Support\Service;

/**
 * wrapper class around PHP uleep function to ensure appropriate sleep times to be tested in PHPunit
 */

class Sleeper
{
    public function sleep(int $microSeconds) : void
    {
        usleep($microSeconds);
    }
}
