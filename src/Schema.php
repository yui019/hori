<?php

namespace Yui019\Hori;

use Closure;
use Illuminate\Database\Schema\Blueprint;

class Schema
{
    public array $blueprints = [];

    /**
     * Create a new table.
     */
    public function table(string $name, Closure $callback): void
    {
        $blueprint = new Blueprint($name);
        $callback($blueprint);

        array_push($this->blueprints, $blueprint);
    }
}
