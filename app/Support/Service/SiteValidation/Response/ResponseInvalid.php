<?php

namespace App\Support\Service\SiteValidation\Response;

class ResponseInvalid implements ResponseInterface
{
    public function isOk() : bool
    {
        return false;
    }
}
