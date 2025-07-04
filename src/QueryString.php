<?php

namespace Goodcat\QueryString;

use Exception;
use Goodcat\QueryString\Attributes\QueryString as QueryStringAttribute;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
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
     * @return array<string, ?string>
     */
    public function getMethodsFrom(object $object): array
    {
        $methods = [];

        $reflectionClass = new ReflectionClass($object);

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(QueryStringAttribute::class);

            foreach ($attributes as $attribute) {
                /** @var QueryStringAttribute $queryString */
                $queryString = $attribute->newInstance();

                $methods[$queryString->name] = $method->getName();
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
        return App::bootstrapPath('cache/querystring.php');
    }

    /**
     * @return array<class-string>
     */
    public function findModelsWithQueryStringTrait(?string $namespace = null, ?string $path = null): array
    {
        // Use valid PSR-4 path/namespace as defined in composer.json
        // E.g. "Goodcat\QueryString\Tests\" and __DIR__ . '../tests'
        $namespace ??= App::getNamespace();
        $path ??= App::path();

        /** @var Filesystem $filesystem */
        $filesystem = App::make(Filesystem::class);

        $files = $filesystem->allFiles($path);

        $classes = [];

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $classString = $namespace.$file->getRelativePathname();

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
                /** @var class-string<Model> $class */
                $models[] = $class;
            }
        }

        return $models;
    }
}
