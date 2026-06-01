<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Throwable;

class CachedSchema
{
    /**
     * Cache por request das tabelas existentes.
     *
     * @var array<string, bool>
     */
    private static array $tables = [];

    /**
     * Cache por request das colunas de cada tabela.
     *
     * @var array<string, array<string, true>>
     */
    private static array $columns = [];

    public static function hasTable(string $table): bool
    {
        $table = trim($table);

        if ($table === '') {
            return false;
        }

        if (! array_key_exists($table, self::$tables)) {
            try {
                self::$tables[$table] = Schema::hasTable($table);
            } catch (Throwable) {
                self::$tables[$table] = false;
            }
        }

        return self::$tables[$table];
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $table = trim($table);
        $column = trim($column);

        if ($table === '' || $column === '') {
            return false;
        }

        if (! array_key_exists($table, self::$columns)) {
            self::$columns[$table] = self::loadColumns($table);
        }

        return isset(self::$columns[$table][strtolower($column)]);
    }

    /**
     * @return array<string, true>
     */
    public static function columns(string $table): array
    {
        $table = trim($table);

        if ($table === '') {
            return [];
        }

        if (! array_key_exists($table, self::$columns)) {
            self::$columns[$table] = self::loadColumns($table);
        }

        return self::$columns[$table];
    }

    public static function reset(): void
    {
        self::$tables = [];
        self::$columns = [];
    }

    /**
     * @return array<string, true>
     */
    private static function loadColumns(string $table): array
    {
        try {
            if (! self::hasTable($table)) {
                return [];
            }

            $columns = Schema::getColumnListing($table);
            $indexed = [];

            foreach ($columns as $column) {
                $indexed[strtolower((string) $column)] = true;
            }

            return $indexed;
        } catch (Throwable) {
            return [];
        }
    }
}
