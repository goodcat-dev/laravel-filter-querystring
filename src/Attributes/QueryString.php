<?php

namespace Goodcat\QueryString\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class QueryString
{
    public array $names;

    /**
     * @param  non-empty-string  ...$names
     */
    public function __construct(string ...$names)
    {
        $this->names = $names;
    }
}
