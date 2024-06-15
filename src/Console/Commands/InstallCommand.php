<?php

namespace Yui019\Hori\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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

        $contents = file_get_contents(__DIR__ . "/../Stubs/schema.stub");
        file_put_contents("database/hori/schema.php", $contents);
        file_put_contents("database/hori/.old-schema/schema.php", $contents);

        $this->info('Done!');
    }
}
