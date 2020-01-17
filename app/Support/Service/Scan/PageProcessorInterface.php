<?php

namespace App\Support\Service\Scan;

use App\Page;
use App\Support\Service\Scan\PageProcessorInterface;

interface PageProcessorInterface
{
    public function handle(Page $page) : void;


}
