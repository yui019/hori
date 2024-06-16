<?php

namespace Yui019\Hori\Commands\GenerateCommand;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Yui019\Hori\Schema;

class SchemaCompareHelper
{
    /**
     * Return all required operations to go from oldSchema to newSchema
     * @return TableOperation[]
     */
    public static function compare(Schema $oldSchema, Schema $newSchema): array
    {
        $createdTables = self::getCreatedTables($oldSchema, $newSchema);
        $droppedTables = self::getDroppedTables($oldSchema, $newSchema);
        $modifiedTables = self::getModifiedTables($oldSchema, $newSchema);

        $operations = [];

        foreach ($createdTables as $table) {
            array_push($operations, self::getTableCreateOperation($table));
        }

        foreach ($droppedTables as $table) {
            array_push($operations, self::getTableDropOperation($table));
        }

        foreach ($modifiedTables as $table) {
            $newTable = $table;
            $oldTable = null;
            foreach ($oldSchema->blueprints as $blueprint) {
                if ($blueprint->getTable() == $newTable->getTable()) {
                    $oldTable = $blueprint;
                }
            }

            assert($oldTable != null, "Wtf this can't happen");

            $modifyOperations = self::getTableModifyOperation($oldTable, $newTable);
            if (count($modifyOperations->statements) > 0) {
                array_push($operations, $modifyOperations);
            }
        }

        return $operations;
    }

    private static function getTableCreateOperation(Blueprint $table): TableOperation
    {
        $result = new TableOperation;
        $result->table = $table->getTable();
        $result->type = TableOperationType::Create;
        $result->statements = [];

        // Add all AddColumn statements
        foreach ($table->getColumns() as $column) {
            $statement = new TableStatement;
            $statement->type = TableStatementType::AddColumn;
            $statement->addedColumnDefinition = $column;

            array_push($result->statements, $statement);
        }

        // Add all AddForeignKeyConstraint statements
        // Commands in blueprints can be dropColumn, rename, etc. but the only
        // ones that will show up in Hori schemas are foreign key definitions
        foreach ($table->getCommands() as $command) {
            if ($command instanceof ForeignKeyDefinition) {
                $statement = new TableStatement;
                $statement->type = TableStatementType::AddForeignKeyConstraint;
                $statement->addedForeignKeyDefinition = $command;

                array_push($result->statements, $statement);
            }
        }

        return $result;
    }

    private static function getTableDropOperation(Blueprint $table): TableOperation
    {
        $result = new TableOperation;
        $result->table = $table->getTable();
        $result->type = TableOperationType::Drop;

        return $result;
    }

    private static function getTableModifyOperation(Blueprint $oldTable, Blueprint $newTable): TableOperation
    {
        $result = new TableOperation;
        $result->table = $oldTable->getTable(); // doesn't matter if you get the table name from oldTable or newTable
        $result->type = TableOperationType::Modify;
        $result->statements = [];

        // find all added columns
        $addedColumns = [];
        foreach ($newTable->getColumns() as $column) {
            $existsInOldTable = false;
            foreach ($oldTable->getColumns() as $oldColumn) {
                if ($oldColumn->getAttributes()["name"] == $column->getAttributes()["name"]) {
                    $existsInOldTable = true;
                    break;
                }
            }

            if (!$existsInOldTable) {
                array_push($addedColumns, $column);
            }
        }

        // find all dropped columns
        $droppedColumns = [];
        foreach ($oldTable->getColumns() as $column) {
            $existsInNewTable = false;
            foreach ($newTable->getColumns() as $newColumn) {
                if ($newColumn->getAttributes()["name"] == $column->getAttributes()["name"]) {
                    $existsInNewTable = true;
                    break;
                }
            }

            if (!$existsInNewTable) {
                array_push($droppedColumns, $column);
            }
        }

        // find all added foreign key definitions
        $addedForeignKeyDefinitions = [];
        foreach ($newTable->getCommands() as $command) {
            if (!($command instanceof ForeignKeyDefinition)) {
                continue;
            }

            $existsInOldTable = false;
            foreach ($oldTable->getCommands() as $oldCommand) {
                if (!($oldCommand instanceof ForeignKeyDefinition)) {
                    continue;
                }

                if ($oldCommand->getAttributes()["index"] == $command->getAttributes()["index"]) {
                    $existsInOldTable = true;
                    break;
                }
            }

            if (!$existsInOldTable) {
                array_push($addedForeignKeyDefinitions, $command);
            }
        }

        /// GENERATE STATEMENTS
        /// ===================

        foreach ($addedColumns as $addedColumn) {
            $statement = new TableStatement;
            $statement->type = TableStatementType::AddColumn;
            $statement->addedColumnDefinition = $addedColumn;

            array_push($result->statements, $statement);
        }

        foreach ($droppedColumns as $droppedColumn) {
            $statement = new TableStatement;
            $statement->type = TableStatementType::DropColumn;
            $statement->droppedColumnName = $droppedColumn->getAttributes()["name"];

            array_push($result->statements, $statement);
        }

        foreach ($addedForeignKeyDefinitions as $addedForeignKeyDefinition) {
            $statement = new TableStatement;
            $statement->type = TableStatementType::AddForeignKeyConstraint;
            $statement->addedForeignKeyDefinition = $addedForeignKeyDefinition;

            array_push($result->statements, $statement);
        }

        return $result;
    }

    /**
     * Find every table in newSchema that doesn't exist in oldSchema
     * @return Blueprint[]
     */
    private static function getCreatedTables(Schema $oldSchema, Schema $newSchema): array
    {
        $createdTables = [];
        foreach ($newSchema->blueprints as $blueprint) {
            $existsInOldSchema = false;
            foreach ($oldSchema->blueprints as $oldBlueprint) {
                if ($oldBlueprint->getTable() == $blueprint->getTable()) {
                    $existsInOldSchema = true;
                    break;
                }
            }

            if (!$existsInOldSchema) {
                array_push($createdTables, $blueprint);
            }
        }

        return $createdTables;
    }

    /**
     * Find every table in oldSchema that doesn't exist in newSchema
     * ...in other words, just the opposite of getCreatedTables
     * @return Blueprint[]
     */
    private static function getDroppedTables(Schema $oldSchema, Schema $newSchema): array
    {
        return self::getCreatedTables($newSchema, $oldSchema);
    }

    /**
     * Find every table that exists in both oldSchema and newSchema
     * @return Blueprint[]
     */
    private static function getModifiedTables(Schema $oldSchema, Schema $newSchema): array
    {
        $modifiedTables = [];
        foreach ($newSchema->blueprints as $blueprint) {
            $existsInOldSchema = false;
            foreach ($oldSchema->blueprints as $oldBlueprint) {
                if ($oldBlueprint->getTable() == $blueprint->getTable()) {
                    $existsInOldSchema = true;
                    break;
                }
            }

            if ($existsInOldSchema) {
                array_push($modifiedTables, $blueprint);
            }
        }

        return $modifiedTables;
    }
}

// Types of operations you can do on tables
enum TableOperationType {
    case Create;
    case Modify;
    case Drop;
    // TODO: also support Rename
}

// Types of statements you can do in a table
enum TableStatementType {
    case AddColumn;
    case DropColumn;
    case AddForeignKeyConstraint;
    // TODO: also support ModifyColumn and RenameColumn
}

class TableStatement
{
    public TableStatementType $type;

    public string $droppedColumnName; // in case of type = DropColumn
    public ColumnDefinition $addedColumnDefinition; // in case of AddColumn
    public ForeignKeyDefinition $addedForeignKeyDefinition; // in case of AddForeignKeyConstraint
}

class TableOperation
{
    public string $table;
    public TableOperationType $type;

    /**
     * @var TableStatement[]
     */
    public array $statements;
}
