<?php

namespace Goodcat\QueryString\Tests\Support;

use Goodcat\QueryString\Attributes\QueryString;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FakeModel extends AbstractModel
{
    /**
     * @param  Builder<self>  $query
     */
    #[QueryString('name', 'email')]
    #[QueryString('description')]
    public function genericTextSearch(Builder $query, ?string $search, string $queryString): void
    {
        $query->where($queryString, 'like', "$search%");
    }
}
