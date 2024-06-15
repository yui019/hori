<?php

namespace Yui019\Hori;

use Closure;
use Illuminate\Database\Schema\Blueprint;

class Schema
{
    public $blueprints = [];

    public function table(string $name, Closure $callback)
    {
        $blueprint = new Blueprint($name);
        $callback($blueprint);

        $this->blueprints[] = $blueprint;
    }
}
