<?php

declare(strict_types=1);

namespace Laramap\Console;

use Illuminate\Console\Command;
use Laramap\Scanner\RelationScanner;

class RelationsCommand extends Command
{
    protected $signature = 'laramap:relations';

    protected $description = 'Show the model relationship map.';

    public function __construct(private readonly RelationScanner $relationScanner)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $graph = $this->relationScanner->scan();

        foreach ($graph as $model => $relations) {
            $this->line($this->formatModelBlock($model, $relations));
            $this->newLine();
        }

        return self::SUCCESS;
    }

    /**
     * @param array<int, array{type:string, related:string}> $relations
     */
    private function formatModelBlock(string $model, array $relations): string
    {
        $lines = [class_basename($model)];

        foreach ($relations as $index => $relation) {
            $prefix = $index === array_key_last($relations) ? ' └── ' : ' ├── ';
            $lines[] = $prefix . $relation['type'] . ' ' . class_basename($relation['related']);
        }

        return implode(PHP_EOL, $lines);
    }
}
