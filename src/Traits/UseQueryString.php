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
        $reflectionClass = new \ReflectionClass($this);

        $methods = [];

        foreach ($reflectionClass->getMethods() as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                if ($attribute->getName() !== QueryString::class) continue;

                /** @var QueryString $queryString */
                $queryString = $attribute->newInstance();

                $methods[$queryString->name] = $method->getName();
            }
        }

        $queryStrings = is_array($request) ? $request : $request->query();

        foreach ($queryStrings as $key => $value) {
            if (in_array($value, [null, ''], true) || ! array_key_exists($key, $methods)) continue;

            $this->{$methods[$key]}($query, $value);
        }
    }
}
