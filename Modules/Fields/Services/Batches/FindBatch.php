<?php

namespace Modules\Fields\Services\Batches;

use Modules\Fields\Models\Batch;

class FindBatch
{
    public static function call($id)
    {
        $batch = Batch::findOrFail($id);

        return $batch;
    }
}
