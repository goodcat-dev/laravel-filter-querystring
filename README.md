# Query String

This package maps the query strings of a request to custom methods.

## Quick Start

Start using `laravel-querystring` in three steps.

1. Download the package via Composer.
   ```sh
   composer require goodcat/laravel-querystring
   ```
2. Add the `UseQueryString` trait to your model and tag a method with the `QueryString` attribute.
   ```php
   use Illuminate\Database\Eloquent\Builder;
   use Goodcat\QueryString\Traits\UseQueryString;
   use Goodcat\QueryString\Attributes\QueryString;
   
   class User extends Authenticatable
   {
       use UseQueryString;
       
       #[QueryString('email')]
       public function filterByEmail(Builder $query, string $search): void
       {
           $query->where('email', $search);
       }
   }
   ```
3. Use the `queryString()` scope when you want to filter by query strings in the request.
   ```php
   class UserController extends Controller
   {
       public function index(Request $request): View
       {
           // E.g. https://example.com/users?email=john@doe.com
           $users = User::query()->queryString($request)->get();
   
           return view('user.index', ['users' => $users]);
       }
   }
   ```

That's it. You're all set to start using `laravel-querystring`.

## Filter functions

A filter function is a method tagged with the `QueryString` attribute. The attribute receives a parameter representing the _name of the query string_.

```php
#[QueryString('name')]
public function filterByName(Builder $query, string $search, string $name): void 
```

E.g. The string `name` in the URL `http:example.com/?name=John+Doe` is mapped to the method tagged with `#[QueryString('name')]` attribute.

The filter function receives three parameters: the _query builder_, the _value of the query string_ and the _name of the query string_. You can add multiple attributes to the same method.

```php
#[QueryString('name')]
#[QueryString('email')]
public function genericStringSearch(Build $query, string $search, string $name): void
{
    $query->where($name, 'like', "$search%");
}
```

## `queryString()` scope

The `queryString()` scope is the local scope you call when you want to filter by query strings. It accepts a `Request` or an `array<string, string>`.

```php
public function index(Request $request): View
{
    $filters = $request->query();
    
    // Change $filters array as desired.
    
    $users = User::query()->queryString($filters)->get();
    
    return view('user.index', ['users' => $users]);
}
```

## Configuration

To publish the config file to `config/querystring.php` run the command:

```sh
php artisan vendor:publish --provider="Goodcat\QueryString\QueryStringServiceProvider"
```

### `null` values

The `null` values are ignored by `laravel-querystring`. If you want `null` values passed to your function, set `'allows_null'` to `true` in `config/querystring.php` file.

> [!NOTE]
> Laravel uses `TrimStrings` and `ConvertEmptyStringsToNull` to trim and nullify empty query strings from requests. If you are passing an `array` instead of the `$request`, is up to you normalize the passed values to the filter functions.
