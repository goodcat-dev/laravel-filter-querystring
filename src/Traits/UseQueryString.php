<?php

namespace Goodcat\QueryString\Traits;

use Goodcat\QueryString\QueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait UseQueryString
{
    /**
     * @param  Builder<self>  $query
     * @param  Request|array<string, ?string>  $request
     */
    public function scopeQueryString(Builder $query, Request|array $request): void
    {
        $queryString = new QueryString;

        $object = $this->getQueryStringObject();

        $methods = $queryString->areCached()
            ? $queryString->loadMethodsFromCache($this)
            : $queryString->getMethodsFrom($object);

        $parameters = $request;

        if ($request instanceof Request) {
            $parameters = $request->query();

            if ($request->isMethod('QUERY')) {
                $parameters = $request->isJson() ? $request->json()->all() : $request->request->all();
            }
        }

        $queryStrings = array_intersect_key($parameters, $methods);

        $allowsNull = config('querystring.allows_null');

        foreach ($queryStrings as $key => $value) {
            if ($value === null && ! $allowsNull) {
                continue;
            }

            $object->{$methods[$key]}($query, $value, $key);
        }
    }

    public function getQueryStringObject(): object
    {
        return $this;
    }
}
