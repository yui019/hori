<?php

namespace Yui019\Hori\Commands\GenerateCommand;

use Illuminate\Console\Command;
use Yui019\Hori\Util\StubHelper;

class GenerateCommand extends Command
{
    protected $signature = 'hori:generate';

    public function handle()
    {
        $this->info('Generating migrations...');

        $schemaClass = require getcwd() . "/database/hori/schema.php";
        $schema = new $schemaClass();
        $schema->create();

        $oldSchemaClass = require getcwd() . "/database/hori/.old-schema/schema.php";
        $oldSchema = new $oldSchemaClass();
        $oldSchema->create();

        // print_r($schema->blueprints);
        print_r(SchemaCompareHelper::compare($oldSchema, $schema));

        $stub = new StubHelper(__DIR__ . "/../../Stubs/migration.stub");
        $migrationBodyUp = new MigrationBodyHelper("test", "create");
        $migrationBodyDown = new MigrationBodyHelper("test", "dropIfExists");

        $stub->replace("{{ up }}", $migrationBodyUp->getContent());
        $stub->replace("{{ down }}", $migrationBodyDown->getContent());

        $migrationName = "test";
        $stub->save(getcwd() . "/database/migrations/" . $migrationName . ".php");

        // copy contents of current schema to old-schema
        file_put_contents(
            getcwd() . "/database/hori/.old-schema/schema.php",
            file_get_contents(getcwd() . "/database/hori/schema.php")
        );

        $this->info('Done!');
    }
}
