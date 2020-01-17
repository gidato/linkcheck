<?php

namespace App\Support\Service\SiteValidation\Response;

class ResponseOk implements ResponseInterface
{
    public function isOk() : bool
    {
        return true;
    }
}
