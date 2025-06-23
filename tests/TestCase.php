<?php

namespace Goodcat\QueryString\Tests;

use Goodcat\QueryString\QueryStringServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            QueryStringServiceProvider::class,
        ];
    }
}
