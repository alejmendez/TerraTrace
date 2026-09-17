<?php

namespace Modules\Fields\Services;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Tool;

class ToolService
{
    private const SEARCHABLE_COLUMNS = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

    public function find(string|int $id): Tool
    {
        return Tool::findOrFail($id);
    }

    public function create(array $data): Tool
    {
        $tool = new Tool;
        $tool->name = $data['name'];
        $tool->purchase_date = $data['purchase_date'];
        $tool->last_maintenance = $data['last_maintenance'];
        $tool->purchase_location = $data['purchase_location'];
        $tool->type = $data['type'];
        $tool->contact = $data['contact'];
        $tool->note = $data['note'];
        $tool->save();

        return $tool;
    }

    public function update(string|int $id, array $data): Tool
    {
        $tool = Tool::findOrFail($id);

        $tool->name = $data['name'];
        $tool->purchase_date = $data['purchase_date'];
        $tool->last_maintenance = $data['last_maintenance'];
        $tool->purchase_location = $data['purchase_location'];
        $tool->type = $data['type'];
        $tool->contact = $data['contact'];
        $tool->note = $data['note'];
        $tool->save();

        return $tool;
    }

    public function delete(string|int $id): void
    {
        Tool::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = Tool::query();
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Tool::query()
            ->select('tools.id', 'tools.name', 'tools.purchase_date', 'tools.last_maintenance', 'tools.purchase_location', 'tools.contact');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('tools.name', 'like', "%{$search}%")
                    ->orWhere('tools.purchase_location', 'like', "%{$search}%")
                    ->orWhere('tools.contact', 'like', "%{$search}%");
            });
        }

        $summary = [
            'tools' => (clone $query)->count(),
            'with_maintenance' => (clone $query)->whereNotNull('tools.last_maintenance')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("tools.{$sort}", $direction)
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
        return Tool::select('id as value', 'name as text')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
