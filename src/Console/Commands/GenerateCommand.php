<?php

namespace Yui019\Hori\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateCommand extends Command
{
    protected $signature = 'hori:generate';

    public function handle()
    {
        $this->info('Generating migrations...');

        $schemaClass = require(getcwd() . "/database/hori/schema.php");
        $schema = new $schemaClass();
        $schema->create();

        $oldSchemaClass = require(getcwd() . "/database/hori/.old-schema/schema.php");
        $oldSchema = new $oldSchemaClass();
        $oldSchema->create();

        print_r($schema->blueprints);

        $this->info('Done!');
    }
}
