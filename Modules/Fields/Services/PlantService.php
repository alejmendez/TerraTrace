<?php

namespace Modules\Fields\Services;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\PlantDetail;

class PlantService
{
    private const SEARCHABLE_COLUMNS = ['plants.code', 'quarter.name', 'quarter.field.name', 'plant_type.name', 'plants.age', 'quarter.responsible.full_name'];

    public function find(string|int $id): Plant
    {
        return Plant::findOrFail($id);
    }

    /**
     * Cross-module lookup by the plant's printable code (e.g.
     * "A-01-04"). Used by HarvestDetailService::create() and
     * PlantDetailService::create() (and historically by a
     * controller) so they don't have to know how codes are
     * formatted (uppercased, trimmed) -- that detail lives here.
     */
    public function findByCode(string $code): ?Plant
    {
        return Plant::firstWhere('code', trim(strtoupper($code)));
    }

    public function create(array $data): Plant
    {
        $plant = new Plant;

        $plant->quarter_id = $data['quarter_id']['value'];
        $plant->code = $data['code'];
        $plant->row = $data['row'];
        $plant->plant_type_id = $data['plant_type_id']['value'];
        $plant->age = 0;
        $plant->planned_at = $data['planned_at'];
        $plant->nursery_origin = $data['nursery_origin'];

        $plant->save();

        return $plant;
    }

    public function update(string|int $id, array $data): Plant
    {
        $plant = Plant::findOrFail($id);

        $plant->quarter_id = $data['quarter_id']['value'];
        $plant->code = $data['code'];
        $plant->row = $data['row'];
        $plant->plant_type_id = $data['plant_type_id']['value'];
        $plant->planned_at = $data['planned_at'];
        $plant->nursery_origin = $data['nursery_origin'];

        $plant->save();

        return $plant;
    }

    public function delete(string|int $id): void
    {
        Plant::destroy($id);
    }

    /**
     * Add a "note" detail row for a plant, deactivating any previous
     * active note for the same plant. Stored as a PlantDetail row
     * with type='note' so the existing PlantDetailCollection JSON
     * endpoint picks it up.
     *
     * Moved from a transversal static helper
     * (Modules\Fields\Services\Plants\CreatePlantNote) so the cross-
     * module consumers can inject PlantService directly.
     */
    public function addNote(int $plantId, string $note): PlantDetail
    {
        PlantDetail::where('plant_id', $plantId)
            ->where('type', 'note')
            ->update(['is_active' => false]);

        $plantDetail = new PlantDetail;
        $plantDetail->plant_id = $plantId;
        $plantDetail->type = 'note';
        $plantDetail->value = $note;
        $plantDetail->is_active = true;
        $plantDetail->save();

        return $plantDetail;
    }

    public function list(array $params = []): mixed
    {
        $query = Plant::query();
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Plant::query()
            ->select('plants.id', 'plants.code', 'plants.quarter_id', 'plants.plant_type_id', 'plants.age', 'plants.planned_at', 'plants.row')
            ->with([
                'plant_type:id,name',
                'quarter:id,name,field_id,responsible_id',
                'quarter.field:id,name',
                'quarter.responsible:id,full_name',
            ]);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('plants.code', 'like', "%{$search}%")
                    ->orWhereHas('quarter', function ($quarterQuery) use ($search) {
                        $quarterQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhereHas('field', function ($fieldQuery) use ($search) {
                                $fieldQuery->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if (! empty($params['field_id'])) {
            $query->whereHas('quarter', function ($quarterQuery) use ($params) {
                $quarterQuery->where('field_id', $params['field_id']);
            });
        }

        if (! empty($params['quarter_id'])) {
            $query->where('plants.quarter_id', $params['quarter_id']);
        }

        if (! empty($params['plant_type_id'])) {
            $query->where('plants.plant_type_id', $params['plant_type_id']);
        }

        if (! empty($params['responsible_id'])) {
            $query->whereHas('quarter', function ($quarterQuery) use ($params) {
                $quarterQuery->where('responsible_id', $params['responsible_id']);
            });
        }

        $summaryQuery = clone $query;
        $summary = [
            'plants' => (clone $summaryQuery)->count(),
            'quarters' => (clone $summaryQuery)->distinct('plants.quarter_id')->count('plants.quarter_id'),
            'fields' => (clone $summaryQuery)
                ->join('quarters as summary_quarters', 'plants.quarter_id', '=', 'summary_quarters.id')
                ->distinct('summary_quarters.field_id')
                ->count('summary_quarters.field_id'),
            'types' => (clone $summaryQuery)->distinct('plants.plant_type_id')->count('plants.plant_type_id'),
        ];

        $sort = in_array($params['sort'] ?? '', ['code', 'age', 'planned_at'], true)
            ? $params['sort']
            : 'code';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 50), 1), 100);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("plants.{$sort}", $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem() ?? 0,
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ],
            'summary' => $summary,
        ];
    }

    /**
     * Flat {value, text} list for cross-module consumers. The value
     * is the plant id; the text is the printable code so the form can
     * display the plant identifier (e.g. "A-01-04") in the <select>.
     */
    public function forSelect(): array
    {
        return Plant::select('id as value', 'code as text')
            ->orderBy('code')
            ->get()
            ->toArray();
    }

    /**
     * Filtered variant: plants whose quarter_id is in the supplied
     * list. Mirrors the lazy dispatch Tasks used to rely on through
     * `EntityRegistry::query('plant', …)`.
     */
    public function byQuarter(array $quarterIds): array
    {
        if (empty($quarterIds)) {
            return [];
        }

        return Plant::select('id as value', 'code as text')
            ->whereIn('quarter_id', $quarterIds)
            ->orderBy('code')
            ->get()
            ->toArray();
    }
}
