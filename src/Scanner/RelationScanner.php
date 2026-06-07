<?php

declare(strict_types=1);

namespace Laramap\Scanner;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class RelationScanner
{
    public function __construct(private readonly ModelScanner $modelScanner)
    {
    }

    /**
     * @return array<string, array<int, array{type:string, related:string}>>
     */
    public function scan(): array
    {
        $graph = [];

        foreach ($this->modelScanner->scan() as $modelClass) {
            $relations = [];

            foreach ($this->relationMethods($modelClass) as $method) {
                $relation = $this->resolveRelation($modelClass, $method);
                if ($relation !== null) {
                    $relations[] = $relation;
                }
            }

            $graph[$modelClass] = $relations;
        }

        return $graph;
    }

    /**
     * @return array<int, ReflectionMethod>
     */
    private function relationMethods(string $modelClass): array
    {
        $reflection = new ReflectionClass($modelClass);
        $methods = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            if ($method->getDeclaringClass()->getName() !== $modelClass) {
                continue;
            }

            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * @return array{type:string, related:string}|null
     */
    private function resolveRelation(string $modelClass, ReflectionMethod $method): ?array
    {
        $declaredType = $method->getReturnType();
        if ($declaredType instanceof ReflectionNamedType && !$declaredType->isBuiltin()) {
            $related = $declaredType->getName();
            if (is_subclass_of($related, Model::class)) {
                return [
                    'type' => $this->relationTypeFromMethodName($method->getName()),
                    'related' => $related,
                ];
            }
        }

        try {
            $instance = (new ReflectionClass($modelClass))->newInstanceWithoutConstructor();
            $relationObject = $method->invoke($instance);
        } catch (\Throwable) {
            return null;
        }

        if (!is_object($relationObject)) {
            return null;
        }

        $related = method_exists($relationObject, 'getRelated') ? $relationObject->getRelated() : null;
        if (!$related instanceof Model) {
            return null;
        }

        return [
            'type' => $this->relationTypeFromRelationObject($relationObject::class),
            'related' => $related::class,
        ];
    }

    private function relationTypeFromMethodName(string $name): string
    {
        return match (true) {
            str_contains($name, 'belongsToMany') => 'belongsToMany',
            str_contains($name, 'belongsTo') => 'belongsTo',
            str_contains($name, 'hasMany') => 'hasMany',
            str_contains($name, 'hasOne') => 'hasOne',
            str_contains($name, 'morphMany') => 'morphMany',
            default => 'relation',
        };
    }

    private function relationTypeFromRelationObject(string $class): string
    {
        return match (true) {
            is_a($class, \Illuminate\Database\Eloquent\Relations\BelongsToMany::class, true) => 'belongsToMany',
            is_a($class, \Illuminate\Database\Eloquent\Relations\BelongsTo::class, true) => 'belongsTo',
            is_a($class, \Illuminate\Database\Eloquent\Relations\HasMany::class, true) => 'hasMany',
            is_a($class, \Illuminate\Database\Eloquent\Relations\HasOne::class, true) => 'hasOne',
            is_a($class, \Illuminate\Database\Eloquent\Relations\MorphMany::class, true) => 'morphMany',
            default => 'relation',
        };
    }
}
