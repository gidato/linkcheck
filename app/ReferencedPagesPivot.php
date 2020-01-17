<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Support\Value\Reference\Reference;
use App\Support\Value\Reference\ReferenceInterface;

class ReferencedPagesPivot extends Pivot
{
    public function getReference(string $method) : ReferenceInterface
    {
        $attributes = $this->toArray();
        $attributes['method'] = $method;
        return Reference::fromArray($attributes);
    }

}
