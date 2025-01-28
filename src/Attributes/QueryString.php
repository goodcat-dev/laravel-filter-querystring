<?php

namespace Goodcat\QueryString\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class QueryString
{
    public function __construct(readonly string $name)
    {
        //
    }
}
