<?php

namespace App\Support;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Throwable;

class AuditoriaModelInspector
{
    /**
     * @return array<int, class-string<Model>>
     */
    public static function modelClasses(): array
    {
        $modelsPath = app_path('Models');

        if (! is_dir($modelsPath)) {
            return [];
        }

        $classes = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($modelsPath));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($modelsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $class = 'App\\Models\\' . str_replace(
                ['/', DIRECTORY_SEPARATOR, '.php'],
                ['\\', '\\', ''],
                $relativePath
            );

            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $classes[] = $class;
            }
        }

        sort($classes);

        return array_values(array_unique($classes));
    }

    /**
     * @param class-string<Model> $modelClass
     */
    public static function shouldAuditGlobally(string $modelClass): bool
    {
        $status = self::status($modelClass);

        return $status['global'] === true;
    }

    /**
     * @param class-string<Model> $modelClass
     * @return array{model:string, table:?string, global:bool, trait:bool, covered:bool, required:bool, excluded:bool, reason:string}
     */
    public static function status(string $modelClass): array
    {
        $base = [
            'model' => $modelClass,
            'table' => null,
            'global' => false,
            'trait' => false,
            'covered' => false,
            'required' => in_array($modelClass, config('auditoria.required_models', []), true),
            'excluded' => false,
            'reason' => 'não analisado',
        ];

        try {
            $reflection = new ReflectionClass($modelClass);

            if ($reflection->isAbstract()) {
                return array_merge($base, [
                    'excluded' => true,
                    'reason' => 'model abstrato',
                ]);
            }

            /** @var Model $model */
            $model = new $modelClass();
            $table = $model->getTable();
            $usesLoggable = in_array(Loggable::class, class_uses_recursive($modelClass), true);
            $excludedByModel = in_array($modelClass, config('auditoria.excluded_models', []), true);
            $excludedByTable = in_array($table, config('auditoria.excluded_tables', []), true);
            $auditableModels = config('auditoria.auditable_models', []);
            $policy = (string) config('auditoria.model_policy', 'all_except_excluded');

            $base = array_merge($base, [
                'table' => $table,
                'trait' => $usesLoggable,
                'excluded' => $excludedByModel || $excludedByTable,
            ]);

            if ($excludedByModel) {
                return array_merge($base, [
                    'covered' => false,
                    'reason' => 'excluído por model',
                ]);
            }

            if ($excludedByTable) {
                return array_merge($base, [
                    'covered' => false,
                    'reason' => 'excluído por tabela',
                ]);
            }

            if ($usesLoggable) {
                return array_merge($base, [
                    'global' => false,
                    'covered' => true,
                    'reason' => 'coberto pela trait App\\Traits\\Loggable',
                ]);
            }

            if ($policy === 'only_listed' && ! in_array($modelClass, $auditableModels, true)) {
                return array_merge($base, [
                    'covered' => false,
                    'reason' => 'fora da lista auditable_models',
                ]);
            }

            return array_merge($base, [
                'global' => true,
                'covered' => true,
                'reason' => 'coberto pela auditoria global',
            ]);
        } catch (Throwable $exception) {
            return array_merge($base, [
                'reason' => 'erro ao analisar: ' . $exception->getMessage(),
            ]);
        }
    }
}
