<?php

namespace Modules\Core\Registry;

use Closure;
use Modules\Auth\Services\AuthService;
use Modules\Fields\Services\CategoryProductService;
use Modules\Fields\Services\DogService;
use Modules\Fields\Services\FieldService;
use Modules\Fields\Services\HarvestService;
use Modules\Fields\Services\ImporterService;
use Modules\Fields\Services\LiquidationService;
use Modules\Fields\Services\MachineryService;
use Modules\Fields\Services\PlantService;
use Modules\Fields\Services\PlantTypeService;
use Modules\Fields\Services\QuarterService;
use Modules\Fields\Services\SecurityEquipmentService;
use Modules\Fields\Services\ToolService;
use Modules\Users\Services\UserService;
use RuntimeException;

/**
 * Resolves the named entity keys the legacy
 * `SelectsController` HTTP endpoint
 * (`/api/selects/{entity}`) still serves, delegating each one to
 * the module owner.
 *
 * Two kinds of entries:
 *
 *   - Service-backed  → entity maps to `[ServiceClass, method]`;
 *                       the dispatcher resolves the service through
 *                       the container (so constructor deps work) and
 *                       calls the method. Most entries are the
 *                       `forSelect()` helper each entity service
 *                       exposes (see AGENTS.md §3).
 *   - Static          → entity maps to a Closure; used for
 *                       translation-driven lists (`scale_type`,
 *                       `genders`) that don't belong to any module.
 *
 * Adding a new entity: register it here and (if it's data-driven)
 * expose the corresponding method on its module's service. No
 * `EntityRegistry::register()` call needed.
 *
 * `EntityRegistry` still exists for one purpose only: model-class
 * resolution in cross-module `belongsTo` / `belongsToMany` relations
 * (see `Modules\Tasks\Models\Task::field()` etc.). The data-shape
 * half of the registry — the closures that this dispatcher replaces
 * — has been removed from the registration sites.
 */
class EntityDispatcher
{
    /**
     * Service-backed entries.
     *
     * @var array<string, array{0: class-string, 1: string}>
     */
    private const SERVICE_MAP = [
        // Fields module
        'field' => [FieldService::class, 'forSelect'],
        'quarter' => [QuarterService::class, 'forSelect'],
        'quarterMultiselect' => [QuarterService::class, 'quartersByFieldGrouped'],
        'plant' => [PlantService::class, 'forSelect'],
        'plant_type' => [PlantTypeService::class, 'forSelect'],
        'tool' => [ToolService::class, 'forSelect'],
        'security_equipment' => [SecurityEquipmentService::class, 'forSelect'],
        'machinery' => [MachineryService::class, 'forSelect'],
        'dog' => [DogService::class, 'forSelect'],
        'importer' => [ImporterService::class, 'forSelect'],
        'category_products' => [CategoryProductService::class, 'categoryProductsForSelect'],
        'harvest' => [HarvestService::class, 'forSelect'],
        'harvest_multiselect' => [HarvestService::class, 'harvestMultiselect'],
        'harvest_available_years' => [HarvestService::class, 'availableYears'],
        'harvest_available_weeks' => [HarvestService::class, 'availableWeeks'],
        'liquidation_available_years' => [LiquidationService::class, 'availableYears'],

        // Users module (three aliases share one method)
        'user' => [UserService::class, 'forSelect'],
        'responsible' => [UserService::class, 'forSelect'],
        'couple' => [UserService::class, 'forSelect'],

        // Auth module
        'role' => [AuthService::class, 'roles'],
    ];

    /**
     * Static / translation-driven entries. Built lazily because
     * closures that call `trans()` can't live inside a const array.
     *
     * @return array<string, Closure(array<string,mixed>): mixed>
     */
    private static function staticMap(): array
    {
        return [
            'scale_type' => static fn () => [
                ['value' => 'weight', 'text' => trans('quarter.show.statistics.scale_type.options.weight')],
                ['value' => 'quantity', 'text' => trans('quarter.show.statistics.scale_type.options.quantity')],
            ],

            'is_commercial_options' => static fn () => app(CategoryProductService::class)->isCommercialOptions(),

            'genders' => static fn () => [
                ['value' => 'M', 'text' => trans('dog.form.gender.options.male')],
                ['value' => 'F', 'text' => trans('dog.form.gender.options.female')],
            ],
        ];
    }

    /**
     * Resolve a single entity by name.
     *
     * @param  array<string, mixed>  $filter  currently unused — service
     *                                        methods are parameterless.
     *                                        Kept for API stability.
     * @return mixed
     */
    public static function dispatch(string $entity, array $filter = [])
    {
        if (isset(self::SERVICE_MAP[$entity])) {
            [$class, $method] = self::SERVICE_MAP[$entity];

            return app($class)->{$method}();
        }

        if (isset(self::staticMap()[$entity])) {
            return self::staticMap()[$entity]($filter);
        }

        throw new RuntimeException("Entity '{$entity}' is not registered in EntityDispatcher.");
    }

    /**
     * Resolve a batch of entities keyed by name. `$entities` mirrors
     * the body shape the `entity=multiple` HTTP endpoint accepts:
     * either `[entity => filter]` (assoc array) or `{entity: filter}`
     * (stdClass from `json_decode`). Filters are passed through but
     * unused by current service methods.
     *
     * @param  iterable<string, mixed>  $entities
     * @return array<string, mixed>
     */
    public static function dispatchMany(iterable $entities): array
    {
        $out = [];

        foreach ($entities as $entity => $filter) {
            $out[$entity] = self::dispatch(
                (string) $entity,
                is_array($filter) ? $filter : []
            );
        }

        return $out;
    }

    /**
     * @return array<string, string> entity => "Service::method" or "static"
     */
    public static function catalogue(): array
    {
        $catalogue = [];

        foreach (self::SERVICE_MAP as $entity => [$class, $method]) {
            $catalogue[$entity] = $class.'::'.$method;
        }

        foreach (array_keys(self::staticMap()) as $entity) {
            $catalogue[$entity] = 'static';
        }

        ksort($catalogue);

        return $catalogue;
    }
}
