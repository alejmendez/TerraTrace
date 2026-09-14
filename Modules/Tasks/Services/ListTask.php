<?php

namespace Modules\Tasks\Services;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Tasks\Models\Task;

class ListTask
{
    private const SEARCHABLE_COLUMNS = ['correlative', 'name', 'status', 'priority', 'updated_at', 'responsible.full_name'];

    public static function call($params)
    {
        $query = Task::query();

        $statusFilter = collect($params['filters']['status']['value'] ?? []);
        $params['filters']['status']['value'] = $statusFilter->map(fn ($item) => $item['value'])->toArray();

        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);
        $tasks = $datatable->of($query)->make();

        return $tasks;
    }

    public static function collection(array $params = []): array
    {
        $query = Task::query()
            ->select('tasks.id', 'tasks.correlative', 'tasks.name', 'tasks.status', 'tasks.priority', 'tasks.start_date', 'tasks.end_date', 'tasks.updated_at', 'tasks.responsible_id')
            ->with(['responsible:id,full_name']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('tasks.correlative', 'like', "%{$search}%")
                    ->where('tasks.name', 'like', "%{$search}%")
                    ->orWhereHas('responsible', function ($responsibleQuery) use ($search) {
                        $responsibleQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $statuses = collect(explode(',', (string) ($params['status'] ?? '')))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();

        if (!empty($statuses)) {
            $query->whereIn('tasks.status', $statuses);
        }

        if (!empty($params['priority'])) {
            $query->where('tasks.priority', $params['priority']);
        }

        if (!empty($params['responsible_id'])) {
            $query->where('tasks.responsible_id', $params['responsible_id']);
        }

        $summary = [
            'tasks' => (clone $query)->count(),
            'assigned' => (clone $query)->whereNotNull('tasks.responsible_id')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['correlative', 'name', 'status', 'priority', 'updated_at', 'end_date'], true)
            ? $params['sort']
            : 'updated_at';
        $direction = ($params['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("tasks.{$sort}", $direction)
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
