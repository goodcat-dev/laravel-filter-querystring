<?php

namespace Goodcat\QueryString\Tests\Feature;

use Goodcat\QueryString\Console\QueryStringCacheCommand;
use Goodcat\QueryString\QueryString;
use Goodcat\QueryString\Tests\Support\AbstractModel;
use Goodcat\QueryString\Tests\Support\FakeModel;
use Goodcat\QueryString\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;

class QueryStringCommandTest extends TestCase
{
    #[Test]
    public function it_caches_query_string_methods(): void
    {
        $this
            ->mockArtisan(QueryStringCacheCommand::class, [
                '--namespace' => 'Goodcat\\QueryString\\Tests\\',
                '--path' => __DIR__.'/..',
            ])
            ->assertExitCode(0);

        $methods = require QueryString::getCachePath();

        $this->assertEquals([
            'Goodcat\QueryString\Tests\Support\FakeModel' => [
                'name' => 'genericTextSearch',
                'email' => 'genericTextSearch',
                'description' => 'genericTextSearch',
            ],
        ], $methods);

        unlink(QueryString::getCachePath());
    }

    #[Test]
    public function it_excludes_abstract_models_when_generating_cache(): void
    {
        try {
            $this->mockArtisan(QueryStringCacheCommand::class, [
                '--namespace' => 'Goodcat\\QueryString\\Tests\\Support\\',
                '--path' => __DIR__.'/../Support',
            ])->assertExitCode(0);

            /** @var array<class-string<Model>, array<string, string>> $methods */
            $methods = require QueryString::getCachePath();

            $this->assertArrayNotHasKey(AbstractModel::class, $methods);
            $this->assertArrayHasKey(FakeModel::class, $methods);
        } finally {
            $this->mockArtisan('querystring:clear')->assertExitCode(0);
        }
    }

    #[Test]
    public function it_clears_query_string_cache(): void
    {
        touch(QueryString::getCachePath());

        $this->mockArtisan('querystring:clear')->assertExitCode(0);

        $this->assertFileDoesNotExist(QueryString::getCachePath());
    }
}
