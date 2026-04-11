<?php

namespace MrCatz\DataTable\Console;

use Illuminate\Console\Command;
use MrCatz\DataTable\MrCatzDataTablesComponent;

class MrcatzMeilisearchConfigureCommand extends Command
{
    protected $signature = 'mrcatz:meilisearch:configure
        {component : Fully-qualified class name of the MrCatzDataTablesComponent
                     OR the search proxy class (e.g. App\\Livewire\\ProductTable
                     or App\\Search\\ProductSearchProxy)}
        {--dry-run : Print the settings that would be sent without calling Meilisearch}';

    protected $description = 'Sync MrCatz typo_tolerance, scoring, and filter settings to a Meilisearch index';

    public function handle(): int
    {
        if (!class_exists(\Laravel\Scout\Searchable::class)) {
            $this->error('Laravel Scout is not installed. Run: composer require laravel/scout meilisearch/meilisearch-php');
            return self::FAILURE;
        }

        $target = $this->argument('component');
        if (!class_exists($target)) {
            $this->error("Class [{$target}] not found.");
            return self::FAILURE;
        }

        // Resolve config + proxy class
        [$config, $proxyClass] = $this->resolveConfigAndProxy($target);
        if ($config === null || $proxyClass === null) {
            return self::FAILURE;
        }

        $instance   = new $proxyClass();
        $indexName  = method_exists($instance, 'searchableAs') ? $instance->searchableAs() : ($instance->getTable() ?? 'default');

        $settings = $this->buildIndexSettings($config);

        $this->info("Meilisearch settings for index [{$indexName}]:");
        $this->newLine();
        foreach ($settings as $key => $value) {
            $this->line("  • {$key}: " . json_encode($value));
        }
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->comment('--dry-run set, not calling Meilisearch.');
            return self::SUCCESS;
        }

        // Apply settings via Scout's underlying Meilisearch client
        try {
            $engine = app(\Laravel\Scout\EngineManager::class)->engine();
            if (!method_exists($engine, 'index')) {
                $this->error('The current Scout driver is not Meilisearch. Set SCOUT_DRIVER=meilisearch in .env.');
                return self::FAILURE;
            }
            $index = $engine->index($indexName);

            if (isset($settings['typoTolerance']))        $index->updateTypoTolerance($settings['typoTolerance']);
            if (isset($settings['searchableAttributes'])) $index->updateSearchableAttributes($settings['searchableAttributes']);
            if (isset($settings['filterableAttributes'])) $index->updateFilterableAttributes($settings['filterableAttributes']);
            if (isset($settings['sortableAttributes']))   $index->updateSortableAttributes($settings['sortableAttributes']);
        } catch (\Throwable $e) {
            $this->error('Failed to apply settings: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Settings synced. Re-index data with:');
        $this->line("  php artisan scout:import \"{$proxyClass}\"");

        return self::SUCCESS;
    }

    /**
     * Accept either a MrCatzDataTablesComponent class (we instantiate to read
     * configTable()) or a search proxy class (we read settings from a
     * conventionally-named static method or fall back to defaults).
     */
    private function resolveConfigAndProxy(string $target): array
    {
        // Case 1: it's a component — instantiate and read configTable()
        if (is_subclass_of($target, MrCatzDataTablesComponent::class)) {
            try {
                $component = new $target();
                $config    = method_exists($component, 'configTable') ? $component->configTable() : null;
                if (!is_array($config)) {
                    $this->error("Component [{$target}] does not return an array from configTable().");
                    return [null, null];
                }
                $proxy = $config['search']['scout_model'] ?? null;
                if (!$proxy) {
                    $this->error("Component [{$target}] does not have 'search.scout_model' set in configTable().");
                    return [null, null];
                }
                return [$config, $proxy];
            } catch (\Throwable $e) {
                $this->error("Could not instantiate component: " . $e->getMessage());
                return [null, null];
            }
        }

        // Case 2: it's a Searchable proxy — use empty config (defaults only)
        if (in_array(\Laravel\Scout\Searchable::class, class_uses_recursive($target), true)) {
            return [[], $target];
        }

        $this->error("Class [{$target}] is neither a MrCatzDataTablesComponent nor a Searchable model.");
        return [null, null];
    }

    /**
     * Translate MrCatz config → Meilisearch index settings payload.
     */
    private function buildIndexSettings(array $config): array
    {
        $settings = [];

        // Typo tolerance
        $typo = $config['typo_tolerance'] ?? null;
        if ($typo !== null) {
            $enabled = $typo === true || (is_array($typo) && ($typo['driver'] ?? 'trigram') !== 'none');
            if ($enabled) {
                $minLen = is_array($typo) ? (int) ($typo['min_word_length'] ?? 4) : 4;
                $settings['typoTolerance'] = [
                    'enabled' => true,
                    'minWordSizeForTypos' => [
                        'oneTypo'  => $minLen,
                        'twoTypos' => $minLen * 2,
                    ],
                ];
            } else {
                $settings['typoTolerance'] = ['enabled' => false];
            }
        }

        // Scoring → searchableAttributes (sorted by weight desc)
        $scoring = $config['scoring'] ?? null;
        if (is_array($scoring) && !empty($scoring)) {
            $isLong = array_key_exists('columns', $scoring) || array_key_exists('mode', $scoring);
            $columns = $isLong ? ($scoring['columns'] ?? []) : $scoring;
            if (!empty($columns)) {
                arsort($columns);
                // Strip table prefix (Meilisearch uses bare attribute names)
                $settings['searchableAttributes'] = array_values(array_map(
                    fn($key) => str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key,
                    array_keys($columns)
                ));
            }
        }

        // date_field_map → filterableAttributes (the timestamp field names)
        $dateFieldMap = $config['search']['date_field_map'] ?? [];
        if (is_array($dateFieldMap) && !empty($dateFieldMap)) {
            $settings['filterableAttributes'] = array_values(array_unique(array_values($dateFieldMap)));
        }

        return $settings;
    }
}
