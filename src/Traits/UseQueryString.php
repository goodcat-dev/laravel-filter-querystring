<?php

namespace Goodcat\QueryString\Traits;

use Goodcat\QueryString\Attributes\QueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use ReflectionClass;
use ReflectionMethod;

trait UseQueryString
{
    /**
     * @param  Builder<self>  $query
     * @param  Request|array<string, ?string>  $request
     */
    public function scopeQueryString(Builder $query, Request|array $request): void
    {
        $object = $this->getQueryStringObject();

        $methods = $this->getQueryStringMethodsFromCache();

        if (!$methods) $methods = $this->getQueryStringMethods($object);

        $queryStrings = array_intersect_key(
            is_array($request) ? $request : $request->query(),
            $methods
        );

        $allowsNull = config('querystring.allows_null');

        foreach ($queryStrings as $key => $value) {
            if ($value === null && ! $allowsNull) {
                continue;
            }

            $object->{$methods[$key]}($query, $value, $key);
        }
    }

    protected function getQueryStringMethodsFromCache(): array
    {
        $cachePath = App::bootstrapPath('cache/querystring.php');

        if (!file_exists($cachePath)) return [];

        /** @var array<class-string, array> $cachedMethods */
        $cachedMethods = require $cachePath;

        $classString = get_class($this);

        if (!array_key_exists($classString, $cachedMethods)) return [];

        return $cachedMethods[$classString];
    }

    /**
     * @return array<string, ?string>
     */
    public function getQueryStringMethods(object $object): array
    {
        $methods = [];

        $reflectionClass = new ReflectionClass($object);

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(QueryString::class);

            foreach ($attributes as $attribute) {
                /** @var QueryString $queryString */
                $queryString = $attribute->newInstance();

                $methods[$queryString->name] = $method->getName();
            }
        }

        return $methods;
    }

    public function getQueryStringObject(): object
    {
        return $this;
    }
}
