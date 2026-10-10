<?php

namespace Goodcat\QueryString\Tests\Feature;

use Goodcat\QueryString\Attributes\QueryString;
use Goodcat\QueryString\Tests\Support\FakeModel;
use Goodcat\QueryString\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class QueryRequestTest extends TestCase
{
    #[Test]
    public function it_filters_using_the_json_body_of_a_query_request(): void
    {
        config()->set('querystring.allows_null', true);

        $this->artisan('querystring:clear');

        $model = new class extends FakeModel
        {
            protected $table = 'fake_models';

            /** @param Builder<self> $query */
            #[QueryString('age')]
            public function filterByAge(Builder $query, int $age): void
            {
                $query->where('age', $age);
            }

            /** @param Builder<self> $query */
            #[QueryString('active', 'verified')]
            public function filterByBoolean(Builder $query, bool $value, string $name): void
            {
                $query->where($name, $value);
            }

            /**
             * @param  Builder<self>  $query
             * @param  list<int>  $ids
             */
            #[QueryString('ids')]
            public function filterByIds(Builder $query, array $ids): void
            {
                $query->whereIn('id', $ids);
            }

            /** @param Builder<self> $query */
            #[QueryString('deleted_at')]
            public function filterByDeletedAt(Builder $query, ?string $date): void
            {
                $query->where('deleted_at', $date);
            }
        };

        $request = Request::create('/users', 'QUERY', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"age":42,"active":true,"verified":false,"name":"John Doe","ids":[1,2],"deleted_at":null}');

        $query = $model->newQuery()->queryString($request);

        $this->assertSame(
            'select * from "fake_models" where "age" = ? and "active" = ? and "verified" = ? and "name" like ? and "id" in (?, ?) and "deleted_at" is null',
            $query->toSql()
        );
        $this->assertSame([42, true, false, 'John Doe%', 1, 2], $query->getBindings());
    }

    #[Test]
    public function it_filters_using_the_form_body_of_a_query_request(): void
    {
        $request = Request::create('/users', 'QUERY', ['name' => 'John Doe'], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $query = FakeModel::query()->queryString($request);

        $this->assertStringContainsString('where "name" like ?', $query->toSql());
        $this->assertSame(['John Doe%'], $query->getBindings());
    }

    #[Test]
    public function it_uses_only_the_form_body_when_a_query_request_also_has_url_parameters(): void
    {
        $request = Request::create('/users?name=Alice&email=alice@example.com', 'QUERY', ['name' => 'Bob'], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $query = FakeModel::query()->queryString($request);
        $expected = FakeModel::query()->where('name', 'like', 'Bob%');

        $this->assertSame($expected->toSql(), $query->toSql());
        $this->assertSame(['Bob%'], $query->getBindings());
    }

    #[Test]
    public function it_does_not_fall_back_to_url_parameters_when_the_query_form_body_is_empty(): void
    {
        $request = Request::create('/users?name=Alice&email=alice@example.com', 'QUERY', [], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $query = FakeModel::query()->queryString($request);

        $this->assertSame(FakeModel::query()->toSql(), $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    #[Test]
    public function it_uses_only_the_body_when_a_query_request_also_has_url_parameters(): void
    {
        $request = Request::create('/users?name=Alice&email=alice@example.com', 'QUERY', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"name":"Bob"}');

        $query = FakeModel::query()->queryString($request);

        $this->assertStringContainsString('where "name" like ?', $query->toSql());
        $this->assertStringNotContainsString('"email"', $query->toSql());
        $this->assertSame(['Bob%'], $query->getBindings());
    }

    #[Test]
    public function it_does_not_fall_back_to_url_parameters_when_the_query_body_is_empty(): void
    {
        $request = Request::create('/users?name=Alice', 'QUERY', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{}');

        $query = FakeModel::query()->queryString($request);

        $this->assertSame(FakeModel::query()->toSql(), $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    #[Test]
    #[DataProvider('nonQueryMethods')]
    public function it_keeps_using_only_url_parameters_for_other_http_methods(string $method): void
    {
        $request = Request::create('/users?name=Alice', $method, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"name":"Bob","email":"bob@example.com"}');

        $query = FakeModel::query()->queryString($request);

        $this->assertStringContainsString('where "name" like ?', $query->toSql());
        $this->assertStringNotContainsString('"email"', $query->toSql());
        $this->assertSame(['Alice%'], $query->getBindings());
    }

    #[Test]
    public function it_ignores_unknown_filters_in_the_query_body(): void
    {
        $request = Request::create('/users', 'QUERY', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"name":"John Doe","unknown":"ignored"}');

        $query = FakeModel::query()->queryString($request);

        $this->assertStringContainsString('where "name" like ?', $query->toSql());
        $this->assertStringNotContainsString('"unknown"', $query->toSql());
        $this->assertSame(['John Doe%'], $query->getBindings());
    }

    #[Test]
    #[DataProvider('nullHandling')]
    public function it_respects_the_null_configuration_for_query_body_filters(bool $allowsNull): void
    {
        config()->set('querystring.allows_null', $allowsNull);

        $request = Request::create('/users', 'QUERY', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"name":"John Doe","email":null}');

        $query = FakeModel::query()->queryString($request);

        $expected = FakeModel::query()->where('name', 'like', 'John Doe%');

        if ($allowsNull) {
            $expected->where('email', 'like', '%');
        }

        $this->assertSame($expected->toSql(), $query->toSql());
        $this->assertSame($expected->getBindings(), $query->getBindings());
    }

    /** @return array<string, array{string}> */
    public static function nonQueryMethods(): array
    {
        return [
            'GET' => ['GET'],
            'POST' => ['POST'],
            'PUT' => ['PUT'],
            'PATCH' => ['PATCH'],
            'DELETE' => ['DELETE'],
        ];
    }

    /** @return array<string, array{bool}> */
    public static function nullHandling(): array
    {
        return [
            'ignore null values' => [false],
            'pass null values to filters' => [true],
        ];
    }
}
