<?php

namespace Modules\Fields\Providers;

use Modules\Core\Registry\EntityRegistry;
use Modules\Fields\Models\CategoryProduct;
use Modules\Fields\Models\Dog;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\Importer;
use Modules\Fields\Models\Machinery;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\PlantType;
use Modules\Fields\Models\Quarter;
use Modules\Fields\Models\SecurityEquipment;
use Modules\Fields\Models\Tool;
use Modules\Fields\Services\Harvests\HarvestAvailableWeeks;
use Modules\Fields\Services\Harvests\HarvestAvailableYears;
use Modules\Fields\Services\Liquidations\LiquidationAvailableYears;

/**
 * Centralised entity registrations for the Fields module.
 *
 * Called from FieldsServiceProvider::register() — keeping them in a
 * dedicated helper means the provider stays small and the entity
 * catalogue is grep-able in one place.
 */
class EntityRegistrations
{
    public static function register(): void
    {
        // Simple "id+text" selects.
        EntityRegistry::register('field', Field::class, static fn () => Field::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('quarter', Quarter::class, static fn () => Quarter::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('plant', Plant::class, static fn () => Plant::select('id as value', 'code as text')->orderBy('code'));
        EntityRegistry::register('plant_type', PlantType::class, static fn () => PlantType::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('tool', Tool::class, static fn () => Tool::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('security_equipment', SecurityEquipment::class, static fn () => SecurityEquipment::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('machinery', Machinery::class, static fn () => Machinery::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('dog', Dog::class, static fn () => Dog::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('importer', Importer::class, static fn () => Importer::select('id as value', 'name as text')->orderBy('name'));
        EntityRegistry::register('category_products', CategoryProduct::class, static fn () => CategoryProduct::select('id', 'name', 'is_commercial'));

        // Quarter grouped by field (multiselect shape).
        EntityRegistry::register('quarterMultiselect', null, static function () {
            return Quarter::leftJoin('fields', 'quarters.field_id', '=', 'fields.id')
                ->select('fields.id as field_id', 'fields.name as field_name', 'quarters.id', 'quarters.name')
                ->orderBy('fields.name')
                ->orderBy('quarters.name')
                ->get()
                ->groupBy('field_id')
                ->map(function ($group) {
                    return [
                        'text' => $group[0]->field_name,
                        'items' => collect($group)->map(fn ($quarter) => [
                            'value' => $quarter->id,
                            'text' => $quarter->name,
                        ])->values(),
                    ];
                })
                ->values()
                ->toArray();
        });

        // Harvest select with custom formatted text + year multiselect.
        EntityRegistry::register('harvest', null, static function () {
            return Harvest::select('id', 'week', 'batch', 'year')
                ->orderBy('date')
                ->get()
                ->map(fn ($harvest) => [
                    'value' => $harvest->id,
                    'year' => $harvest->year,
                    'text' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
                ])
                ->toArray();
        });

        EntityRegistry::register('harvest_multiselect', null, static function () {
            return Harvest::select('year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->get()
                ->map(function ($h) {
                    return [
                        'items' => Harvest::select('id', 'week', 'batch')
                            ->where('year', $h->year)
                            ->orderBy('date', 'desc')
                            ->get()
                            ->map(fn ($harvest) => [
                                'value' => $harvest->id,
                                'label' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
                            ])->values(),
                        'label' => $h->year,
                    ];
                })
                ->values()
                ->toArray();
        });

        // Aggregations provided by dedicated services.
        EntityRegistry::register('harvest_available_years', null, static fn () => HarvestAvailableYears::call());
        EntityRegistry::register('harvest_available_weeks', null, static fn () => HarvestAvailableWeeks::call());
        EntityRegistry::register('liquidation_available_years', null, static fn () => LiquidationAvailableYears::call());

        // Static option lists from translations.
        EntityRegistry::register('scale_type', null, static fn () => [
            ['value' => 'weight', 'text' => trans('quarter.show.statistics.scale_type.options.weight')],
            ['value' => 'quantity', 'text' => trans('quarter.show.statistics.scale_type.options.quantity')],
        ]);

        EntityRegistry::register('is_commercial_options', null, static fn () => [
            ['value' => null, 'text' => trans('generics.all')],
            ['value' => true, 'text' => trans('generics.yes')],
            ['value' => false, 'text' => trans('generics.no')],
        ]);

        EntityRegistry::register('genders', null, static fn () => [
            ['value' => 'M', 'text' => trans('dog.form.gender.options.male')],
            ['value' => 'F', 'text' => trans('dog.form.gender.options.female')],
        ]);
    }
}
