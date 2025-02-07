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