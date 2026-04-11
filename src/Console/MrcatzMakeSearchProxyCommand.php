<?php

namespace MrCatz\DataTable\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MrcatzMakeSearchProxyCommand extends Command
{
    protected $signature = 'mrcatz:make-search-proxy
        {name : Resource name (e.g. Product, User, Category)}
        {--table= : Database table name (default: snake plural of name)}
        {--key=id : Primary key column}
        {--columns= : Comma-separated columns to index (e.g. name,sku,description)}
        {--index= : Meilisearch index name (default: table name)}
        {--path=app/Search : Output directory relative to project root}
        {--namespace=App\\Search : PHP namespace for the generated class}
        {--force : Overwrite existing file}';

    protected $description = 'Generate a Laravel Scout proxy class for MrCatz Meilisearch integration';

    public function handle(): int
    {
        if (!class_exists(\Laravel\Scout\Searchable::class)) {
            $this->error('Laravel Scout is not installed.');
            $this->newLine();
            $this->line('Install it first with:');
            $this->line('  composer require laravel/scout meilisearch/meilisearch-php');
            $this->line('  php artisan vendor:publish --provider="Laravel\\Scout\\ScoutServiceProvider"');
            return self::FAILURE;
        }

        $name      = Str::studly($this->argument('name'));
        $className = "{$name}SearchProxy";
        $table     = $this->option('table') ?: Str::snake(Str::plural($name));
        $primary   = $this->option('key') ?: 'id';
        $indexName = $this->option('index') ?: $table;
        $namespace = trim($this->option('namespace'), '\\');
        $relPath   = trim($this->option('path'), '/');

        // Resolve columns: explicit option, or interactive prompt, or all schema columns
        $columns = $this->resolveColumns($table);
        if (empty($columns)) {
            $this->error('No columns selected. Aborting.');
            return self::FAILURE;
        }

        $destDir  = base_path($relPath);
        $destFile = "{$destDir}/{$className}.php";

        if (file_exists($destFile) && !$this->option('force')) {
            $this->warn("SKIP  {$relPath}/{$className}.php (already exists, use --force to overwrite)");
            return self::SUCCESS;
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $stubPath = __DIR__ . '/../../stubs/search-proxy.stub';
        if (!file_exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");
            return self::FAILURE;
        }

        $selects = $this->buildSelectLines($table, $columns);
        $content = strtr(file_get_contents($stubPath), [
            '{{namespace}}'  => $namespace,
            '{{class}}'      => $className,
            '{{table}}'      => $table,
            '{{primaryKey}}' => $primary,
            '{{indexName}}'  => $indexName,
            '{{joins}}'      => '', // join scaffolding left empty — user can add manually
            '{{selects}}'    => $selects,
        ]);

        file_put_contents($destFile, $content);

        $this->info("CREATE  {$relPath}/{$className}.php");
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Review the generated file and adjust columns/joins if needed');
        $this->line('  2. Add to configTable() in your MrCatzDataTablesComponent:');
        $this->newLine();
        $this->line("       'search' => [");
        $this->line("           'driver'      => 'scout',");
        $this->line("           'scout_model' => \\{$namespace}\\{$className}::class,");
        $this->line("           // 'filter_pushdown' => 'auto', // optional: 'auto' (default) | 'always' | 'never'");
        $this->line("       ],");
        $this->newLine();
        $this->line('  3. Index existing data:');
        $this->line("       php artisan scout:import \"{$namespace}\\{$className}\"");
        $this->newLine();
        $this->line('  4. (Optional) Sync typo tolerance + scoring to Meilisearch:');
        $this->line("       php artisan mrcatz:meilisearch:configure {$namespace}\\{$className}");

        return self::SUCCESS;
    }

    /**
     * Resolve which columns to index. In order:
     *   1. --columns flag
     *   2. Interactive prompt with schema-detected columns
     *   3. All schema columns (non-interactive fallback)
     */
    private function resolveColumns(string $table): array
    {
        if ($this->option('columns')) {
            return array_filter(array_map('trim', explode(',', $this->option('columns'))));
        }

        $available = [];
        try {
            if (Schema::hasTable($table)) {
                $available = Schema::getColumnListing($table);
            }
        } catch (\Throwable $e) {
            // Schema introspection failed (e.g. no DB connection in console)
        }

        if (empty($available)) {
            $this->warn("Table [{$table}] not found or DB unreachable. Provide columns via --columns=col1,col2");
            return [];
        }

        if (!$this->input->isInteractive()) {
            return $available;
        }

        $this->info("Detected columns in [{$table}]:");
        foreach ($available as $i => $col) {
            $this->line("  [{$i}] {$col}");
        }
        $picked = $this->ask('Enter column numbers to index (comma-separated, blank = all)', '');
        if (trim($picked) === '') return $available;

        $indexes = array_filter(array_map('trim', explode(',', $picked)));
        $result = [];
        foreach ($indexes as $idx) {
            if (is_numeric($idx) && isset($available[(int) $idx])) {
                $result[] = $available[(int) $idx];
            }
        }
        return $result;
    }

    private function buildSelectLines(string $table, array $columns): string
    {
        $lines = [];
        foreach ($columns as $col) {
            // Qualify with table name unless already qualified
            $qualified = str_contains($col, '.') ? $col : "{$table}.{$col}";
            $lines[] = "                '{$qualified}',";
        }
        return implode("\n", $lines) . "\n";
    }
}
