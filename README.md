# Laravel Filter Query String

[![Latest Version on Packagist](https://img.shields.io/packagist/v/goodcat/laravel-filter-querystring.svg?style=flat-square)](https://packagist.org/packages/goodcat/laravel-filter-querystring)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/goodcat-dev/laravel-filter-querystring/test.yaml?branch=main&label=tests&style=flat-square)](https://github.com/goodcat-dev/laravel-filter-querystring/actions?query=workflow%3Atests+branch%3Amain)

Filter Eloquent models using URL query parameters or HTTP `QUERY` request bodies.

## Installation

Requires PHP 8.2+ and Laravel 11, 12 or 13.

```sh
composer require goodcat/laravel-filter-querystring
```

## Quick Start

Add `UseQueryString` to your model and define a public filter method:

```php
use Goodcat\QueryString\Attributes\QueryString;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use UseQueryString;

    #[QueryString('email')]
    public function filterByEmail(Builder $query, string $email): void
    {
        $query->where('email', $email);
    }
}
```

Register a route in `routes/api.php`:

```php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/users', function (Request $request) {
    return User::query()->queryString($request)->get();
});
```

Request `/api/users?email=john%40doe.com` to find users with that email. Parameters without a matching filter are ignored.

## Filter Methods

Filters receive the builder, the value and, optionally, the parameter name. Use multiple names to share a filter:

```php
#[QueryString('name', 'email')]
public function filterByText(Builder $query, string $value, string $name): void
{
    $query->where($name, 'like', $value.'%');
}
```

You can also repeat the attribute on a method. Validate input in your application before filtering.

## Passing an Array

Pass an array to choose the filter values directly:

```php
$users = User::query()->queryString(['email' => 'john@doe.com'])->get();
```

Array values are passed as provided, without trimming or converting empty strings to `null`.

## HTTP `QUERY` Requests

For `QUERY`, filters come only from the JSON or URL-encoded form body. URL parameters are ignored, even when the body is empty. Other methods, including `POST`, use only URL parameters.

Using the same imports as the Quick Start, register a route in `routes/api.php`:

```php
Route::match(['QUERY'], '/users', function (Request $request) {
    return User::query()->queryString($request)->get();
});
```

Send a JSON body:

```sh
curl -X QUERY https://example.com/api/users \
    -H 'Content-Type: application/json' \
    -d '{"email":"john@doe.com"}'
```

Or a URL-encoded form body:

```sh
curl -X QUERY https://example.com/api/users \
    --data-urlencode 'email=john@doe.com'
```

## Filter Objects

To keep filters in a separate class, override this public method on your model:

```php
public function getQueryStringObject(): object
{
    return new UserFilters;
}
```

Define public methods on `UserFilters` with the same attributes and signatures as model filters.

## Null Values

`null` values are ignored by default. To pass them to your filters, publish the configuration:

```sh
php artisan vendor:publish --provider="Goodcat\QueryString\QueryStringServiceProvider"
```

Set `'allows_null' => true` in `config/querystring.php` and allow `null` in the relevant filter parameters.

## Caching

Cache filters during deployment and regenerate the cache after changing models or filters:

```sh
php artisan querystring:cache
```

Clear it with `php artisan querystring:clear`. Laravel's `optimize` and `optimize:clear` commands also create and clear this cache.
