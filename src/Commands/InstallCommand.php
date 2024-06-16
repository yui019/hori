<?php

namespace Yui019\Hori\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Yui019\Hori\Util\StubHelper;

class InstallCommand extends Command
{
    protected $signature = 'hori:install';

    public function handle()
    {
        $this->info('Installing Hori...');

        if (File::exists("database/hori")) {
            $this->fail("Hori is already installed!");
        }

        File::makeDirectory("database/hori");
        File::makeDirectory("database/hori/.old-schema");

        $stub = new StubHelper(__DIR__ . "/../Stubs/schema.stub");
        $stub->save("database/hori/schema.php");
        $stub->save("database/hori/.old-schema/schema.php");

        $this->info('Done!');
    }
}
