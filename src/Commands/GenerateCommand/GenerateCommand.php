<?php

namespace Yui019\Hori\Commands\GenerateCommand;

use Illuminate\Console\GeneratorCommand;
use Yui019\Hori\Commands\GenerateCommand\MigrationBodyHelper;

class GenerateCommand extends GeneratorCommand
{
    protected $signature = 'hori:generate {name}';

    protected $type = "Migration";

    protected function getStub()
    {
        return realpath(__DIR__ . "/../../Stubs/migration.stub");
    }

    protected function getPath($name)
    {
        // for some reason if the user inputs "test", $name will be "App\test"
        // this puts the $name without the "App\" into $migrationName
        $migrationName = explode("\\", $name)[1];

        // 2024_06_17_123456_migrationName
        $filename = date("Y_m_d_") . time() . "_" . $migrationName;

        return getcwd() . "/database/migrations/" . $filename . ".php";
    }

    protected function replaceClass($stub, $name)
    {
        $newSchemaClass = require getcwd() . "/database/hori/schema.php";
        $newSchema = new $newSchemaClass();
        $newSchema->create();

        $oldSchemaClass = require getcwd() . "/database/hori/.old-schema/schema.php";
        $oldSchema = new $oldSchemaClass();
        $oldSchema->create();

        $migrationBodyHelper = new MigrationBodyHelper($oldSchema, $newSchema);

        $newStub = $stub;
        $newStub = str_replace("{{ up }}", $migrationBodyHelper->getContentUp(), $newStub);
        $newStub = str_replace("{{ down }}", $migrationBodyHelper->getContentDown(), $newStub);

        // copy contents of current schema to old-schema
        file_put_contents(
            getcwd() . "/database/hori/.old-schema/schema.php",
            file_get_contents(getcwd() . "/database/hori/schema.php")
        );

        return $newStub;
    }
}
