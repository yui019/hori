<?php

namespace Yui019\Hori\Commands\GenerateCommand;

use Illuminate\Support\Stringable;
use Yui019\Hori\Schema;
use Yui019\Hori\Util\StubHelper;

class MigrationBodyHelper
{
    protected Schema $oldSchema;
    protected Schema $newSchema;

    public function __construct(Schema $oldSchema, Schema $newSchema)
    {
        $this->oldSchema = $oldSchema;
        $this->newSchema = $newSchema;
    }

    public function getContentUp(): string
    {
        return self::getContent($this->oldSchema, $this->newSchema);
    }

    public function getContentDown(): string
    {
        return self::getContent($this->newSchema, $this->oldSchema);
    }

    private static function getContent(Schema $oldSchema, Schema $newSchema): string
    {
        $operations = SchemaCompareHelper::compare($oldSchema, $newSchema);
        $operations = OperationsSortHelper::sort($operations);

        $contents = [];
        foreach ($operations as $operation) {
            $content = "";

            if ($operation->type == TableOperationType::Drop) {
                $stub = new StubHelper(__DIR__ . "/../../Stubs/migration_body_empty.stub");
                $stub->replace("{{ table }}", $operation->table);
                $stub->replace("{{ type }}", "dropIfExists");

                $content = $stub->getContent();
            } else {
                $stub = new StubHelper(__DIR__ . "/../../Stubs/migration_body.stub");
                $stub->replace("{{ table }}", $operation->table);

                if ($operation->type == TableOperationType::Create) {
                    $stub->replace("{{ type }}", "create");
                } else if ($operation->type == TableOperationType::Modify) {
                    $stub->replace("{{ type }}", "table");
                } else {
                    assert(true, "Wtf this can't happen");
                }

                $stub->replace("{{ statements }}", self::getStatementsString($operation->statements));

                $content = $stub->getContent();
            }

            array_push($contents, $content);
        }

        return implode("\n", $contents);
    }

    /**
     * @param TableStatement[] $statements
     */
    private static function getStatementsString(array $statements): string
    {
        $statementStrings = [];

        foreach ($statements as $statement) {
            $statementString = "\t\t\t\$table->";

            if ($statement->type == TableStatementType::AddColumn) {
                $attributes = $statement->addedColumnDefinition->getAttributes();
                $type = $attributes["type"];
                $name = $attributes["name"];

                $params = [];
                foreach ($attributes as $key => $value) {
                    if ($key != "type" && $key != "name") {
                        if (is_bool($value)) {
                            $value = $value ? "true" : "false";
                        }

                        array_push($params, "'$key' => $value");
                    }
                }

                $paramsString = "[ " . implode(", ", $params) . " ]";

                $statementString .= "addColumn('$type', '$name', $paramsString)";
            } else if ($statement->type == TableStatementType::AddForeignKeyConstraint) {
                $attributes = $statement->addedForeignKeyDefinition->getAttributes();
                $column = $attributes["columns"][0];
                $references = $attributes["references"];
                $on = $attributes["on"];

                if ($on instanceof Stringable) {
                    $on = $on->toString();
                }

                $statementString .= "foreign('$column')->references('$references')->on('$on')";
            } else if ($statement->type == TableStatementType::DropColumn) {
                $name = $statement->droppedColumnName;

                $statementString .= "dropColumn('$name')";
            }

            $statementString .= ";";

            array_push($statementStrings, $statementString);
        }

        return implode("\n", $statementStrings);
    }
}
