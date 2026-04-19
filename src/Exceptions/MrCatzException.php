<?php

namespace MrCatz\DataTable\Exceptions;

class MrCatzException extends \RuntimeException
{
    public static function columnNotFound(int $index, int $total): self
    {
        return new self("Column index [{$index}] out of range. Table has {$total} columns (0-" . ($total - 1) . ").");
    }

    public static function filterNotFound(string $id): self
    {
        return new self("Filter with ID [{$id}] not found. Make sure it is defined in setFilter().");
    }

    public static function filterNotInitialized(string $id): self
    {
        return new self("Filter [{$id}] has not been initialized. Make sure ->get() is called on the filter.");
    }

    public static function rowNotFound(int $index, int $total): self
    {
        return new self("Row index [{$index}] out of range. Table has {$total} rows (0-" . ($total - 1) . ").");
    }

    public static function invalidScoringMode(string $mode): self
    {
        return new self("Invalid scoring mode [{$mode}]. Allowed values: 'replace', 'complement'.");
    }

    public static function invalidTypoDriver(string $driver): self
    {
        return new self("Invalid typo_tolerance driver [{$driver}]. Allowed values: 'none', 'trigram', 'pg_trgm'.");
    }

    public static function unsupportedDbForDriver(string $driver, string $actualDriver): self
    {
        return new self("typo_tolerance driver [{$driver}] requires a 'pgsql' database connection, but the current connection uses [{$actualDriver}]. Switch to 'trigram' for portable fuzzy search.");
    }

    public static function scoutNotInstalled(): self
    {
        return new self(
            "Search driver 'scout' requires Laravel Scout. Install it with:\n" .
            "  composer require laravel/scout meilisearch/meilisearch-php\n" .
            "Then run: php artisan vendor:publish --provider=\"Laravel\\Scout\\ScoutServiceProvider\""
        );
    }

    public static function scoutRequiresBuilder(): self
    {
        return new self("Search driver 'scout' requires baseQuery() to return an Eloquent or Query Builder, not a static array. Use the default 'database' driver instead.");
    }

    public static function scoutModelRequiredForQueryBuilder(): self
    {
        return new self(
            "Search driver 'scout' with a Query Builder baseQuery() requires a 'scout_model' proxy class.\n" .
            "Generate one with: php artisan mrcatz:make-search-proxy {Name}\n" .
            "Then add it to configTable():\n" .
            "  'search' => ['driver' => 'scout', 'scout_model' => \\App\\Search\\YourSearchProxy::class]"
        );
    }

    public static function scoutModelNotFound(string $class): self
    {
        return new self("Scout model class [{$class}] not found. Make sure it exists and is autoloaded.");
    }

    public static function modelNotSearchable(string $class): self
    {
        return new self("Class [{$class}] does not use the Laravel\\Scout\\Searchable trait. Add 'use Searchable;' and implement toSearchableArray().");
    }

    public static function invalidFilterPushdownMode(string $mode): self
    {
        return new self("Invalid filter_pushdown mode [{$mode}]. Allowed values: 'auto', 'always', 'never'.");
    }

    public static function filterNotPushable(string $key, string $reason): self
    {
        return new self("Filter [{$key}] cannot be pushed to Meilisearch ({$reason}). Either change filter_pushdown to 'auto' or 'never', or remove this filter.");
    }

    public static function invalidDateFormat(string $format): self
    {
        return new self("Invalid date filter format [{$format}]. Allowed: 'date', 'datetime', 'time', 'time_hm', 'month_year', 'year'.");
    }

    public static function invalidDateCondition(string $condition): self
    {
        return new self("Invalid date filter condition [{$condition}]. Allowed: '=', '!=', '<>', '>', '<', '>=', '<='.");
    }

    public static function dateOperatorNotSupported(string $operator, string $format): self
    {
        return new self("Operator [{$operator}] is not supported for date format [{$format}]. Supported operators for this format: '=', '!=', '>', '<', '>=', '<='.");
    }

    public static function invalidDateRangePart(string $part): self
    {
        return new self("Invalid date range part [{$part}]. Must be 'from' or 'to'.");
    }

    public static function invalidCheckCondition(string $condition): self
    {
        return new self("Invalid check filter condition [{$condition}]. Allowed: 'whereIn', 'whereNotIn'.");
    }

    public static function allowExcludeOnCallback(string $id): self
    {
        return new self("allowExclude() is not supported on createCheckWithCallback [{$id}] — the callback owns the include/exclude logic.");
    }

    public static function invalidCheckMode(string $mode): self
    {
        return new self("Invalid check filter mode [{$mode}]. Must be 'include' or 'exclude'.");
    }

    public static function setFilterDateBoundsNonDate(string $id, ?string $actualType): self
    {
        $got = $actualType ?? 'unknown';
        return new self("setFilterDateBounds() requires a 'date' or 'date_range' filter, but filter [{$id}] is of type [{$got}].");
    }

    public static function filterCallbackMethodNotFound(string $id, string $method): self
    {
        return new self("Filter [{$id}] callback override points to method [{$method}()] which does not exist on the component. Make sure the method is public and spelled correctly.");
    }
}
