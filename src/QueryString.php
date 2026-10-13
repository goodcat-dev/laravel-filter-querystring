<?php

namespace Goodcat\QueryString;

use Goodcat\QueryString\Attributes\QueryString as QueryStringAttribute;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use ReflectionMethod;

class QueryString
{
    /**
     * @return array<string, string>
     */
    public function loadMethodsFromCache(object $object): array
    {
        /** @var array<class-string, array<string, string>> $cachedMethods */
        $cachedMethods = require QueryString::getCachePath();

        return $cachedMethods[get_class($object)] ?? [];
    }

    /**
     * @return array<string, string>
     */
    public function getMethodsFrom(object $object): array
    {
        $methods = [];

        $reflectionClass = new ReflectionClass($object);

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(QueryStringAttribute::class);

            foreach ($attributes as $attribute) {
                $queryString = $attribute->newInstance();

                foreach ($queryString->names as $name) {
                    $methods[$name] = $method->getName();
                }
            }
        }

        return $methods;
    }

    public function areCached(): bool
    {
        return file_exists(QueryString::getCachePath());
    }

    public static function getCachePath(): string
    {
        return app()->bootstrapPath('cache/querystring.php');
    }

    /**
     * @return list<class-string<Model>>
     */
    public function findModelsWithQueryStringTrait(?string $namespace = null, ?string $path = null): array
    {
        // Use valid PSR-4 path/namespace as defined in composer.json
        // E.g. "Goodcat\QueryString\Tests\" and __DIR__ . '../tests'
        $namespace ??= app()->getNamespace();
        $path ??= app()->path();

        $filesystem = app(Filesystem::class);

        $files = $filesystem->allFiles($path);

        $classes = [];

        foreach ($files as $file) {
            $classString = $namespace.$file->getRelativePathname();

            $classes[] = str_replace(['/', '.php'], ['\\', ''], $classString);
        }

        $models = [];

        foreach ($classes as $class) {
            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (
                $reflection->isSubclassOf(Model::class)
                && $reflection->isInstantiable()
                && in_array(UseQueryString::class, class_uses_recursive($class), true)
            ) {
                /** @var class-string<Model> $class */
                $models[] = $class;
            }
        }

        return $models;
    }
}
