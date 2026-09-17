<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\Quarter;
use Modules\Fields\Services\Owners\CreateOrUpdateOwner;

class FieldService
{
    private const SEARCHABLE_COLUMNS = ['name', 'location', 'size'];

    public function find(string|int $id): Field
    {
        return Field::select(
            'fields.id',
            'fields.name',
            'fields.location',
            'fields.size',
            'fields.blueprint',
            'owners.name as owner_name',
            'owners.dni as owner_dni',
        )
            ->withCount('plants')
            ->withCount('quarters')
            ->leftjoin('owners', 'fields.owner_id', '=', 'owners.id')
            ->findOrFail($id);
    }

    public function create(array $data): Field
    {
        DB::beginTransaction();
        try {
            $field = new Field;

            $field->name = $data['name'];
            $field->location = $data['location'];
            $field->size = $data['size'];
            $field->blueprint = $data['blueprint'];

            if (isset($data['owner_dni'])) {
                $owner = CreateOrUpdateOwner::call($data['owner_dni'], $data['owner_name']);
                $field->owner_id = $owner->id;
            }

            $field->save();

            if (isset($data['documents']) && is_array($data['documents'])) {
                foreach ($data['documents'] as $documentPath) {
                    $field->documents()->create([
                        'path' => $documentPath['path'],
                        'type' => $documentPath['type'],
                        'name' => $documentPath['name'],
                    ]);
                }
            }

            DB::commit();

            return $field;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function update(string|int $id, array $data): Field
    {
        DB::beginTransaction();
        try {
            $field = Field::findOrFail($id);

            $field->name = $data['name'];
            $field->location = $data['location'];
            $field->size = $data['size'];

            if (isset($data['owner_dni'])) {
                $owner = CreateOrUpdateOwner::call($data['owner_dni'], $data['owner_name']);
                $field->owner_id = $owner->id;
            }

            if ($data['blueprint']) {
                $field->blueprint = $data['blueprint'];
            }

            if (($data['blueprintRemove'] ?? null) === '1') {
                $field->blueprint = null;
            }

            $field->save();

            if (isset($data['documentsRemove']) && is_array($data['documentsRemove'])) {
                $field->documents()->whereIn('id', $data['documentsRemove'])->delete();
            }

            if (isset($data['documents']) && is_array($data['documents'])) {
                foreach ($data['documents'] as $documentPath) {
                    $field->documents()->create([
                        'path' => $documentPath['path'],
                        'type' => $documentPath['type'],
                        'name' => $documentPath['name'],
                    ]);
                }
            }

            DB::commit();

            return $field;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function delete(string|int $id): void
    {
        Field::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = Field::select('id', 'name', 'location', 'size')->withCount('plants');
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Field::query()->select('fields.id', 'fields.name', 'fields.location', 'fields.size');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('fields.name', 'like', "%{$search}%")
                    ->orWhere('fields.location', 'like', "%{$search}%");
            });
        }

        $fieldIds = (clone $query)->select('fields.id');
        $quarterIds = Quarter::query()->whereIn('field_id', $fieldIds)->select('id');
        $summary = [
            'fields' => (clone $query)->count(),
            'area' => (float) ((clone $query)->sum('fields.size') ?? 0),
            'quarters' => Quarter::query()->whereIn('field_id', $fieldIds)->count(),
            'plants' => Plant::query()->whereIn('quarter_id', $quarterIds)->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'size', 'plants_count', 'quarters_count'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->withCount(['plants', 'quarters'])
            ->orderBy($sort, $direction)
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
}
