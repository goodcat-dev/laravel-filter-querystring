<?php

use Goodcat\QueryString\QueryStringServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders(): array
    {
        return [
            QueryStringServiceProvider::class,
        ];
    }
}