<?php

namespace Yui019\Hori;

use Illuminate\Support\ServiceProvider;
use Yui019\Hori\Console\Commands\InstallCommand;
use Yui019\Hori\Console\Commands\GenerateCommand\GenerateCommand;

class LaravelCommandServiceProvider extends ServiceProvider
{
    protected $commands = [
        InstallCommand::class,
        GenerateCommand::class
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }
}
