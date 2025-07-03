<?php

namespace Goodcat\QueryString\Tests\Feature;

use Goodcat\QueryString\QueryString;
use Goodcat\QueryString\Tests\Support\FakeModel;
use Goodcat\QueryString\Tests\TestCase;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Test;

class QueryStringTest extends TestCase
{
    #[Test]
    public function it_checks_if_query_string_are_cached()
    {
        $queryString = new QueryString;

        touch(QueryString::getCachePath());

        $this->assertTrue($queryString->areCached());

        unlink(QueryString::getCachePath());

        $this->assertFalse($queryString->areCached());
    }

    #[Test]
    public function it_gets_query_string_methods_from_object()
    {
        $queryString = new QueryString;

        $object = (new FakeModel)->getQueryStringObject();

        $methods = $queryString->getMethodsFrom($object);

        $this->assertEquals([
            'name' => 'genericTextSearch',
            'email' => 'genericTextSearch',
        ], $methods);
    }

    #[Test]
    public function it_loads_query_string_from_cache()
    {
        $model = new FakeModel;

        $cache = [
            get_class($model) => [
                'name' => 'genericTextSearch',
                'email' => 'genericTextSearch',
            ],
        ];

        $queryString = new QueryString;

        file_put_contents(
            QueryString::getCachePath(),
            '<?php return ' . var_export($cache, true) . ';'
        );

        $methods = $queryString->loadMethodsFromCache($model);

        $this->assertEquals([
            'name' => 'genericTextSearch',
            'email' => 'genericTextSearch',
        ], $methods);

        unlink(QueryString::getCachePath());
    }

    #[Test]
    public function it_finds_models_with_querystring_trait()
    {
        $queryString = new QueryString;

        $models = $queryString->findModelsWithQuerystringTrait(
            'Goodcat\\QueryString\\Tests\\',
            __DIR__ . '/..'
        );

        $this->assertEquals([
            'Goodcat\QueryString\Tests\Support\FakeModel'
        ], $models);
    }
}
