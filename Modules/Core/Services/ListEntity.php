<?php

namespace Modules\Core\Services;

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
use Modules\Users\Models\User;

class ListEntity
{
    public static function call($entity, $filter = [])
    {
        $entityQuery = match ($entity) {
            'field' => Field::select('id as value', 'name as text')->orderBy('name'),
            'quarter' => Quarter::select('id as value', 'name as text')->orderBy('name'),
            'quarterMultiselect' => self::quarterMultiselect(),
            'plant' => Plant::select('id as value', 'code as text')->orderBy('code'),
            'plant_type' => PlantType::select('id as value', 'name as text')->orderBy('name'),
            'tool' => Tool::select('id as value', 'name as text')->orderBy('name'),
            'security_equipment' => SecurityEquipment::select('id as value', 'name as text')->orderBy('name'),
            'machinery' => Machinery::select('id as value', 'name as text')->orderBy('name'),
            'dog' => Dog::select('id as value', 'name as text')->orderBy('name'),
            'harvest' => Harvest::select('id', 'week', 'batch', 'year')->orderBy('date')->get()->map(function ($harvest) {
                return [
                    'value' => $harvest->id,
                    'year' => $harvest->year,
                    'text' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
                ];
            }),
            'harvest_multiselect' => Harvest::select('year')->distinct()->orderBy('year', 'desc')->get()->map(function ($harvest) {
                return [
                    'items' => Harvest::select('id', 'week', 'batch')->where('year', $harvest->year)->orderBy('date', 'desc')->get()->map(function ($harvest) {
                        return [
                            'value' => $harvest->id,
                            'label' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
                        ];
                    }),
                    'label' => $harvest->year,
                ];
            }),
            'role' => \Spatie\Permission\Models\Role::select('name as value', 'name as text')->orderBy('name'),
            'harvest_available_years' => HarvestAvailableYears::call(),
            'harvest_available_weeks' => HarvestAvailableWeeks::call(),
            'liquidation_available_years' => LiquidationAvailableYears::call(),
            'importer' => Importer::select('id as value', 'name as text')->orderBy('name'),
            'category_products' => CategoryProduct::select('id', 'name', 'is_commercial'),
            'task_priorities' => collect(config('tasks.priorities'))->map(function ($priority) {
                return [
                    'value' => $priority,
                    'text' => __("task.form.priority.options.{$priority}"),
                ];
            }),
            'task_states' => collect(config('tasks.states'))->map(function ($state) {
                return [
                    'value' => $state,
                    'text' => __("task.form.status.options.{$state}"),
                ];
            }),
            'task_repeat_type' => collect(config('tasks.repeat_type'))->map(function ($type) {
                return [
                    'value' => $type,
                    'text' => __("task.form.repeat_type.options.{$type}"),
                ];
            }),
            'task_supplies_units' => collect(config('tasks.supplies_units'))->map(function ($unit) {
                return [
                    'value' => $unit,
                    'text' => __("task.form.supplies.unit.options.{$unit}"),
                ];
            }),
            'responsible' => User::select('id as value', 'full_name as text')->orderBy('full_name'),
            'couple' => User::select('id as value', 'full_name as text')->orderBy('full_name'),
            'user' => User::select('id as value', 'full_name as text')->orderBy('full_name'),
            'scale_type' => [
                [
                    'value' => 'weight',
                    'text' => trans('quarter.show.statistics.scale_type.options.weight'),
                ],
                [
                    'value' => 'quantity',
                    'text' => trans('quarter.show.statistics.scale_type.options.quantity'),
                ],
            ],
            'is_commercial_options' => [
                [
                    'value' => null,
                    'text' => trans('generics.all'),
                ],
                [
                    'value' => true,
                    'text' => trans('generics.yes'),
                ],
                [
                    'value' => false,
                    'text' => trans('generics.no'),
                ],
            ],
            'genders' => [
                [
                    'value' => 'M',
                    'text' => trans('dog.form.gender.options.male'),
                ],
                [
                    'value' => 'F',
                    'text' => trans('dog.form.gender.options.female'),
                ],
            ],
            default => [],
        };

        if (is_array($entityQuery) || $entityQuery instanceof \Illuminate\Support\Collection) {
            return $entityQuery;
        }

        return self::applyFilter($entityQuery, $filter)->get();
    }

    protected static function applyFilter($query, $filter)
    {
        foreach ($filter as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    protected static function quarterMultiselect()
    {
        return Quarter::leftJoin('fields', 'quarters.field_id', '=', 'fields.id')
            ->select('fields.id as field_id', 'fields.name as field_name', 'quarters.id', 'quarters.name')
            ->orderBy('fields.name')
            ->orderBy('quarters.name')
            ->get()
            ->groupBy('field_id')
            ->map(function ($group, $fieldName) {
                return [
                    'text' => $group[0]->field_name,
                    'items' => collect($group)->map(function ($quarter) {
                        return [
                            'value' => $quarter->id,
                            'text' => $quarter->name,
                        ];
                    }),
                ];
            })
            ->values()
            ->toArray();
    }
}
