<?php

namespace Yui019\Hori\Commands\GenerateCommand;

use Exception;
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

                    $referencedTable = $attributes["on"];
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

            $dependencyGraph[$operation->table] = $references;
        }

        $correctOrder = self::topologicalSort($dependencyGraph);

        $sortedOperations = [];
        foreach ($correctOrder as $table) {
            foreach ($operations as $operation) {
                if ($operation->table == $table) {
                    array_push($sortedOperations, $operation);
                    break;
                }
            }
        }

        return $sortedOperations;
    }

    /**
     * @param array[string]string[] $dependencyGraph
     * @return string[]
     */
    private static function topologicalSort($dependencyGraph): array
    {
        $graph = [];
        foreach ($dependencyGraph as $key => $value) {
            $graph[$key] = $value;
        }

        $result = [];

        $allDependenciesEmpty = true;
        foreach ($graph as $_ => $dependsOn) {
            if (!empty($dependsOn)) {
                $allDependenciesEmpty = false;
                break;
            }
        }

        if ($allDependenciesEmpty) {
            foreach ($graph as $table => $_) {
                array_push($result, $table);
            }

            return $result;
        }

        $runAlgorithm = function () use (&$graph, &$result) {
            // Find table with no dependents
            // =============================

            $tableWithNoDependents = null;
            foreach ($graph as $table => $_) {
                $hasDependents = false;
                foreach ($graph as $_ => $dependsOn) {
                    if (in_array($table, $dependsOn)) {
                        $hasDependents = true;
                        break;
                    }
                }

                if (!$hasDependents) {
                    $tableWithNoDependents = $table;
                    break;
                }
            }

            if ($tableWithNoDependents == null) {
                return false;
            }

            // Add it to the result
            // ====================

            array_push($result, $tableWithNoDependents);

            // Remove that table from the graph
            // ================================
            foreach ($graph as $table => $dependsOn) {
                if (in_array($tableWithNoDependents, $dependsOn)) {
                    $newDependsOn = array_diff($dependsOn, [$tableWithNoDependents]);
                    $graph[$table] = $newDependsOn;
                }
            }
            unset($graph[$tableWithNoDependents]);

            return true;
        };

        while (true) {
            if (empty($graph)) {
                break;
            }

            if ($runAlgorithm() == false) {
                throw new Exception("Cycle!!");
            }
        }

        return array_reverse($result);
    }
}
