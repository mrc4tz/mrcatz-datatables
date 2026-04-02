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
}
