<?php

namespace Goodcat\QueryString\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
final readonly class QueryString
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(public string $name)
    {
        //
    }
}
