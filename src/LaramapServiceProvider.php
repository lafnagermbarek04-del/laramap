<?php

declare(strict_types=1);

namespace Laramap;

use Illuminate\Support\ServiceProvider;
use Laramap\Console\RelationsCommand;
use Laramap\Console\ShowCommand;
use Laramap\Scanner\ModelScanner;
use Laramap\Scanner\RelationScanner;
use Laramap\Scanner\TableScanner;

class LaramapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModelScanner::class, static fn () => new ModelScanner());
        $this->app->singleton(RelationScanner::class, static fn ($app) => new RelationScanner($app->make(ModelScanner::class)));
        $this->app->singleton(TableScanner::class, static fn ($app) => new TableScanner($app->make(RelationScanner::class)));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RelationsCommand::class,
                ShowCommand::class,
            ]);
        }
    }
}
