<?php

namespace Tests\Unit;

use Goodcat\QueryString\Attributes\QueryString;
use Goodcat\QueryString\Tests\TestCase;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;

class UseQueryStringTest extends TestCase
{
    #[Test]
    public function it_gets_query_string_methods(): void
    {
        (new FakeModel)->query()->queryString(['name' => 'John Doe'])->dd();
    }
}

class FakeModel extends Model
{
    use UseQueryString;

    #[QueryString('name')]
    public function genericTextSearch(Builder $query, string $search, string $queryString): void
    {
        $query->where($queryString, 'like', "$search%");
    }
}