<?php

declare(strict_types=1);

namespace Laramap\Console;

use Illuminate\Console\Command;
use Laramap\Scanner\TableScanner;

class ShowCommand extends Command
{
    protected $signature = 'laramap:show {table}';

    protected $description = 'Show a table structure and its relations.';

    public function __construct(private readonly TableScanner $tableScanner)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $table = (string) $this->argument('table');
        $report = $this->tableScanner->scan($table);

        $this->line('Table: ' . $report['table']);
        $this->newLine();
        $this->line('Columns:');

        foreach ($report['columns'] as $column) {
            $this->line('- ' . $column);
        }

        $this->newLine();
        $this->line('Relations:');

        if ($report['relations'] === []) {
            $this->line('- None');
            return self::SUCCESS;
        }

        foreach ($report['relations'] as $relation) {
            $this->line('- ' . $relation['type'] . ' ' . class_basename($relation['related']));
        }

        return self::SUCCESS;
    }
}
