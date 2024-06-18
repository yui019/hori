<?php

namespace Yui019\Hori\Commands\GenerateCommand;

use Illuminate\Support\Stringable;

class OperationsSortHelper
{
    /**
     * Sort array of operations according to foreign key constraints.
     *
     * Create operations are sorted such that if table A has a foreign key
     * referencing table B, table B is created first.
     * Other operations, such as Modify and Drop are kept in the same order and
     * placed after all Create operations.
     *
     * @param TableOperation[] $operations
     * @return TableOperation[]
     */
    public static function sort(array $operations): array
    {
        $createOperations = [];
        $otherOperations = [];

        foreach ($operations as $operation) {
            if ($operation->type == TableOperationType::Create) {
                array_push($createOperations, $operation);
            } else {
                array_push($otherOperations, $operation);
            }
        }

        $createOperations = self::sortCreateOperations($createOperations);

        return array_merge($createOperations, $otherOperations);
    }

    /**
     * @param TableOperation[] $operations
     * @return TableOperation[]
     */
    private static function sortCreateOperations(array $operations): array
    {
        $dependencyGraph = [];

        foreach ($operations as $operation) {
            // array of all other tables this table references
            $references = [];

            foreach ($operation->statements as $statement) {
                if ($statement->type == TableStatementType::AddForeignKeyConstraint) {
                    $attributes = $statement->addedForeignKeyDefinition->getAttributes();

                    $referencedTable = $attributes["references"];
                    if ($referencedTable instanceof Stringable) {
                        $referencedTable = $referencedTable->toString();
                    }

                    // ignore self references
                    if ($referencedTable == $operation->table) {
                        continue;
                    }

                    array_push($references, $referencedTable);
                }
            }

            array_push($dependencyGraph, [
                $operation->table => $references,
            ]);
        }

        /**
         * TODO: do topological sort on $dependencyGraph
         *
         * https://rosettacode.org/wiki/Topological_sort#Depth_First
         */

        return [];
    }
}
