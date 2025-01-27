<?php

namespace Goodcat\QueryString\Concerns;

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
        $filters = is_array($request) ? $request : $request->query();

        foreach ($filters as $key => $value) {
            if ($value === null) unset($filters[$key]);
        }

        dd($filters);
    }
}
