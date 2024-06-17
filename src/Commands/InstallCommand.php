<?php

namespace Yui019\Hori\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Yui019\Hori\Util\StubHelper;

class InstallCommand extends Command
{
    protected $signature = 'hori:install
                            {--dont-delete-default-migrations : Do not delete the 3 default laravel migrations.}';

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

        $stubEmpty = new StubHelper(__DIR__ . "/../Stubs/schema_empty.stub");
        $stubEmpty->save("database/hori/.old-schema/schema.php");

        if (!$this->option("dont-delete-default-migrations")) {
            $this->deleteDefaultMigrations();
        }

        $this->info('Done!');
    }

    private function deleteDefaultMigrations()
    {
        File::delete("database/migrations/0001_01_01_000000_create_users_table.php");
        File::delete("database/migrations/0001_01_01_000001_create_cache_table.php");
        File::delete("database/migrations/0001_01_01_000002_create_jobs_table.php");
    }
}
