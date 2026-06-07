<?php

declare(strict_types=1);

namespace Laramap\Scanner;

use Illuminate\Database\Eloquent\Model;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class ModelScanner
{
    /**
     * @return array<int, string>
     */
    public function scan(): array
    {
        $paths = $this->modelPaths();
        $models = [];

        foreach ($paths as $path) {
            foreach ($this->classesInPath($path) as $class) {
                if ($this->looksLikeModel($class)) {
                    $models[] = $class;
                }
            }
        }

        return array_values(array_unique($models));
    }

    /**
     * @return array<int, string>
     */
    private function modelPaths(): array
    {
        $paths = [base_path('app/Models'), base_path('app')];

        return array_values(array_filter($paths, static fn (string $path) => is_dir($path)));
    }

    /**
     * @return array<int, string>
     */
    private function classesInPath(string $path): array
    {
        $classes = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }

            if (!preg_match('/namespace\s+([^;]+);/m', $contents, $namespaceMatch)) {
                continue;
            }

            if (!preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)/m', $contents, $classMatch)) {
                continue;
            }

            $classes[] = trim($namespaceMatch[1]) . '\\' . trim($classMatch[1]);
        }

        return $classes;
    }

    private function looksLikeModel(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }

        $reflection = new ReflectionClass($class);

        return $reflection->isSubclassOf(Model::class);
    }
}
