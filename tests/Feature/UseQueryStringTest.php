<?php

namespace Goodcat\QueryString\Tests\Feature;

use Goodcat\QueryString\Console\QueryStringCacheCommand;
use Goodcat\QueryString\Tests\Support\FakeModel;
use Goodcat\QueryString\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UseQueryStringTest extends TestCase
{
    #[Test]
    public function it_generates_query_from_query_string(): void
    {
        $sql = (new FakeModel)->query()->queryString(['name' => 'John Doe'])->toSql();

        $this->assertStringContainsString('where "name" like ?', $sql);
    }

    #[Test]
    public function it_ignores_empty_query_string(): void
    {
        $sql = (new FakeModel)->query()->queryString(['email' => null])->toSql();

        $this->assertStringNotContainsString('where "email" like ?', $sql);
    }

    #[Test]
    public function it_handles_multiple_attributes_on_same_function(): void
    {
        $queryString = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ];

        $sql = (new FakeModel)->query()->queryString($queryString)->toSql();

        $this->assertStringContainsString('where "name" like ? and "email" like ?', $sql);
    }

    #[Test]
    public function it_uses_config_file(): void
    {
        config()->set('querystring.allows_null', true);

        $sql = (new FakeModel)->query()->queryString(['email' => null])->toSql();

        $this->assertStringContainsString('where "email" like ?', $sql);
    }

    #[Test]
    public function it_should_load_querystring_from_cache(): void
    {
        $this
            ->artisan(QueryStringCacheCommand::class, [
                '--namespace' => 'Goodcat\\QueryString\\Tests\\',
                '--path' => __DIR__.'/..',
            ]);

        $sql = (new FakeModel)->query()->queryString(['name' => 'John Doe'])->toSql();

        $this->assertStringContainsString('where "name" like ?', $sql);
    }
}
