<?php

namespace Goodcat\QueryString;

use Goodcat\QueryString\Console\QueryStringCacheCommand;
use Goodcat\QueryString\Console\QueryStringClearCommand;
use Illuminate\Support\ServiceProvider;

class QueryStringServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/querystring.php' => config_path('querystring.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                QueryStringCacheCommand::class,
                QueryStringClearCommand::class,
            ]);

            $this->optimizes(
                optimize: 'querystring:cache',
                clear: 'querystring:clear',
            );
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/querystring.php',
            'querystring'
        );
    }
}
