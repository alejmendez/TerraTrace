<?php

namespace Modules\Fields\Services\Batches;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Batch;

class ListBatch
{
    public static function call($params = [])
    {
        $searchableColumns = ['batch_number', 'delivery_date', 'importer.name'];

        $query = Batch::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $batches = $datatable->of($query)->make();

        return $batches;
    }
}
