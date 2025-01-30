<?php

namespace Goodcat\QueryString\Traits;

use Goodcat\QueryString\Attributes\QueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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

        $queryStrings = is_array($request) ? $request : $request->query();

        foreach ($queryStrings as $key => $value) {
            if (! array_key_exists($key, $methods)) continue;

            $this->{$methods[$key]}($query, $value);
        }
    }

    protected function getQueryStringMethods(): array
    {
        $methods = [];

        $reflectionClass = new \ReflectionClass($this);

        foreach ($reflectionClass->getMethods() as $method) {
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
