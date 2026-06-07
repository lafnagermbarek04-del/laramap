<?php

declare(strict_types=1);

namespace Laramap\Scanner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class TableScanner
{
    public function __construct(private readonly RelationScanner $relationScanner)
    {
    }

    /**
     * @return array{table:string, columns:array<int, string>, relations:array<int, array{type:string, related:string}>}
     */
    public function scan(string $table): array
    {
        return [
            'table' => $table,
            'columns' => $this->columns($table),
            'relations' => $this->relations($table),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function columns(string $table): array
    {
        try {
            if (Schema::hasTable($table)) {
                return Schema::getColumnListing($table);
            }
        } catch (\Throwable) {
        }

        $model = $this->modelForTable($table);
        if ($model instanceof Model) {
            $columns = [];
            foreach (['id', 'created_at', 'updated_at'] as $column) {
                $columns[] = $column;
            }

            return $columns;
        }

        return [];
    }

    /**
     * @return array<int, array{type:string, related:string}>
     */
    private function relations(string $table): array
    {
        $model = $this->modelForTable($table);
        if (!$model instanceof Model) {
            return [];
        }

        $graph = $this->relationScanner->scan();
        return $graph[$model::class] ?? [];
    }

    private function modelForTable(string $table): ?Model
    {
        $modelScanner = new ModelScanner();

        foreach ($modelScanner->scan() as $modelClass) {
            $reflection = new ReflectionClass($modelClass);
            if (!$reflection->isInstantiable()) {
                continue;
            }

            $model = $reflection->newInstanceWithoutConstructor();
            if (method_exists($model, 'getTable') && $model->getTable() === $table) {
                return $model;
            }
        }

        return null;
    }
}
