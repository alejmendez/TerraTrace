<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Collection;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Batch;
use Modules\Fields\Models\Harvest;

class BatchService
{
    private const SEARCHABLE_COLUMNS = ['batch_number', 'delivery_date', 'importer.name'];

    public function find(string|int $id): Batch
    {
        return Batch::findOrFail($id);
    }

    public function create(array $data): Batch
    {
        $batch = new Batch;
        $batch->batch_number = $data['batch_number'];
        $batch->delivery_date = $data['delivery_date'];
        $batch->importer_id = $data['importer_id']['value'];
        $batch->carrier = $data['carrier'];
        $batch->current_weight = $data['current_weight'];
        $batch->save();

        $this->syncHarvests($batch, $data['harvests'] ?? []);

        return $batch;
    }

    public function update(string|int $id, array $data): Batch
    {
        $batch = Batch::findOrFail($id);
        $batch->batch_number = $data['batch_number'];
        $batch->delivery_date = $data['delivery_date'];
        $batch->importer_id = $data['importer_id']['value'];
        $batch->carrier = $data['carrier'];
        $batch->current_weight = $data['current_weight'];
        $batch->save();

        $this->syncHarvests($batch, $data['harvests'] ?? []);

        return $batch;
    }

    public function delete(string|int $id): void
    {
        Batch::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = Batch::query();
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Batch::query()
            ->select('batches.id', 'batches.batch_number', 'batches.delivery_date', 'batches.importer_id', 'batches.carrier', 'batches.current_weight')
            ->with(['importer:id,name', 'harvests:id,year,week,batch']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('batches.batch_number', 'like', "%{$search}%")
                    ->orWhere('batches.carrier', 'like', "%{$search}%")
                    ->orWhereHas('importer', function ($importerQuery) use ($search) {
                        $importerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($params['importer_id'])) {
            $query->where('batches.importer_id', $params['importer_id']);
        }

        $summary = [
            'batches' => (clone $query)->count(),
            'total_weight' => (clone $query)->sum('batches.current_weight'),
        ];

        $sort = in_array($params['sort'] ?? '', ['batch_number', 'delivery_date', 'current_weight'], true)
            ? $params['sort']
            : 'delivery_date';
        $direction = ($params['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("batches.{$sort}", $direction)
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
     * Harvests grouped by year, excluding those already attached to
     * another batch (or to the current batch when editing).
     * Moved from BatchesController::getHarvests().
     */
    public function availableHarvests(?string $batchId = null): Collection
    {
        return Harvest::select('year')->distinct()->orderBy('year', 'desc')->get()->map(function ($harvest) use ($batchId) {
            $harvests = Harvest::select('id', 'week', 'batch')
                ->where('year', $harvest->year)
                ->whereDoesntHave('batches', function ($query) use ($batchId) {
                    if ($batchId) {
                        $query->where('batch_id', '!=', $batchId);
                    }
                })
                ->orderBy('date', 'desc')
                ->get();

            return [
                'items' => $harvests->map(function ($harvest) {
                    return [
                        'value' => $harvest->id,
                        'label' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
                    ];
                }),
                'label' => $harvest->year,
            ];
        })->filter(fn ($harvest) => $harvest['items']->isNotEmpty())->values();
    }

    private function syncHarvests(Batch $batch, array $harvests): void
    {
        $harvestIds = collect($harvests)->map(fn ($q) => $q['value'])->toArray();
        $batch->harvests()->sync($harvestIds);
    }
}
