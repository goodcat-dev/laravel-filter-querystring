<?php

namespace Goodcat\QueryString\Traits;

use Goodcat\QueryString\Attributes\QueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;

trait UseQueryString
{
    /**
     * @param Builder $query
     * @param Request|array<string, string> $request
     * @return void
     */
    public function scopeQueryString(Builder $query, Request|array $request): void
    {
        $methods = $this->getQueryStringMethods();

        $queryStrings = array_intersect_key(
            is_array($request) ? $request : $request->query(),
            $methods
        );

        $allowsNull = config('querystring.allows_null');

        foreach ($queryStrings as $key => $value) {
            if ($value === null && ! $allowsNull) continue;

            $this->{$methods[$key]}($query, $value, $key);
        }
    }

    protected function getQueryStringMethods(): array
    {
        $methods = [];

        $reflectionClass = new ReflectionClass($this);

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
}
