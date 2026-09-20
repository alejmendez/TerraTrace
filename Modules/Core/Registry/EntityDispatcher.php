<?php

namespace Modules\Core\Registry;

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
 * Every entry is service-backed: the entity maps to
 * `[ServiceClass, method]` and the dispatcher resolves the service
 * through the container (so constructor deps work) and calls the
 * method. Most entries are the `forSelect()` helper each entity
 * service exposes (see AGENTS.md §3).
 *
 * Truly-static option lists (genders, scale_type, is-commercial
 * filters) live in the frontend as TS constants under
 * `Modules/Core/Resources/js/constants/`, not here — they don't
 * need an HTTP round-trip.
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
     * Resolve a single entity by name.
     *
     * @param  array<string, mixed>  $filter  currently unused — service
     *                                        methods are parameterless.
     *                                        Kept for API stability.
     * @return mixed
     */
    public static function dispatch(string $entity, array $filter = [])
    {
        if (! isset(self::SERVICE_MAP[$entity])) {
            throw new RuntimeException("Entity '{$entity}' is not registered in EntityDispatcher.");
        }

        [$class, $method] = self::SERVICE_MAP[$entity];

        return app($class)->{$method}();
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
     * @return array<string, string> entity => "Service::method"
     */
    public static function catalogue(): array
    {
        $catalogue = [];

        foreach (self::SERVICE_MAP as $entity => [$class, $method]) {
            $catalogue[$entity] = $class.'::'.$method;
        }

        ksort($catalogue);

        return $catalogue;
    }
}
