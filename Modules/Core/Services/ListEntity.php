<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Registry\EntityRegistry;

/**
 * Resolves a short entity name (e.g. "field", "task_priorities") into
 * the data structure the frontend expects for <select> options.
 *
 * All entity definitions now live in `Modules/<Name>/Providers/<Name>ServiceProvider::register()`
 * via EntityRegistry::register(). This file used to hard-code every
 * Fields model import; with the registry it has zero cross-module
 * dependencies.
 *
 * Output shapes accepted:
 *   - array        → returned as-is
 *   - Collection   → returned as-is
 *   - Builder/Query → applyFilter() then ->get()
 *
 * Adding a new entity: register it in the relevant module's
 * ServiceProvider::register(). No Core change required.
 */
class ListEntity
{
    /**
     * Resolve an entity name into the data the frontend needs.
     *
     * @param  string  $entity
     * @param  array<string, mixed>  $filter  applied when the entity is a query
     * @return array|Collection
     */
    public static function call($entity, $filter = [])
    {
        $result = EntityRegistry::query($entity, $filter);

        if (is_array($result) || $result instanceof Collection) {
            return $result;
        }

        // Eloquent Builder or query builder.
        return self::applyFilter($result, $filter)->get();
    }

    protected static function applyFilter($query, array $filter)
    {
        if (! $query instanceof Builder) {
            return $query;
        }

        foreach ($filter as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }
}
