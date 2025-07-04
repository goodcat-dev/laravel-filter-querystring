<?php

namespace Goodcat\QueryString\Tests\Support;

use Goodcat\QueryString\Attributes\QueryString;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FakeModel extends Model
{
    use UseQueryString;

    /**
     * @param  Builder<self>  $query
     */
    #[QueryString('name')]
    #[QueryString('email')]
    public function genericTextSearch(Builder $query, ?string $search, string $queryString): void
    {
        $query->where($queryString, 'like', "$search%");
    }
}
