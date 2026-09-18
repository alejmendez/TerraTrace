<?php

namespace Modules\Core\Registry;

use Closure;
use RuntimeException;

/**
 * Runtime registry of entity names → model classes.
 *
 * Lets each module declare the Eloquent model class behind a named
 * entity (the strings `EntityRegistry::model('field')` consumes from
 * `Modules\Tasks\Models\Task::field()` etc.), without Core having to
 * know about any specific module's models.
 *
 * Registration happens in each module's `ServiceProvider::register()`
 * (NOT boot()) so the registry is populated before any boot logic
 * queries it. Core itself doesn't register anything — it's the spine.
 *
 * @deprecated Use `EntityDispatcher` for data-shape lookups
 *             (see AGENTS.md §4.4). This registry now serves one
 *             purpose only: resolving entity name → model class for
 *             cross-module `belongsTo` / `belongsToMany` relations.
 *             The query-factory / data-shape half of the old API is
 *             retained as a default fallback (so `register('field',
 *             Field::class)` keeps working) but is no longer the
 *             intended entry point.
 *
 * The registry is static. State persists for the process lifetime; in
 * Octane that's across requests (intentional — registrations are
 * idempotent and cheap to repeat). For tests, call reset() in setUp.
 */
class EntityRegistry
{
    /** @var array<string, array{model: ?string, factory: Closure}> */
    private static array $entries = [];

    /**
     * Register an entity.
     *
     * @param  string  $name  short slug, e.g. "field", "harvest"
     * @param  string|null  $model  Eloquent model FQCN, or null when
     *                              providing a factory closure
     * @param  Closure|null  $factory  optional Closure(array $filter): mixed
     */
    public static function register(string $name, ?string $model, ?Closure $factory = null): void
    {
        if ($model === null && $factory === null) {
            throw new RuntimeException("EntityRegistry::register({$name}) needs either a model or a factory.");
        }

        // If a model is given without a factory, build a default factory
        // that returns a fresh query on the model. The list ordering /
        // column selection used to live inline here; it now lives in
        // each module's service method (FieldService::forSelect, etc.)
        // and is dispatched through EntityDispatcher.
        $factory ??= static fn (array $filter) => $model::query();

        self::$entries[$name] = [
            'model' => $model,
            'factory' => $factory,
        ];
    }

    public static function has(string $name): bool
    {
        return isset(self::$entries[$name]);
    }

    /**
     * Return the model class for an entity, or null if it was registered
     * with a factory closure only.
     */
    public static function model(string $name): ?string
    {
        if (! isset(self::$entries[$name])) {
            throw new RuntimeException("Entity '{$name}' is not registered in EntityRegistry.");
        }

        return self::$entries[$name]['model'];
    }

    /**
     * Invoke the entity's query factory with the given filter.
     *
     * @param  array<string, mixed>  $filter
     * @return mixed
     */
    public static function query(string $name, array $filter = [])
    {
        if (! isset(self::$entries[$name])) {
            throw new RuntimeException("Entity '{$name}' is not registered in EntityRegistry.");
        }

        return (self::$entries[$name]['factory'])($filter);
    }

    /**
     * Drop all registrations. Useful in tests where the registry is
     * populated by service providers that may run in a different order
     * across suites.
     */
    public static function reset(): void
    {
        self::$entries = [];
    }

    /**
     * @return array<string, string|null> map of name → model class (or null)
     */
    public static function all(): array
    {
        return array_map(fn (array $e) => $e['model'], self::$entries);
    }
}
