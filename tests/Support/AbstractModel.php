<?php

namespace Goodcat\QueryString\Tests\Support;

use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
    use UseQueryString;
}
