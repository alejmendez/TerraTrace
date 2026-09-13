<?php

namespace Modules\Fields\Services\Harvests;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Harvest;

class ListHarvest
{
    public static function call($params = [])
    {
        $query = Harvest::with('details', 'details.quarter', 'details.quarter.field', 'farmer');

        $searchableColumns = ['year', 'batch', 'details.quarter.field.name', 'details.quarter.name', 'farmer.full_name'];

        $special_sort = false;
        $paramsCollection = collect($params);

        $perPage = $paramsCollection->get('rows', 10);
        $currentPage = $paramsCollection->get('page', 0) + 1;

        $filters = collect($paramsCollection->get('filters', []));
        $sort = $paramsCollection->get('sortField');
        $sortDirection = $paramsCollection->get('sortOrder') == 1 ? 'asc' : 'desc';
        if ($sort === 'total_weight' || $sort === 'unit_count') {
            $params['sortField'] = '';
            $params['sortOrder'] = '';
            $special_sort = true;
        }

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $harvestsQuery = $datatable->of($query)->make(true);
        $harvestsTotal = $harvestsQuery->clone()->reorder()->get();

        $quarterFilter = collect($filters->get('details.quarter_id', []));
        $quarterId = $quarterFilter->get('value');

        $detailsTotal = [];
        $details_count = 0;
        $details_sum_weight = 0;
        foreach ($harvestsTotal as $harvest) {
            $detailsTotal = $harvest->details;
            if ($quarterId) {
                $detailsTotal = $harvest->details->filter(function ($detail) use ($quarterId) {
                    return $detail->quarter_id === $quarterId;
                });
            }

            $details_count += $detailsTotal->count();
            // $details_sum_weight += $detailsTotal->sum('weight');
            $details_sum_weight += $harvest->weight;
        }

        $harvests = $harvestsQuery->paginate($perPage, page: $currentPage);

        $harvestsCollection = $harvests->getCollection();

        $harvestsCollection->transform(function ($harvest) use ($filters) {
            $quarterFilter = collect($filters->get('details.quarter_id', []));
            $quarterId = $quarterFilter->get('value');

            $details = $harvest->details;
            if ($quarterId) {
                $details = $details->where('quarter_id', $quarterId);
            }

            $field_names = $details->map(fn ($detail) => $detail->quarter?->field?->name)->unique()->join(', ');
            $quarter_names = $details->map(fn ($detail) => $detail->quarter?->name)->unique()->join(', ');

            if ($quarter_names == '') {
                if ($harvest->quarters->count() > 0) {
                    $quarter_names = $harvest->quarters->map(fn ($quarter) => $quarter->name)->unique()->join(', ');
                    $field_names = $harvest->quarters->map(fn ($quarter) => $quarter->field?->name)->unique()->join(', ');
                }
            }

            return [
                'id' => $harvest->id,
                'date' => $harvest->date,
                'year' => $harvest->year,
                'week' => $harvest->week,
                'batch' => $harvest->batch,
                'field_names' => $field_names,
                'quarter_names' => $quarter_names,
                // 'total_weight' => $details->map(fn ($detail) => $detail->weight)->sum(),
                'total_weight' => $harvest->weight,
                'unit_count' => $details->count(),
                'farmer_name' => optional($harvest->farmer)->name,
            ];
        });

        $harvestsArray = $harvests->toArray();

        if ($special_sort) {
            $data = collect($harvestsArray['data']);
            if ($sortDirection === 'asc') {
                $data = $data->sortBy($sort);
            } else {
                $data = $data->sortByDesc($sort);
            }
            $harvestsArray['data'] = $data->values();
        }

        $harvestsArray['details_count'] = $details_count;
        $harvestsArray['details_sum_weight'] = $details_sum_weight;

        return $harvestsArray;
    }
}
