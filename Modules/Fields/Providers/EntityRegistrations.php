<?php

namespace Modules\Fields\Providers;

use Modules\Core\Registry\EntityRegistry;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Machinery;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\Quarter;
use Modules\Fields\Models\SecurityEquipment;
use Modules\Fields\Models\Tool;

/**
 * EntityRegistry registrations owned by the Fields module.
 *
 * Only the bare model-class mappings needed by cross-module
 * `belongsTo` / `belongsToMany` relations (see
 * `Modules\Tasks\Models\Task::field()`, etc.) live here — no query
 * factories. Data-shape lookups (the `forSelect()`, `availableYears()`,
 * etc. calls the SelectsController HTTP endpoint serves) moved to
 * `EntityDispatcher`, which delegates to the appropriate module
 * service.
 */
class EntityRegistrations
{
    public static function register(): void
    {
        EntityRegistry::register('field', Field::class);
        EntityRegistry::register('quarter', Quarter::class);
        EntityRegistry::register('plant', Plant::class);
        EntityRegistry::register('tool', Tool::class);
        EntityRegistry::register('security_equipment', SecurityEquipment::class);
        EntityRegistry::register('machinery', Machinery::class);
    }
}
