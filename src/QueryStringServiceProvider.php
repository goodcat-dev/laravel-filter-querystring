<?php

namespace Goodcat\QueryString;

use Illuminate\Support\ServiceProvider;

class QueryStringServiceProvider extends ServiceProvider 
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/querystring.php' => config_path('querystring.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/querystring.php',
            'querystring'
        );
    }
}