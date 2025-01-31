<?php

namespace Goodcat\QueryString\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class QueryString
{
    public function __construct(public readonly string $name)
    {
        //
    }
}
