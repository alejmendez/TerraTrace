<?php

namespace Modules\Tasks\Services;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Tasks\Models\Task;

class ListTask
{
    public static function call($params)
    {
        $searchableColumns = ['correlative', 'name', 'status', 'priority', 'updated_at', 'responsible.full_name'];
        $query = Task::query();

        $statusFilter = collect($params['filters']['status']['value'] ?? []);
        $params['filters']['status']['value'] = $statusFilter->map(fn ($item) => $item['value'])->toArray();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $tasks = $datatable->of($query)->make();

        return $tasks;
    }
}
