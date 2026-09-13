<?php

namespace Modules\Fields\Services\Batches;

use Modules\Fields\Models\Batch;

class CreateBatch
{
    public static function call($data): Batch
    {
        $batch = new Batch;
        $batch->batch_number = $data['batch_number'];
        $batch->delivery_date = $data['delivery_date'];
        $batch->importer_id = $data['importer_id']['value'];
        $batch->carrier = $data['carrier'];
        $batch->current_weight = $data['current_weight'];
        $batch->save();

        $harvests_ids = collect($data['harvests'] ?? [])->map(fn ($q) => $q['value'])->toArray();
        $batch->harvests()->sync($harvests_ids);

        return $batch;
    }
}
