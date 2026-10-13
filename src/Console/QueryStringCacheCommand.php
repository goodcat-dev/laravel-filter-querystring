<?php

namespace Goodcat\QueryString\Console;

use Goodcat\QueryString\QueryString;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'querystring:cache')]
class QueryStringCacheCommand extends Command
{
    protected $signature = 'querystring:cache 
                            {--namespace= : The namespace used by the path} 
                            {--path= : The path to search for Models}';

    protected $description = 'Discover and cache the application\'s query strings';

    public function handle(): void
    {
        $queryString = new QueryString;

        $methods = [];

        $models = $queryString->findModelsWithQueryStringTrait(
            $this->option('namespace'),
            $this->option('path')
        );

        foreach ($models as $modelClass) {
            $model = new $modelClass;

            if (! method_exists($model, 'getQueryStringObject')) {
                throw new LogicException("Model {$modelClass} must provide getQueryStringObject().");
            }

            /** @var object $object */
            $object = $model->getQueryStringObject();

            $methods[$modelClass] = $queryString->getMethodsFrom($object);
        }

        app(Filesystem::class)->replace(
            QueryString::getCachePath(),
            '<?php return '.var_export($methods, true).';'
        );

        $this->components->info('Query strings cached successfully.');
    }
}
