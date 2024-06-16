<?php

namespace Yui019\Hori\Commands\GenerateCommand;

use Yui019\Hori\Util\StubHelper;

class MigrationBodyHelper
{
    protected $table;
    protected $type;
    protected $statements;

    public function __construct(string $table, string $type)
    {
        $this->table = $table;
        $this->type = $type;
        $this->statements = [];
    }

    public function addStatement(): void
    {
    }

    public function getContent(): string
    {
        if ($this->type == "dropIfExists") {
            $stub = new StubHelper(__DIR__ . "/../../Stubs/migration_body_empty.stub");
            $stub->replace("{{ type }}", $this->type);
            $stub->replace("{{ table }}", $this->table);

            return $stub->getContent();
        } else {
            $stub = new StubHelper(__DIR__ . "/../../Stubs/migration_body.stub");
            $stub->replace("{{ type }}", $this->type);
            $stub->replace("{{ table }}", $this->table);
            $stub->replace("{{ statements }}", implode("\n", $this->statements));

            return $stub->getContent();
        }
    }
}
