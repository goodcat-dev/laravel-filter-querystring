# Query String

This package allows you to filter Eloquent models using query string parameters.

## Quick Start

Get started with `laravel-querystring` in three steps.

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
   
3. Use the `queryString()` scope when you want to filter models based on query string parameters in the request.
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

## Caching

`laravel-querystring` scans your code to locate methods tagged with the `QueryString` attribute.
While the performance impact of this discovery is negligible, you can cache these methods using the Artisan command `querystring:cache`.
To clear the cache, use the Artisan command `querystring:clear`.

```shell
php artisan querystring:cache
```

To streamline your deployment process, `laravel-querystring` integrates with Laravel's `optimize` command.
Use the `optimize` and `optimize:clear` commands to create and remove the cache.

## Configuration

To publish the config file to `config/querystring.php` run the command:

```sh
php artisan vendor:publish --provider="Goodcat\QueryString\QueryStringServiceProvider"
```

### Handling `null` values

The `null` values are ignored by `laravel-querystring`. If you want `null` values passed to your function, set `'allows_null'` to `true` in `config/querystring.php` file.

## Digging deeper

Let's take a closer look at how `laravel-querystring` works under the hood and explore its advanced features.

### `#[QueryString]` attribute

The `QueryString` attribute is used to map the _name of a query string_ to a method. The attribute name must match the query string name.

```php
#[QueryString('name')]
public function filterByName(Builder $query, string $search): void 
```

E.g. The string `name` in the URL `http://example.com/?name=John+Doe` is mapped to the method tagged with the `#[QueryString('name')]` attribute.


### Filter methods

The filter method receives three parameters: the query builder, the query string value and the query string name. You can add multiple attributes to the same method.

```php
#[QueryString('name')]
#[QueryString('email')]
public function genericStringSearch(Build $query, string $search, string $name): void
{
    $query->where($name, 'like', "$search%");
}
```

### `queryString()` scope

The `queryString()` scope is responsible for calling your filter methods. It accepts a `Request` or an `array<string, string>`.

```php
public function index(Request $request): View
{
    $filters = $request->query();
    
    // Change $filters array as desired.
    
    $users = User::query()->queryString($filters)->get();
    
    return view('user.index', ['users' => $users]);
}
```

Laravel uses `TrimStrings` and `ConvertEmptyStringsToNull` middlewares to trim and nullify empty strings from requests. If you pass an `array` to the filter method, it's up to you to normalize the passed value.

### Filter object

By default, `laravel-querystring` searches the model for filter methods. If you wish, you can register a different class by overriding the `getQueryStringObject()` method.

```php
   class User extends Authenticatable
   {
       use UseQueryString;

        protected function getQueryStringObject(): object
        {
            return new CustomFilterClass();
        }
   }
```
