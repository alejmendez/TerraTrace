<?php

namespace Modules\Fields\Services\Batches;

use Modules\Fields\Models\Batch;

class DeleteBatch
{
    public static function call($id): void
    {
        Batch::destroy($id);
    }
}
