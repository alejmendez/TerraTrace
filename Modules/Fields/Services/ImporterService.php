<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Str;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Importer;

class ImporterService
{
    private const SEARCHABLE_COLUMNS = ['name', 'slug'];

    public function find(string|int $id): Importer
    {
        return Importer::findOrFail($id);
    }

    public function create(array $data): Importer
    {
        $slug = Str::slug($data['name']);
        $importer = Importer::where('slug', $slug)->first();
        if (! $importer) {
            $importer = new Importer;
            $importer->name = $data['name'];
            $importer->slug = $slug;
            $importer->save();
        }

        return $importer;
    }

    public function update(string|int $id, array $data): Importer
    {
        $importer = Importer::findOrFail($id);
        $importer->name = $data['name'];
        $importer->slug = Str::slug($data['name']);
        $importer->save();

        return $importer;
    }

    public function delete(string|int $id): void
    {
        Importer::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = Importer::query();
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Importer::query()
            ->select('importers.id', 'importers.name', 'importers.slug')
            ->withCount(['batches', 'liquidations']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('importers.name', 'like', "%{$search}%")
                    ->orWhere('importers.slug', 'like', "%{$search}%");
            });
        }

        $summary = [
            'importers' => (clone $query)->count(),
            'with_batches' => (clone $query)->has('batches')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'slug'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("importers.{$sort}", $direction)
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
     * Flat {value, text} list for cross-module consumers.
     */
    public function forSelect(): array
    {
        return Importer::select('id as value', 'name as text')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
