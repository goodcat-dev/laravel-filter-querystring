<?php

namespace Goodcat\QueryString\Console;

use Exception;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'querystring:cache')]
class QueryStringCacheCommand extends Command
{
    protected $signature = 'querystring:cache';

    protected $description = 'Discover and cache the application\'s query strings';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->callSilently('querystring:clear');

        $models = $this->getAllModels(app_path());

        $methods = [];

        foreach ($models as $modelClass) {
            $model = new $modelClass;

            $object = $model->getQueryStringObject();

            $methods[$modelClass] = $model->getQueryStringMethods($object);
        }

        file_put_contents(
            App::bootstrapPath('cache/querystring.php'),
            '<?php return ' . var_export($methods, true) . ';'
        );

        $this->components->info('Query strings cached successfully.');
    }

    /**
     * @return class-string[]
     */
    protected function getAllModels(string $path): array
    {
        $files = $this->files->allFiles($path);

        $namespace = App::getNamespace();

        $classes = [];

        foreach ($files as $file) {
            if ($file->isDir()) continue;

            $classString = $namespace . $file->getRelativePathname('.php');

            $classes[] = str_replace(['/', '.php'], ['\\', ''], $classString);
        }

        $models = [];

        foreach ($classes as $class) {
            try {
                $reflection = new ReflectionClass($class);
            } catch (Exception) {
                continue;
            }

            if (
                $reflection->isSubclassOf(Model::class)
                && in_array(UseQueryString::class, array_keys($reflection->getTraits()))
            ) {
                $models[] = $class;
            }
        }

        return $models;
    }
}